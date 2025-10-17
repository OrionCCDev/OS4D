<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\PerformanceMetric;
use App\Models\EmployeeEvaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformanceCalculator
{
    /**
     * Calculate and store performance metrics for a user
     */
    public function calculateUserPerformance($userId, $date = null, $periodType = 'daily')
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $user = User::findOrFail($userId);

        $startDate = $this->getPeriodStartDate($date, $periodType);
        $endDate = $this->getPeriodEndDate($date, $periodType);

        $tasks = $this->getUserTasksForPeriod($userId, $startDate, $endDate);

        $metrics = $this->calculateMetrics($tasks, $user, $startDate, $endDate);

        // Store or update performance metric
        PerformanceMetric::updateOrCreate(
            [
                'user_id' => $userId,
                'metric_date' => $date,
                'period_type' => $periodType,
            ],
            $metrics
        );

        return $metrics;
    }

    /**
     * Calculate performance metrics for all users
     */
    public function calculateAllUsersPerformance($date = null, $periodType = 'daily')
    {
        $users = User::where('role', '!=', 'admin')->get();
        $results = [];

        foreach ($users as $user) {
            $results[$user->id] = $this->calculateUserPerformance($user->id, $date, $periodType);
        }

        return $results;
    }

    /**
     * Generate monthly evaluation for a user
     */
    public function generateMonthlyEvaluation($userId, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $user = User::findOrFail($userId);
        $tasks = $this->getUserTasksForPeriod($userId, $startDate, $endDate);

        $evaluationData = $this->calculateEvaluationData($tasks, $user, $startDate, $endDate);

        // Store evaluation
        $evaluation = EmployeeEvaluation::updateOrCreate(
            [
                'user_id' => $userId,
                'evaluation_type' => 'monthly',
                'evaluation_period_start' => $startDate,
            ],
            array_merge($evaluationData, [
                'evaluation_period_end' => $endDate,
                'evaluated_by' => auth()->id(),
            ])
        );

        return $evaluation;
    }

    /**
     * Generate quarterly evaluation for a user
     */
    public function generateQuarterlyEvaluation($userId, $year, $quarter)
    {
        $startDate = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
        $endDate = $startDate->copy()->addMonths(2)->endOfMonth();

        $user = User::findOrFail($userId);
        $tasks = $this->getUserTasksForPeriod($userId, $startDate, $endDate);

        $evaluationData = $this->calculateEvaluationData($tasks, $user, $startDate, $endDate);

        $evaluation = EmployeeEvaluation::updateOrCreate(
            [
                'user_id' => $userId,
                'evaluation_type' => 'quarterly',
                'evaluation_period_start' => $startDate,
            ],
            array_merge($evaluationData, [
                'evaluation_period_end' => $endDate,
                'evaluated_by' => auth()->id(),
            ])
        );

        return $evaluation;
    }

    /**
     * Generate annual evaluation for a user
     */
    public function generateAnnualEvaluation($userId, $year)
    {
        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();

        $user = User::findOrFail($userId);
        $tasks = $this->getUserTasksForPeriod($userId, $startDate, $endDate);

        $evaluationData = $this->calculateEvaluationData($tasks, $user, $startDate, $endDate);

        $evaluation = EmployeeEvaluation::updateOrCreate(
            [
                'user_id' => $userId,
                'evaluation_type' => 'annual',
                'evaluation_period_start' => $startDate,
            ],
            array_merge($evaluationData, [
                'evaluation_period_end' => $endDate,
                'evaluated_by' => auth()->id(),
            ])
        );

        return $evaluation;
    }

    /**
     * Calculate rankings for a specific period
     */
    public function calculateRankings($evaluationType, $periodStart, $periodEnd)
    {
        $evaluations = EmployeeEvaluation::forPeriod($evaluationType, $periodStart, $periodEnd)
            ->orderBy('performance_score', 'desc')
            ->get();

        $rank = 1;
        foreach ($evaluations as $evaluation) {
            $evaluation->update(['rank' => $rank]);
            $rank++;
        }

        return $evaluations;
    }

    /**
     * Get user tasks for a specific period
     */
    private function getUserTasksForPeriod($userId, $startDate, $endDate)
    {
        return Task::where('assigned_to', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Calculate performance metrics
     */
    private function calculateMetrics($tasks, $user, $startDate, $endDate)
    {
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed');
        $onTimeTasks = $completedTasks->filter(function ($task) {
            return $task->completed_at && $task->due_date && $task->completed_at <= $task->due_date;
        });
        $earlyTasks = $completedTasks->filter(function ($task) {
            return $task->completed_at && $task->due_date && $task->completed_at < $task->due_date;
        });
        $overdueTasks = $tasks->where('is_overdue', true);
        $rejectedTasks = $tasks->where('status', 'rejected');

        $completionRate = $totalTasks > 0 ? ($completedTasks->count() / $totalTasks) * 100 : 0;
        $onTimeRate = $completedTasks->count() > 0 ? ($onTimeTasks->count() / $completedTasks->count()) * 100 : 0;
        $qualityRate = $completedTasks->count() > 0 ? (($completedTasks->count() - $rejectedTasks->count()) / $completedTasks->count()) * 100 : 0;

        $averageCompletionTime = $this->calculateAverageCompletionTime($completedTasks);
        $efficiencyScore = $this->calculateEfficiencyScore($completedTasks, $averageCompletionTime);
        $overallScore = $this->calculateOverallScore($completionRate, $onTimeRate, $qualityRate, $efficiencyScore);

        return [
            'user_id' => $user->id,
            'metric_date' => $startDate,
            'period_type' => $this->getPeriodType($startDate, $endDate),
            'tasks_assigned' => $totalTasks,
            'tasks_completed' => $completedTasks->count(),
            'tasks_on_time' => $onTimeTasks->count(),
            'tasks_early' => $earlyTasks->count(),
            'tasks_overdue' => $overdueTasks->count(),
            'tasks_rejected' => $rejectedTasks->count(),
            'high_priority_tasks' => $tasks->where('priority', 'high')->count(),
            'medium_priority_tasks' => $tasks->where('priority', 'medium')->count(),
            'low_priority_tasks' => $tasks->where('priority', 'low')->count(),
            'average_completion_time' => $averageCompletionTime,
            'efficiency_score' => $efficiencyScore,
            'quality_score' => $qualityRate,
            'punctuality_score' => $onTimeRate,
            'overall_score' => $overallScore,
        ];
    }

    /**
     * Calculate evaluation data
     */
    private function calculateEvaluationData($tasks, $user, $startDate, $endDate)
    {
        $metrics = $this->calculateMetrics($tasks, $user, $startDate, $endDate);

        return [
            'performance_score' => $metrics['overall_score'],
            'tasks_completed' => $metrics['tasks_completed'],
            'on_time_completion_rate' => $metrics['punctuality_score'],
            'quality_score' => $metrics['quality_score'],
            'early_completions' => $metrics['tasks_early'],
            'overdue_tasks' => $metrics['tasks_overdue'],
            'rejected_tasks' => $metrics['tasks_rejected'],
        ];
    }

    /**
     * Calculate average completion time
     */
    private function calculateAverageCompletionTime($completedTasks)
    {
        $validTasks = $completedTasks->filter(function ($task) {
            return $task->assigned_at && $task->completed_at;
        });

        if ($validTasks->isEmpty()) {
            return 0;
        }

        $totalHours = $validTasks->sum(function ($task) {
            return $task->assigned_at->diffInHours($task->completed_at);
        });

        return round($totalHours / $validTasks->count(), 2);
    }

    /**
     * Calculate efficiency score
     */
    private function calculateEfficiencyScore($completedTasks, $averageCompletionTime)
    {
        // Base efficiency on completion time and task complexity
        $baseScore = 80;
        $timeBonus = max(0, 20 - ($averageCompletionTime / 24)); // Bonus for faster completion
        return min(100, $baseScore + $timeBonus);
    }

    /**
     * Calculate overall performance score
     */
    private function calculateOverallScore($completionRate, $onTimeRate, $qualityRate, $efficiencyScore)
    {
        $weights = [
            'completion' => 0.30,
            'punctuality' => 0.25,
            'quality' => 0.25,
            'efficiency' => 0.20,
        ];

        return round(
            ($completionRate * $weights['completion']) +
            ($onTimeRate * $weights['punctuality']) +
            ($qualityRate * $weights['quality']) +
            ($efficiencyScore * $weights['efficiency']),
            2
        );
    }

    /**
     * Get period start date
     */
    private function getPeriodStartDate($date, $periodType)
    {
        switch ($periodType) {
            case 'daily':
                return $date->startOfDay();
            case 'weekly':
                return $date->startOfWeek();
            case 'monthly':
                return $date->startOfMonth();
            case 'quarterly':
                return $date->startOfQuarter();
            case 'yearly':
                return $date->startOfYear();
            default:
                return $date->startOfDay();
        }
    }

    /**
     * Get period end date
     */
    private function getPeriodEndDate($date, $periodType)
    {
        switch ($periodType) {
            case 'daily':
                return $date->endOfDay();
            case 'weekly':
                return $date->endOfWeek();
            case 'monthly':
                return $date->endOfMonth();
            case 'quarterly':
                return $date->endOfQuarter();
            case 'yearly':
                return $date->endOfYear();
            default:
                return $date->endOfDay();
        }
    }

    /**
     * Get period type based on date range
     */
    private function getPeriodType($startDate, $endDate)
    {
        $days = $startDate->diffInDays($endDate);

        if ($days <= 1) return 'daily';
        if ($days <= 7) return 'weekly';
        if ($days <= 31) return 'monthly';
        if ($days <= 93) return 'quarterly';
        return 'yearly';
    }
}
