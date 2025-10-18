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
        $query = Project::with(['tasks', 'users', 'owner']);

        // Apply search filter
        if ($request && $request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('short_code', 'like', "%{$searchTerm}%");
            });
        }

        // Apply filters
        if (isset($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Get paginated results
        $projects = $query->paginate(10);

        // Transform the data
        $transformedProjects = $projects->getCollection()->map(function ($project) {
            $totalTasks = $project->tasks->count();
            $completedTasks = $project->tasks->where('status', 'completed')->count();
            $overdueTasks = $project->tasks->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count();

            // Count sub-folders (assuming you have a folders relationship or similar)
            $subFoldersCount = 0; // This would need to be implemented based on your folder structure

            return [
                'id' => $project->id,
                'name' => $project->name,
                'short_code' => $project->short_code ?? 'N/A',
                'status' => $project->status,
                'owner' => $project->owner->name ?? 'N/A',
                'start_date' => $project->start_date,
                'due_date' => $project->due_date,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'overdue_tasks' => $overdueTasks,
                'completion_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
                'team_size' => $project->users->count(),
                'sub_folders_count' => $subFoldersCount,
                'users_involved' => $project->users->pluck('name')->toArray(),
                'created_at' => $project->created_at,
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
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('assigned_to', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $tasks = $query->get();

        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'overdue_tasks' => $tasks->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
            'completion_rate' => $tasks->count() > 0 ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 2) : 0,
            'tasks_by_priority' => $this->groupTasksByPriority($tasks),
            'tasks_by_status' => $this->groupTasksByStatus($tasks),
            'average_completion_time' => $this->calculateAverageCompletionTime($tasks),
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
     * Calculate rejected tasks
     */
    private function calculateRejectedTasks($userId, $startDate, $endDate)
    {
        return Task::where('assigned_to', $userId)
            ->where('status', 'rejected')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }
}
