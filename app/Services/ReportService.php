<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\PerformanceMetric;
use App\Models\EmployeeEvaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generate project overview report
     */
    public function getProjectOverviewReport($filters = [], $request = null)
    {
        $query = Project::with(['tasks', 'users', 'owner', 'folders']);

        // Apply search filter
        if ($request && $request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('short_code', 'like', "%{$searchTerm}%");
            });
        }

        // Apply filters
        if (isset($filters['status']) && !empty($filters['status']) && $filters['status'] !== 'all') {
            $statusArray = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
            $query->whereIn('status', $statusArray);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Get paginated results
        $projects = $query->paginate(10);

        // Transform the data
        $transformedProjects = $projects->getCollection()->map(function ($project) {
            // Build a robust task set: include any task linked to this project OR to any folder within it
            $allFolderIds = \App\Models\ProjectFolder::where('project_id', $project->id)->pluck('id');
            $tasks = \App\Models\Task::query()
                ->where('project_id', $project->id)
                ->orWhereIn('folder_id', $allFolderIds)
                ->get();

            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('status', 'completed')->count();
            $overdueTasks = $tasks->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count();

            // Team members based on assigned users across tasks (fallback if pivot table unused)
            $assignedUserIds = $tasks->pluck('assigned_to')->filter()->unique();
            $usersInvolved = \App\Models\User::whereIn('id', $assignedUserIds)->pluck('name')->toArray();
            $teamSize = count($assignedUserIds);

            // Due date fallback: earliest task due date if project due date missing
            $derivedDueDate = $project->due_date ?: $tasks->filter(fn($t) => !empty($t->due_date))->min('due_date');

            // Count sub-folders (only direct children, not nested)
            $subFoldersCount = $project->folders()->whereNull('parent_id')->count();

            return [
                'id' => $project->id,
                'name' => $project->name,
                'short_code' => $project->short_code ?? 'N/A',
                'status' => $project->status,
                'owner' => $project->owner->name ?? 'N/A',
                'start_date' => $project->start_date,
                'due_date' => $derivedDueDate,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'overdue_tasks' => $overdueTasks,
                'completion_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
                'team_size' => $teamSize,
                'sub_folders_count' => $subFoldersCount,
                'users_involved' => $usersInvolved,
                'created_at' => $project->created_at,
            ];
        });

        // Replace the collection in the paginator
        $projects->setCollection($transformedProjects);

        return $projects;
    }

    /**
     * Get detailed project progress report with comprehensive data
     */
    public function getDetailedProjectProgress($filters = [], $request = null)
    {
        $query = Project::with(['tasks.assignee', 'tasks.creator', 'users', 'owner', 'folders']);

        // Apply project_id filter if specified
        if (isset($filters['project_id']) && $filters['project_id']) {
            $query->where('id', $filters['project_id']);
        }

        // Apply search filter
        if ($request && $request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('short_code', 'like', "%{$searchTerm}%");
            });
        }

        // Apply filters
        if (isset($filters['status']) && !empty($filters['status']) && $filters['status'] !== 'all') {
            $statusArray = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
            $query->whereIn('status', $statusArray);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Get paginated results
        $projects = $query->paginate(10);

        // Transform the data with detailed information
        $transformedProjects = $projects->getCollection()->map(function ($project) {
            // Use same robust task set as overview to keep numbers consistent
            $allFolderIds = \App\Models\ProjectFolder::where('project_id', $project->id)->pluck('id');
            $tasks = \App\Models\Task::query()
                ->where('project_id', $project->id)
                ->orWhereIn('folder_id', $allFolderIds)
                ->get();
            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('status', 'completed');
            $completedTasksCount = $completedTasks->count();

            // Task status breakdown
            $pendingTasks = $tasks->where('status', 'pending')->count();
            $inProgressTasks = $tasks->where('status', 'in_progress')->count();
            $onHoldTasks = $tasks->where('status', 'on_hold')->count();

            // Overdue and on-time analysis
            $overdueTasks = $tasks->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count();

            $onTimeTasks = $completedTasks->filter(function ($task) {
                return $task->completed_at && $task->due_date && $task->completed_at <= $task->due_date;
            })->count();

            // Priority breakdown
            $highPriorityTasks = $tasks->where('priority', 'high')->count();
            $mediumPriorityTasks = $tasks->where('priority', 'medium')->count();
            $lowPriorityTasks = $tasks->where('priority', 'low')->count();

            // Team performance - users derived from assignments
            $assignedUserIds = $tasks->pluck('assigned_to')->filter()->unique();
            $teamMembers = User::whereIn('id', $assignedUserIds)->get();

            $teamPerformance = $teamMembers->map(function ($user) use ($tasks) {
                $userTasks = $tasks->where('assigned_to', $user->id);
                $userCompletedTasks = $userTasks->where('status', 'completed')->count();
                $userTotalTasks = $userTasks->count();

                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'total_tasks' => $userTotalTasks,
                    'completed_tasks' => $userCompletedTasks,
                    'pending_tasks' => $userTasks->whereIn('status', ['pending', 'assigned', 'in_progress', 'workingon'])->count(),
                    'overdue_tasks' => $userTasks->where('status', '!=', 'completed')
                        ->where('due_date', '<', now())
                        ->count(),
                    'completion_rate' => $userTotalTasks > 0 ? round(($userCompletedTasks / $userTotalTasks) * 100, 2) : 0,
                    'avg_task_duration' => $this->calculateAverageTaskDuration($userTasks),
                    'last_activity' => $userTasks->max('updated_at'),
                ];
            })->sortByDesc('completion_rate')->values();

            // Recent tasks (last 10) - Enhanced for PDF export
            $recentTasks = $tasks->sortByDesc('updated_at')->take(10)->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->title, // Fixed: Use title instead of name
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'assignee' => $task->assignee->name ?? 'Unassigned',
                    'assignee_email' => $task->assignee->email ?? null,
                    'created_by' => $task->creator->name ?? 'Unknown',
                    'created_at' => $task->created_at,
                    'assigned_at' => $task->assigned_at,
                    'started_at' => $task->started_at,
                    'due_date' => $task->due_date,
                    'completed_at' => $task->completed_at,
                    'completion_notes' => $task->completion_notes,
                    'is_overdue' => $task->status != 'completed' && $task->due_date && $task->due_date < now(),
                    'days_overdue' => $task->status != 'completed' && $task->due_date && $task->due_date < now()
                        ? now()->diffInDays($task->due_date)
                        : 0,
                    'days_remaining' => $task->status != 'completed' && $task->due_date
                        ? now()->diffInDays($task->due_date, false)
                        : null,
                    'progress_percentage' => $this->calculateTaskProgress($task),
                    'progress_status' => $this->getTaskProgressStatus($task),
                    'progress_stage' => $this->getTaskProgressStage($task),
                ];
            })->values();

            // Timeline analysis
            $projectDuration = $project->created_at->diffInDays(now());
            $expectedDuration = $project->due_date ? $project->created_at->diffInDays($project->due_date) : null;
            $daysRemaining = $project->due_date ? now()->diffInDays($project->due_date, false) : null;

            // Calculate estimated completion date based on current progress
            $estimatedCompletionDate = null;
            if ($completedTasksCount > 0 && $totalTasks > 0 && $projectDuration > 0) {
                $tasksPerDay = $completedTasksCount / $projectDuration;
                $remainingTasks = $totalTasks - $completedTasksCount;
                $estimatedDaysToComplete = $tasksPerDay > 0 ? ceil($remainingTasks / $tasksPerDay) : 0;
                $estimatedCompletionDate = now()->addDays($estimatedDaysToComplete);
            }

            // Count sub-folders
            $subFoldersCount = $project->folders()->whereNull('parent_id')->count();

            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'short_code' => $project->short_code ?? 'N/A',
                'status' => $project->status,
                'owner' => $project->owner->name ?? 'N/A',
                'owner_email' => $project->owner->email ?? 'N/A',
                'start_date' => $project->start_date,
                'due_date' => $project->due_date,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,

                // Task statistics
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasksCount,
                'pending_tasks' => $pendingTasks,
                'in_progress_tasks' => $inProgressTasks,
                'on_hold_tasks' => $onHoldTasks,
                'overdue_tasks' => $overdueTasks,
                'on_time_tasks' => $onTimeTasks,

                // Priority breakdown
                'high_priority_tasks' => $highPriorityTasks,
                'medium_priority_tasks' => $mediumPriorityTasks,
                'low_priority_tasks' => $lowPriorityTasks,

                // Completion metrics
                'completion_percentage' => $totalTasks > 0 ? round(($completedTasksCount / $totalTasks) * 100, 2) : 0,
                'on_time_completion_rate' => $completedTasksCount > 0 ? round(($onTimeTasks / $completedTasksCount) * 100, 2) : 0,

                // Team information
                'team_size' => $assignedUserIds->count(),
                'users_involved' => $teamMembers->pluck('name')->toArray(),
                'team_performance' => $teamPerformance,

                // Timeline data
                'project_duration_days' => $projectDuration,
                'expected_duration_days' => $expectedDuration,
                'days_remaining' => $daysRemaining,
                'estimated_completion_date' => $estimatedCompletionDate,
                'is_on_schedule' => $estimatedCompletionDate && $project->due_date ?
                    $estimatedCompletionDate <= $project->due_date : true,

                // Additional details
                'sub_folders_count' => $subFoldersCount,
                'recent_tasks' => $recentTasks,

                // Risk indicators
                'has_overdue_tasks' => $overdueTasks > 0,
                'overdue_percentage' => $totalTasks > 0 ? round(($overdueTasks / $totalTasks) * 100, 2) : 0,
                'is_at_risk' => $overdueTasks > 0 || ($daysRemaining !== null && $daysRemaining < 7 && $completedTasksCount < $totalTasks * 0.8),
            ];
        });

        // Replace the collection in the paginator
        $projects->setCollection($transformedProjects);

        return $projects;
    }

    /**
     * Generate task completion report
     */
    public function getTaskCompletionReport($filters = [])
    {
        $query = Task::with(['project', 'assignee', 'creator']);

        // Apply filters
        if (!empty($filters['project_id']) && $filters['project_id'] !== 'all') {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['user_id']) && $filters['user_id'] !== 'all') {
            $query->where('assigned_to', $filters['user_id']);
        }

        if (!empty($filters['status']) && is_array($filters['status']) && !in_array('all', $filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        if (!empty($filters['priority']) && is_array($filters['priority']) && !in_array('all', $filters['priority'])) {
            $query->whereIn('priority', $filters['priority']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Add search functionality
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('project', function($projectQuery) use ($searchTerm) {
                      $projectQuery->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('assignee', function($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Get all tasks for statistics
        $allTasks = $query->get();

        // Get paginated tasks for display
        $paginatedTasks = $query->orderBy('created_at', 'desc')->paginate(15);

        return [
            'total_tasks' => $allTasks->count(),
            'completed_tasks' => $allTasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $allTasks->whereIn('status', ['in_progress', 'workingon', 'assigned'])->count(),
            'overdue_tasks' => $allTasks->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
            'completion_rate' => $allTasks->count() > 0 ? round(($allTasks->where('status', 'completed')->count() / $allTasks->count()) * 100, 2) : 0,
            'tasks_by_priority' => $this->groupTasksByPriority($allTasks),
            'tasks_by_status' => $this->groupTasksByStatus($allTasks),
            'average_completion_time' => $this->calculateAverageCompletionTime($allTasks),
            'tasks' => $paginatedTasks, // Paginated task list for display
        ];
    }

    /**
     * Generate user performance report
     */
    public function getUserPerformanceReport($userId, $filters = [])
    {
        $user = User::findOrFail($userId);

        $query = Task::where('assigned_to', $userId);

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $tasks = $query->get();

        $completedTasks = $tasks->where('status', 'completed');
        $onTimeTasks = $completedTasks->filter(function ($task) {
            return $task->completed_at && $task->due_date && $task->completed_at <= $task->due_date;
        });

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'position' => $user->position,
            ],
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $completedTasks->count(),
            'on_time_tasks' => $onTimeTasks->count(),
            'overdue_tasks' => $tasks->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
            'completion_rate' => $tasks->count() > 0 ? round(($completedTasks->count() / $tasks->count()) * 100, 2) : 0,
            'on_time_rate' => $completedTasks->count() > 0 ? round(($onTimeTasks->count() / $completedTasks->count()) * 100, 2) : 0,
            'average_completion_time' => $this->calculateAverageCompletionTime($completedTasks),
            'tasks_by_priority' => $this->groupTasksByPriority($tasks),
            'performance_score' => $this->calculatePerformanceScore($tasks),
        ];
    }

    /**
     * Generate employee rankings
     */
    public function getEmployeeRankings($filters = [])
    {
        $query = User::where('role', '!=', 'admin');

        // Apply user filter if provided
        if (!empty($filters['user_id'])) {
            $query->where('id', $filters['user_id']);
        }

        $users = $query->get();

        $rankings = $users->map(function ($user) use ($filters) {
            $userReport = $this->getUserPerformanceReport($user->id, $filters);
            return [
                'user' => $userReport['user'],
                'performance_score' => $userReport['performance_score'],
                'completion_rate' => $userReport['completion_rate'],
                'on_time_rate' => $userReport['on_time_rate'],
                'total_tasks' => $userReport['total_tasks'],
                'completed_tasks' => $userReport['completed_tasks'],
            ];
        })->sortByDesc('performance_score')->values();

        // Add ranking numbers
        $rankings = $rankings->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        return $rankings;
    }

    /**
     * Generate monthly evaluation data
     */
    public function generateMonthlyEvaluation($userId, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $user = User::findOrFail($userId);
        $userReport = $this->getUserPerformanceReport($userId, [
            'date_from' => $startDate,
            'date_to' => $endDate,
        ]);

        // Calculate performance score with weighted factors
        $performanceScore = $this->calculateWeightedPerformanceScore($userReport);

        return [
            'user_id' => $userId,
            'evaluation_type' => 'monthly',
            'evaluation_period_start' => $startDate,
            'evaluation_period_end' => $endDate,
            'performance_score' => $performanceScore,
            'tasks_completed' => $userReport['completed_tasks'],
            'on_time_completion_rate' => $userReport['on_time_rate'],
            'quality_score' => $this->calculateQualityScore($userReport),
            'early_completions' => $this->calculateEarlyCompletions($userId, $startDate, $endDate),
            'overdue_tasks' => $userReport['overdue_tasks'],
            'rejected_tasks' => $this->calculateRejectedTasks($userId, $startDate, $endDate),
        ];
    }

    /**
     * Group tasks by priority
     */
    private function groupTasksByPriority($tasks)
    {
        return $tasks->groupBy('priority')->map(function ($group) {
            return $group->count();
        });
    }

    /**
     * Group tasks by status
     */
    private function groupTasksByStatus($tasks)
    {
        return $tasks->groupBy('status')->map(function ($group) {
            return $group->count();
        });
    }

    /**
     * Calculate average completion time
     */
    private function calculateAverageCompletionTime($tasks)
    {
        $completedTasks = $tasks->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_at');

        if ($completedTasks->isEmpty()) {
            return 0;
        }

        $totalHours = $completedTasks->sum(function ($task) {
            return $task->assigned_at->diffInHours($task->completed_at);
        });

        return round($totalHours / $completedTasks->count(), 2);
    }

    /**
     * Calculate performance score
     */
    private function calculatePerformanceScore($tasks)
    {
        if ($tasks->isEmpty()) {
            return 0;
        }

        $completedTasks = $tasks->where('status', 'completed');
        $completionRate = ($completedTasks->count() / $tasks->count()) * 100;

        $onTimeTasks = $completedTasks->filter(function ($task) {
            return $task->completed_at && $task->due_date && $task->completed_at <= $task->due_date;
        });

        $onTimeRate = $completedTasks->count() > 0 ? ($onTimeTasks->count() / $completedTasks->count()) * 100 : 0;

        return round(($completionRate * 0.6) + ($onTimeRate * 0.4), 2);
    }

    /**
     * Calculate weighted performance score
     */
    private function calculateWeightedPerformanceScore($userReport)
    {
        $completionWeight = 0.30;
        $onTimeWeight = 0.25;
        $qualityWeight = 0.20;
        $efficiencyWeight = 0.15;
        $earlyBonusWeight = 0.10;

        $completionScore = $userReport['completion_rate'];
        $onTimeScore = $userReport['on_time_rate'];
        $qualityScore = $this->calculateQualityScore($userReport);
        $efficiencyScore = $this->calculateEfficiencyScore($userReport);
        $earlyBonus = $this->calculateEarlyBonus($userReport);

        return round(
            ($completionScore * $completionWeight) +
            ($onTimeScore * $onTimeWeight) +
            ($qualityScore * $qualityWeight) +
            ($efficiencyScore * $efficiencyWeight) +
            ($earlyBonus * $earlyBonusWeight),
            2
        );
    }

    /**
     * Calculate quality score
     */
    private function calculateQualityScore($userReport)
    {
        // This would be based on approval rates, rejection rates, etc.
        // For now, return a default quality score
        return 85.0;
    }

    /**
     * Calculate efficiency score
     */
    private function calculateEfficiencyScore($userReport)
    {
        // This would be based on average completion time, task complexity, etc.
        // For now, return a default efficiency score
        return 80.0;
    }

    /**
     * Calculate early completion bonus
     */
    private function calculateEarlyBonus($userReport)
    {
        // This would be based on tasks completed before due date
        // For now, return a default early bonus
        return 10.0;
    }

    /**
     * Calculate early completions
     */
    private function calculateEarlyCompletions($userId, $startDate, $endDate)
    {
        return Task::where('assigned_to', $userId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->whereColumn('completed_at', '<', 'due_date')
            ->count();
    }

    /**
     * Get user rankings for dashboard display
     */
    public function getUserRankings($userId, $period = 'overall')
    {
        $user = User::findOrFail($userId);

        // Get all users for comparison
        $allUsers = User::where('role', '!=', 'admin')->get();

        $rankings = $allUsers->map(function ($u) use ($period) {
            $filters = [];

            // Apply period filter
            if ($period === 'monthly') {
                $filters = [
                    'date_from' => now()->startOfMonth(),
                    'date_to' => now()->endOfMonth()
                ];
            }

            $userReport = $this->getUserPerformanceReport($u->id, $filters);
            return [
                'user_id' => $u->id,
                'user_name' => $u->name,
                'performance_score' => $userReport['performance_score'],
                'completion_rate' => $userReport['completion_rate'],
                'total_tasks' => $userReport['total_tasks'],
                'completed_tasks' => $userReport['completed_tasks'],
            ];
        })->sortByDesc('performance_score')->values();

        // Add ranking numbers and find current user
        $userRanking = null;
        $rankings = $rankings->map(function ($item, $index) use ($userId, &$userRanking) {
            $item['rank'] = $index + 1;
            if ($item['user_id'] == $userId) {
                $userRanking = $item;
            }
            return $item;
        });

        return [
            'user_ranking' => $userRanking,
            'total_users' => $rankings->count(),
            'period' => $period,
            'top_3' => $rankings->take(3)->values(),
        ];
    }

    /**
     * Calculate rejected tasks
     */
    private function calculateRejectedTasks($userId, $startDate, $endDate)
    {
        return Task::where('assigned_to', $userId)
            ->where('status', 'rejected')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Calculate average task duration for a user
     */
    private function calculateAverageTaskDuration($userTasks)
    {
        $completedTasks = $userTasks->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereNotNull('assigned_at');

        if ($completedTasks->count() === 0) {
            return 0;
        }

        $totalDays = $completedTasks->sum(function ($task) {
            return $task->assigned_at->diffInDays($task->completed_at);
        });

        return round($totalDays / $completedTasks->count(), 1);
    }

    /**
     * Calculate task progress percentage
     */
    private function calculateTaskProgress($task)
    {
        $statusProgressMap = [
            'pending' => 0,
            'assigned' => 5,
            'in_progress' => 15,
            'submitted_for_review' => 30,
            'in_review' => 50,
            'approved' => 70,
            'ready_for_email' => 85,
            'on_client_consultant_review' => 90,
            'in_review_after_client_consultant_reply' => 95,
            're_submit_required' => 60,
            'rejected' => 0,
            'completed' => 100,
        ];

        return $statusProgressMap[$task->status] ?? 0;
    }

    /**
     * Get task progress status description
     */
    private function getTaskProgressStatus($task)
    {
        $statusDescriptions = [
            'pending' => 'Not Started',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'submitted_for_review' => 'Under Internal Review',
            'in_review' => 'Internal Review',
            'approved' => 'Internally Approved',
            'ready_for_email' => 'Ready for Client',
            'on_client_consultant_review' => 'Client Review',
            'in_review_after_client_consultant_reply' => 'Processing Client Feedback',
            're_submit_required' => 'Resubmission Required',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
        ];

        return $statusDescriptions[$task->status] ?? 'Unknown';
    }

    /**
     * Get task progress stage
     */
    private function getTaskProgressStage($task)
    {
        $userWorkStages = [
            'pending', 'assigned', 'in_progress', 'submitted_for_review',
            'in_review', 'approved', 'ready_for_email'
        ];

        $clientReviewStages = [
            'on_client_consultant_review', 'in_review_after_client_consultant_reply'
        ];

        if (in_array($task->status, $userWorkStages)) {
            return 'user_work';
        } elseif (in_array($task->status, $clientReviewStages)) {
            return 'client_review';
        } elseif ($task->status === 'completed') {
            return 'completed';
        } else {
            return 'pending';
        }
    }
}
