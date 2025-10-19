<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\PerformanceCalculator;
use App\Models\Project;
use App\Models\User;
use App\Models\EmployeeEvaluation;
use App\Models\PerformanceMetric;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected $reportService;
    protected $performanceCalculator;

    public function __construct(ReportService $reportService, PerformanceCalculator $performanceCalculator)
    {
        $this->reportService = $reportService;
        $this->performanceCalculator = $performanceCalculator;
    }

    /**
     * Display the main reports dashboard
     */
    public function index(Request $request): View
    {
        $filters = $this->getFiltersFromRequest($request);

        // Get summary data for dashboard
        $summaryData = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_tasks' => \App\Models\Task::count(),
            'completed_tasks' => \App\Models\Task::where('status', 'completed')->count(),
            'total_users' => User::where('role', '!=', 'admin')->count(),
            'overdue_tasks' => \App\Models\Task::where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
        ];

        // Get recent evaluations
        $recentEvaluations = EmployeeEvaluation::with(['user', 'evaluator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get top performers this month
        $topPerformers = $this->reportService->getEmployeeRankings([
            'date_from' => Carbon::now()->startOfMonth(),
            'date_to' => Carbon::now()->endOfMonth(),
        ])->take(5);

        return view('reports.index', compact('summaryData', 'recentEvaluations', 'topPerformers', 'filters'));
    }

    /**
     * Display project reports
     */
    public function projects(Request $request): View
    {
        $filters = $this->getFiltersFromRequest($request);
        $projects = $this->reportService->getProjectOverviewReport($filters, $request);

        return view('reports.projects.overview', compact('projects', 'filters'));
    }

    /**
     * Display project progress report
     */
    public function projectProgress(Request $request): View
    {
        $filters = $this->getFiltersFromRequest($request);
        $projects = $this->reportService->getDetailedProjectProgress($filters, $request);

        // Get all projects for the filter dropdown
        $allProjects = Project::select('id', 'name', 'short_code')->orderBy('name')->get();

        return view('reports.projects.progress', compact('projects', 'filters', 'allProjects'));
    }

    /**
     * Display task reports
     */
    public function tasks(Request $request): View
    {
        $filters = $this->getFiltersFromRequest($request);
        
        // Add search parameter to filters
        $filters['search'] = $request->get('search');
        
        $taskReport = $this->reportService->getTaskCompletionReport($filters);

        // Get all projects and users for filter dropdowns
        $projects = Project::select('id', 'name', 'short_code')->orderBy('name')->get();
        $users = User::where('role', '!=', 'admin')->select('id', 'name', 'email')->orderBy('name')->get();


        return view('reports.tasks.completion', compact('taskReport', 'filters', 'projects', 'users'));
    }

    /**
     * Display user performance reports
     */
    public function users(Request $request): View
    {
        $filters = $this->getFiltersFromRequest($request);
        $rankings = $this->reportService->getEmployeeRankings($filters);

        return view('reports.users.performance', compact('rankings', 'filters'));
    }

    /**
     * Display individual user performance
     */
    public function userPerformance(Request $request, $userId): View
    {
        $filters = $this->getFiltersFromRequest($request);
        $userReport = $this->reportService->getUserPerformanceReport($userId, $filters);

        return view('reports.users.individual', compact('userReport', 'filters'));
    }

    /**
     * Display evaluations
     */
    public function evaluations(Request $request): View
    {
        $filters = $this->getFiltersFromRequest($request);
        $evaluationType = $request->get('type', 'monthly');

        $evaluations = EmployeeEvaluation::with(['user', 'evaluator'])
            ->when($evaluationType, function ($query) use ($evaluationType) {
                return $query->where('evaluation_type', $evaluationType);
            })
            ->when($filters['date_from'], function ($query) use ($filters) {
                return $query->where('evaluation_period_start', '>=', $filters['date_from']);
            })
            ->when($filters['date_to'], function ($query) use ($filters) {
                return $query->where('evaluation_period_start', '<=', $filters['date_to']);
            })
            ->orderBy('performance_score', 'desc')
            ->paginate(20);

        return view('reports.evaluations.index', compact('evaluations', 'filters', 'evaluationType'));
    }

    /**
     * Generate monthly evaluation
     */
    public function generateMonthlyEvaluation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $evaluation = $this->performanceCalculator->generateMonthlyEvaluation(
            $request->user_id,
            $request->year,
            $request->month
        );

        return redirect()->route('reports.evaluations')
            ->with('success', 'Monthly evaluation generated successfully.');
    }

    /**
     * Generate quarterly evaluation
     */
    public function generateQuarterlyEvaluation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
            'quarter' => 'required|integer|min:1|max:4',
        ]);

        $evaluation = $this->performanceCalculator->generateQuarterlyEvaluation(
            $request->user_id,
            $request->year,
            $request->quarter
        );

        return redirect()->route('reports.evaluations')
            ->with('success', 'Quarterly evaluation generated successfully.');
    }

    /**
     * Generate annual evaluation
     */
    public function generateAnnualEvaluation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $evaluation = $this->performanceCalculator->generateAnnualEvaluation(
            $request->user_id,
            $request->year
        );

        return redirect()->route('reports.evaluations')
            ->with('success', 'Annual evaluation generated successfully.');
    }

    /**
     * Calculate rankings for a period
     */
    public function calculateRankings(Request $request)
    {
        $request->validate([
            'evaluation_type' => 'required|in:monthly,quarterly,annual',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $rankings = $this->performanceCalculator->calculateRankings(
            $request->evaluation_type,
            $request->period_start,
            $request->period_end
        );

        return redirect()->route('reports.evaluations')
            ->with('success', 'Rankings calculated successfully.');
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request, $reportType)
    {
        $filters = $this->getFiltersFromRequest($request);

        switch ($reportType) {
            case 'project-progress':
                // Get all projects (not paginated) for export
                $projectsQuery = $this->reportService->getDetailedProjectProgress($filters, $request);
                $projects = $projectsQuery->getCollection(); // Get all items from paginator collection

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.project-progress', [
                    'projects' => $projects,
                    'filters' => $filters,
                ]);

                $pdf->setPaper('a4', 'portrait');
                $filename = 'project-progress-report-' . now()->format('Y-m-d') . '.pdf';

                return $pdf->download($filename);

            case 'projects':
                // Use detailed project progress for consistency with template
                $projectsQuery = $this->reportService->getDetailedProjectProgress($filters, $request);
                $projects = $projectsQuery->getCollection();

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.project-progress', [
                    'projects' => $projects,
                    'filters' => $filters,
                ]);

                $pdf->setPaper('a4', 'portrait');
                $filename = 'projects-overview-' . now()->format('Y-m-d') . '.pdf';

                return $pdf->download($filename);

            default:
                return response()->json(['message' => 'Report type not supported'], 400);
        }
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request, $reportType)
    {
        $filters = $this->getFiltersFromRequest($request);

        switch ($reportType) {
            case 'project-progress':
            case 'projects':
                // Get all projects (not paginated) for export
                $projectsQuery = $this->reportService->getDetailedProjectProgress($filters, $request);
                $projects = $projectsQuery->getCollection(); // Get all items from paginator collection

                $filename = 'project-progress-report-' . now()->format('Y-m-d') . '.xlsx';

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ProjectProgressExport($projects),
                    $filename
                );

            default:
                return response()->json(['message' => 'Report type not supported'], 400);
        }
    }

    /**
     * Get available users for filters
     */
    public function getUsers(Request $request)
    {
        $users = User::where('role', '!=', 'admin')
            ->select('id', 'name', 'email')
            ->get();

        return response()->json($users);
    }

    /**
     * Get available projects for filters
     */
    public function getProjects(Request $request)
    {
        $projects = Project::select('id', 'name', 'status')
            ->get();

        return response()->json($projects);
    }

    /**
     * Get performance metrics for a user
     */
    public function getUserMetrics(Request $request, $userId)
    {
        $periodType = $request->get('period_type', 'monthly');
        $date = $request->get('date', Carbon::now()->toDateString());

        $metrics = PerformanceMetric::where('user_id', $userId)
            ->where('period_type', $periodType)
            ->where('metric_date', '<=', $date)
            ->orderBy('metric_date', 'desc')
            ->limit(12)
            ->get();

        return response()->json($metrics);
    }

    /**
     * Extract filters from request
     */
    private function getFiltersFromRequest(Request $request): array
    {
        $status = $request->get('status', []);

        // Ensure status is always an array
        if (!is_array($status)) {
            $status = $status ? [$status] : [];
        }

        return [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'user_id' => $request->get('user_id'),
            'project_id' => $request->get('project_id'),
            'status' => $status,
            'priority' => $request->get('priority', []),
        ];
    }

    /**
     * Export comprehensive project report with all data, tasks, and history
     */
    public function exportFullProjectReport(Project $project)
    {
        try {
            \Log::info('Starting full project report export for project: ' . $project->id);
            
            // Get comprehensive project data
            $projectData = $this->getComprehensiveProjectData($project);
            \Log::info('Project data retrieved successfully', ['project_id' => $project->id]);
            
            // Test with minimal data first
            $testData = [
                'project' => $project,
                'projectStats' => [
                    'total_tasks' => 0,
                    'completed_tasks' => 0,
                    'in_progress_tasks' => 0,
                    'pending_tasks' => 0,
                    'overdue_tasks' => 0,
                    'completion_rate' => 0,
                ],
                'allTasks' => collect([]),
                'allTaskHistory' => collect([]),
                'teamPerformance' => collect([]),
                'projectTimeline' => [
                    'created_at' => $project->created_at,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'updated_at' => $project->updated_at,
                ],
                'projectDuration' => 0
            ];
            
            // Generate PDF
            $pdf = Pdf::loadView('reports.pdf.full-project-report', $testData);
            $pdf->setPaper('A4', 'portrait');
            \Log::info('PDF generated successfully');
            
            $filename = 'Full_Project_Report_' . $project->short_code . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            \Log::info('Downloading PDF with filename: ' . $filename);
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('Failed to export full project report: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to generate project report: ' . $e->getMessage());
        }
    }

    /**
     * Get comprehensive project data including all tasks and history
     */
    private function getComprehensiveProjectData(Project $project)
    {
        // Load project with all relationships
        $project->load([
            'folders.children',
            'tasks.assignee',
            'tasks.creator',
            'tasks.attachments',
            'tasks.history.user',
            'manager',
            'createdBy'
        ]);

        // Get all tasks with full details
        $allTasks = $project->tasks()->with([
            'assignee',
            'creator',
            'attachments',
            'history.user',
            'project'
        ])->get();

        // Calculate project statistics
        $projectStats = [
            'total_tasks' => $allTasks->count(),
            'completed_tasks' => $allTasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $allTasks->where('status', 'in_progress')->count(),
            'pending_tasks' => $allTasks->where('status', 'pending')->count(),
            'overdue_tasks' => $allTasks->where('due_date', '<', now())->where('status', '!=', 'completed')->count(),
            'completion_rate' => $allTasks->count() > 0 ? round(($allTasks->where('status', 'completed')->count() / $allTasks->count()) * 100, 2) : 0,
        ];

        // Get task history for all tasks
        $allTaskHistory = \App\Models\TaskHistory::whereIn('task_id', $allTasks->pluck('id'))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('task_id');

        // Get team performance data
        $teamMembers = $allTasks->pluck('assignee')->filter()->unique('id');
        $teamPerformance = [];
        
        foreach ($teamMembers as $member) {
            $memberTasks = $allTasks->where('assignee_id', $member->id);
            $teamPerformance[] = [
                'user' => $member,
                'total_tasks' => $memberTasks->count(),
                'completed_tasks' => $memberTasks->where('status', 'completed')->count(),
                'in_progress_tasks' => $memberTasks->where('status', 'in_progress')->count(),
                'completion_rate' => $memberTasks->count() > 0 ? round(($memberTasks->where('status', 'completed')->count() / $memberTasks->count()) * 100, 2) : 0,
            ];
        }

        // Get project timeline
        $projectTimeline = [
            'created_at' => $project->created_at,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'updated_at' => $project->updated_at,
        ];

        // Calculate project duration
        $projectDuration = null;
        if ($project->start_date && $project->end_date) {
            $start = \Carbon\Carbon::parse($project->start_date);
            $end = \Carbon\Carbon::parse($project->end_date);
            $projectDuration = $start->diffInDays($end);
        }

        return [
            'project' => $project,
            'projectStats' => $projectStats,
            'allTasks' => $allTasks,
            'allTaskHistory' => $allTaskHistory,
            'teamPerformance' => $teamPerformance,
            'projectTimeline' => $projectTimeline,
            'projectDuration' => $projectDuration,
            'folders' => $project->folders,
            'exportDate' => now(),
        ];
    }
}
