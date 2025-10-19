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
        try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

            $user = User::findOrFail($request->user_id);
            
            // Calculate performance metrics for the month
            $startDate = Carbon::create($request->year, $request->month, 1)->startOfMonth();
            $endDate = Carbon::create($request->year, $request->month, 1)->endOfMonth();
            
            // Check if evaluation already exists for this user and period
            $existingEvaluation = EmployeeEvaluation::where('user_id', $request->user_id)
                ->where('evaluation_type', 'monthly')
                ->where('evaluation_period_start', $startDate)
                ->first();
            
            if ($existingEvaluation) {
                // Update existing evaluation instead of creating a new one
                $metrics = $this->calculateUserMetrics($user, $startDate, $endDate);
                
                $existingEvaluation->update([
                    'evaluated_by' => auth()->id(),
                    'performance_score' => $metrics['performance_score'],
                    'tasks_completed' => $metrics['completed_tasks'],
                    'on_time_completion_rate' => $metrics['on_time_rate'],
                    'overdue_tasks' => $metrics['overdue_tasks'],
                    'status' => 'completed'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Monthly evaluation for ' . $startDate->format('F Y') . ' updated successfully',
                    'evaluation_id' => $existingEvaluation->id
                ]);
            }
            
            $metrics = $this->calculateUserMetrics($user, $startDate, $endDate);
            
            // Create evaluation record
            $evaluation = EmployeeEvaluation::create([
                'user_id' => $request->user_id,
                'evaluated_by' => auth()->id(),
                'evaluation_type' => 'monthly',
                'evaluation_period_start' => $startDate,
                'evaluation_period_end' => $endDate,
                'performance_score' => $metrics['performance_score'],
                'tasks_completed' => $metrics['completed_tasks'],
                'on_time_completion_rate' => $metrics['on_time_rate'],
                'overdue_tasks' => $metrics['overdue_tasks'],
                'status' => 'completed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Monthly evaluation generated successfully',
                'evaluation_id' => $evaluation->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error generating monthly evaluation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate quarterly evaluation
     */
    public function generateQuarterlyEvaluation(Request $request)
    {
        try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
            'quarter' => 'required|integer|min:1|max:4',
        ]);

            $user = User::findOrFail($request->user_id);
            
            // Calculate quarter dates
            $quarterMonths = [
                1 => [1, 3],   // Q1: Jan-Mar
                2 => [4, 6],   // Q2: Apr-Jun
                3 => [7, 9],   // Q3: Jul-Sep
                4 => [10, 12]  // Q4: Oct-Dec
            ];
            
            $startMonth = $quarterMonths[$request->quarter][0];
            $endMonth = $quarterMonths[$request->quarter][1];
            
            $startDate = Carbon::create($request->year, $startMonth, 1)->startOfMonth();
            $endDate = Carbon::create($request->year, $endMonth, 1)->endOfMonth();
            
            // Check if evaluation already exists for this user and period
            $existingEvaluation = EmployeeEvaluation::where('user_id', $request->user_id)
                ->where('evaluation_type', 'quarterly')
                ->where('evaluation_period_start', $startDate)
                ->first();
            
            if ($existingEvaluation) {
                // Update existing evaluation instead of creating a new one
                $metrics = $this->calculateUserMetrics($user, $startDate, $endDate);
                
                $existingEvaluation->update([
                    'evaluated_by' => auth()->id(),
                    'performance_score' => $metrics['performance_score'],
                    'tasks_completed' => $metrics['completed_tasks'],
                    'on_time_completion_rate' => $metrics['on_time_rate'],
                    'overdue_tasks' => $metrics['overdue_tasks'],
                    'status' => 'completed'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Quarterly evaluation for Q' . $request->quarter . ' ' . $request->year . ' updated successfully',
                    'evaluation_id' => $existingEvaluation->id
                ]);
            }
            
            $metrics = $this->calculateUserMetrics($user, $startDate, $endDate);
            
            // Create evaluation record
            $evaluation = EmployeeEvaluation::create([
                'user_id' => $request->user_id,
                'evaluated_by' => auth()->id(),
                'evaluation_type' => 'quarterly',
                'evaluation_period_start' => $startDate,
                'evaluation_period_end' => $endDate,
                'performance_score' => $metrics['performance_score'],
                'tasks_completed' => $metrics['completed_tasks'],
                'on_time_completion_rate' => $metrics['on_time_rate'],
                'overdue_tasks' => $metrics['overdue_tasks'],
                'status' => 'completed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quarterly evaluation generated successfully',
                'evaluation_id' => $evaluation->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error generating quarterly evaluation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate annual evaluation
     */
    public function generateAnnualEvaluation(Request $request)
    {
        try {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

            $user = User::findOrFail($request->user_id);
            
            // Calculate year dates
            $startDate = Carbon::create($request->year, 1, 1)->startOfYear();
            $endDate = Carbon::create($request->year, 12, 31)->endOfYear();
            
            // Check if evaluation already exists for this user and period
            $existingEvaluation = EmployeeEvaluation::where('user_id', $request->user_id)
                ->where('evaluation_type', 'annual')
                ->where('evaluation_period_start', $startDate)
                ->first();
            
            if ($existingEvaluation) {
                // Update existing evaluation instead of creating a new one
                $metrics = $this->calculateUserMetrics($user, $startDate, $endDate);
                
                $existingEvaluation->update([
                    'evaluated_by' => auth()->id(),
                    'performance_score' => $metrics['performance_score'],
                    'tasks_completed' => $metrics['completed_tasks'],
                    'on_time_completion_rate' => $metrics['on_time_rate'],
                    'overdue_tasks' => $metrics['overdue_tasks'],
                    'status' => 'completed'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Annual evaluation for ' . $request->year . ' updated successfully',
                    'evaluation_id' => $existingEvaluation->id
                ]);
            }
            
            $metrics = $this->calculateUserMetrics($user, $startDate, $endDate);
            
            // Create evaluation record
            $evaluation = EmployeeEvaluation::create([
                'user_id' => $request->user_id,
                'evaluated_by' => auth()->id(),
                'evaluation_type' => 'annual',
                'evaluation_period_start' => $startDate,
                'evaluation_period_end' => $endDate,
                'performance_score' => $metrics['performance_score'],
                'tasks_completed' => $metrics['completed_tasks'],
                'on_time_completion_rate' => $metrics['on_time_rate'],
                'overdue_tasks' => $metrics['overdue_tasks'],
                'status' => 'completed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Annual evaluation generated successfully',
                'evaluation_id' => $evaluation->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error generating annual evaluation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating evaluation: ' . $e->getMessage()
            ], 500);
        }
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
            
            // Generate PDF
            $pdf = Pdf::loadView('reports.pdf.full-project-report', $projectData);
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
            
            // Return a more detailed error response
            return response()->json([
                'error' => true,
                'message' => 'Failed to generate project report: ' . $e->getMessage(),
                'details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'project_id' => $project->id
                ]
            ], 500);
        }
    }

    /**
     * Get comprehensive project data including all tasks and history
     */
    private function getComprehensiveProjectData(Project $project)
    {
        \Log::info('Loading project relationships for project: ' . $project->id);
        
        // Load project with all relationships
        $project->load([
            'folders.children',
            'tasks.assignee',
            'tasks.creator',
            'tasks.attachments',
            'tasks.histories.user',
            'owner'
        ]);
        
        \Log::info('Project relationships loaded successfully');

        // Get all tasks with full details
        \Log::info('Loading tasks for project: ' . $project->id);
        $allTasks = $project->tasks()->with([
            'assignee',
            'creator',
            'attachments',
            'histories.user',
            'project'
        ])->get();
        
        \Log::info('Tasks loaded successfully', ['task_count' => $allTasks->count()]);

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

    /**
     * Calculate user metrics for a given period
     */
    private function calculateUserMetrics($user, $startDate, $endDate)
    {
        $tasks = $user->assignedTasks()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $overdueTasks = $tasks->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        
        $onTimeTasks = $tasks->where('status', 'completed')
            ->filter(function($task) {
                return $task->completed_at && $task->due_date && 
                       $task->completed_at <= $task->due_date;
            })->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        $onTimeRate = $completedTasks > 0 ? round(($onTimeTasks / $completedTasks) * 100, 1) : 0;
        
        // Calculate performance score (similar to dashboard logic)
        $performanceScore = $this->calculateAdvancedPerformanceScore($user, $startDate, $endDate);
        
        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'overdue_tasks' => $overdueTasks,
            'completion_rate' => $completionRate,
            'on_time_rate' => $onTimeRate,
            'performance_score' => $performanceScore
        ];
    }

    /**
     * Calculate advanced performance score for a user
     */
    private function calculateAdvancedPerformanceScore($user, $startDate = null, $endDate = null)
    {
        $query = $user->assignedTasks();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $tasks = $query->get();
        
        if ($tasks->isEmpty()) {
            return 0;
        }
        
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->whereIn('status', ['in_progress', 'workingon', 'assigned'])->count();
        $rejectedTasks = $tasks->where('status', 'rejected')->count();
        $overdueTasks = $tasks->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();
        $onTimeCompleted = $tasks->where('status', 'completed')
            ->filter(function($task) {
                return $task->completed_at && $task->due_date && 
                       $task->completed_at <= $task->due_date;
            })->count();
        
        $totalTasks = $tasks->count();
        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        
        // Base score from completed tasks
        $baseScore = $completedTasks * 10;
        
        // Bonus for in-progress tasks
        $progressBonus = $inProgressTasks * 5;
        
        // Penalties
        $rejectionPenalty = $rejectedTasks * 15;
        $overduePenalty = $overdueTasks * 10;
        
        // On-time bonus
        $onTimeBonus = $onTimeCompleted * 5;
        
        // Experience multiplier
        $experienceMultiplier = $this->calculateExperienceMultiplier($totalTasks);
        
        // Calculate final score
        $rawScore = ($baseScore + $progressBonus + $onTimeBonus - $rejectionPenalty - $overduePenalty) * $experienceMultiplier;
        $finalScore = max(0, min(100, $rawScore));
        
        return round($finalScore, 1);
    }

    /**
     * Calculate experience multiplier based on total tasks
     */
    private function calculateExperienceMultiplier($totalTasks)
    {
        if ($totalTasks >= 50) return 1.2;      // Experienced
        if ($totalTasks >= 20) return 1.1;      // Intermediate
        if ($totalTasks >= 10) return 1.0;      // Standard
        if ($totalTasks >= 5) return 0.9;       // New
        return 0.8;                             // Very new
    }
}
