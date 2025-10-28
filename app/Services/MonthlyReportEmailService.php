<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\EmployeeEvaluation;
use App\Mail\MonthlyReportEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MonthlyReportEmailService
{
    protected $reportService;
    protected $performanceCalculator;

    public function __construct(ReportService $reportService, PerformanceCalculator $performanceCalculator)
    {
        $this->reportService = $reportService;
        $this->performanceCalculator = $performanceCalculator;
    }

    /**
     * Send monthly reports to all users
     */
    public function sendMonthlyReportsToAllUsers()
    {
        $previousMonth = Carbon::now()->subMonth();
        $year = $previousMonth->year;
        $month = $previousMonth->month;

        Log::info("Starting monthly report generation for {$previousMonth->format('F Y')}");

        // Get all non-admin users
        $users = User::where('role', '!=', 'admin')->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            try {
                $this->sendMonthlyReportToUser($user, $year, $month);
                $successCount++;
                Log::info("Monthly report sent successfully to {$user->email}");
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Failed to send monthly report to {$user->email}: " . $e->getMessage());
            }
        }

        Log::info("Monthly report sending completed. Success: {$successCount}, Errors: {$errorCount}");

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_users' => $users->count()
        ];
    }

    /**
     * Send monthly report to a specific user
     */
    public function sendMonthlyReportToUser(User $user, int $year, int $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get user's evaluation data
        $evaluation = $this->getOrCreateUserEvaluation($user, $year, $month);

        // Get user's project tasks data
        $projectTasksData = $this->getUserProjectTasksData($user, $startDate, $endDate);

        // Get performance metrics
        $performanceMetrics = $this->getUserPerformanceMetrics($user, $startDate, $endDate);

        // Generate PDF report
        $pdfPath = $this->generateUserMonthlyReportPDF($user, $evaluation, $projectTasksData, $performanceMetrics, $startDate, $endDate);

        // Send email
        $this->sendEmailWithReport($user, $evaluation, $projectTasksData, $performanceMetrics, $startDate, $endDate, $pdfPath);

        // Clean up PDF file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }
    }

    /**
     * Get or create user evaluation for the month
     */
    private function getOrCreateUserEvaluation(User $user, int $year, int $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Check if evaluation exists
        $evaluation = EmployeeEvaluation::where('user_id', $user->id)
            ->where('evaluation_type', 'monthly')
            ->where('evaluation_period_start', $startDate)
            ->first();

        if (!$evaluation) {
            // Create evaluation if it doesn't exist
            $metrics = $this->calculateUserMetrics($user, $startDate, $endDate);

            $evaluation = EmployeeEvaluation::create([
                'user_id' => $user->id,
                'evaluated_by' => 1, // System user
                'evaluation_type' => 'monthly',
                'evaluation_period_start' => $startDate,
                'evaluation_period_end' => $endDate,
                'performance_score' => $metrics['performance_score'],
                'tasks_completed' => $metrics['completed_tasks'],
                'on_time_completion_rate' => $metrics['on_time_rate'],
                'overdue_tasks' => $metrics['overdue_tasks'],
                'status' => 'completed'
            ]);
        }

        return $evaluation;
    }

    /**
     * Get user's project tasks data
     */
    private function getUserProjectTasksData(User $user, Carbon $startDate, Carbon $endDate)
    {
        $tasks = Task::where('assigned_to', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['project', 'creator'])
            ->get();

        $projectStats = [];
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->whereIn('status', ['in_progress', 'workingon', 'assigned'])->count();
        $overdueTasks = $tasks->where('due_date', '<', now()->startOfDay())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        // Group by project
        $tasksByProject = $tasks->groupBy('project_id');

        foreach ($tasksByProject as $projectId => $projectTasks) {
            $project = $projectTasks->first()->project;
            if ($project) {
                $projectStats[] = [
                    'project' => $project,
                    'total_tasks' => $projectTasks->count(),
                    'completed_tasks' => $projectTasks->where('status', 'completed')->count(),
                    'in_progress_tasks' => $projectTasks->whereIn('status', ['in_progress', 'workingon', 'assigned'])->count(),
                    'overdue_tasks' => $projectTasks->where('due_date', '<', now()->startOfDay())
                        ->whereNotIn('status', ['completed', 'cancelled'])
                        ->count(),
                    'completion_rate' => $projectTasks->count() > 0 ?
                        round(($projectTasks->where('status', 'completed')->count() / $projectTasks->count()) * 100, 1) : 0,
                    'tasks' => $projectTasks->take(10) // Limit to 10 most recent tasks per project
                ];
            }
        }

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'overdue_tasks' => $overdueTasks,
            'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
            'projects' => $projectStats
        ];
    }

    /**
     * Get user performance metrics
     */
    private function getUserPerformanceMetrics(User $user, Carbon $startDate, Carbon $endDate)
    {
        return $this->calculateUserMetrics($user, $startDate, $endDate);
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
        $overdueTasks = $tasks->where('due_date', '<', now()->startOfDay())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $onTimeTasks = $tasks->where('status', 'completed')
            ->filter(function($task) {
                return $task->completed_at && $task->due_date &&
                       $task->completed_at <= $task->due_date;
            })->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        $onTimeRate = $completedTasks > 0 ? round(($onTimeTasks / $completedTasks) * 100, 1) : 0;

        // Calculate performance score
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
     * Calculate advanced performance score
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
        $overdueTasks = $tasks->where('due_date', '<', now()->startOfDay())
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
     * Calculate experience multiplier
     */
    private function calculateExperienceMultiplier($totalTasks)
    {
        if ($totalTasks >= 50) return 1.2;      // Experienced
        if ($totalTasks >= 20) return 1.1;      // Intermediate
        if ($totalTasks >= 10) return 1.0;      // Standard
        if ($totalTasks >= 5) return 0.9;       // New
        return 0.8;                             // Very new
    }

    /**
     * Generate PDF report for user
     */
    private function generateUserMonthlyReportPDF(User $user, $evaluation, $projectTasksData, $performanceMetrics, $startDate, $endDate)
    {
        $pdf = Pdf::loadView('emails.pdf.monthly-report', [
            'user' => $user,
            'evaluation' => $evaluation,
            'projectTasksData' => $projectTasksData,
            'performanceMetrics' => $performanceMetrics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'monthYear' => $startDate->format('F Y'),
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'Monthly_Report_' . $user->name . '_' . $startDate->format('F_Y') . '.pdf';
        $filepath = storage_path('app/temp/' . $filename);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $pdf->save($filepath);

        return $filepath;
    }

    /**
     * Send email with report
     */
    private function sendEmailWithReport(User $user, $evaluation, $projectTasksData, $performanceMetrics, $startDate, $endDate, $pdfPath)
    {
        $monthYear = $startDate->format('F Y');

        $emailData = [
            'user' => $user,
            'evaluation' => $evaluation,
            'projectTasksData' => $projectTasksData,
            'performanceMetrics' => $performanceMetrics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'monthYear' => $monthYear,
            'pdfPath' => $pdfPath,
            'generatedAt' => now()
        ];

        // Send to user
        Mail::to($user->email)
            ->cc(['mohab@orioncc.com', 'engineering@orion-contracting.com'])
            ->send(new MonthlyReportEmail($emailData));
    }

    /**
     * Send test email to specific user
     */
    public function sendTestMonthlyReport($userEmail)
    {
        $user = User::where('email', $userEmail)->first();

        if (!$user) {
            throw new \Exception("User with email {$userEmail} not found");
        }

        $previousMonth = Carbon::now()->subMonth();
        $year = $previousMonth->year;
        $month = $previousMonth->month;

        Log::info("Sending test monthly report to {$userEmail} for {$previousMonth->format('F Y')}");

        $this->sendMonthlyReportToUser($user, $year, $month);

        return "Test monthly report sent successfully to {$userEmail}";
    }
}
