<?php

// Task Scoring System
// This file contains methods to calculate individual task scores

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class TaskScoringService
{
    /**
     * Calculate individual task score based on various factors
     */
    public function calculateTaskScore(Task $task, User $user = null)
    {
        $score = 0;
        $breakdown = [];

        // Base score for task completion
        if ($task->status === 'completed') {
            $score += 10;
            $breakdown['completed'] = 10;
        } elseif (in_array($task->status, ['in_progress', 'workingon', 'assigned'])) {
            $score += 5;
            $breakdown['in_progress'] = 5;
        }

        // On-time email sending bonus - NEW LOGIC: based on email confirmation sending
        if ($task->hasEmailConfirmationSent() && $task->due_date) {
            $emailSentAt = $task->getEmailConfirmationSentAt();
            if ($emailSentAt && $emailSentAt->startOfDay() <= $task->due_date->startOfDay()) {
                $score += 3;
                $breakdown['on_time_email_bonus'] = 3;
            } else {
                $score -= 2;
                $breakdown['late_email_penalty'] = -2;
            }
        }

        // Priority bonus
        $priorityBonus = $this->getPriorityBonus($task->priority);
        if ($priorityBonus > 0) {
            $score += $priorityBonus;
            $breakdown['priority_bonus'] = $priorityBonus;
        }

        // Quality bonus (based on completion without rejections)
        if ($task->status === 'completed' && !$this->hasBeenRejected($task)) {
            $score += 2;
            $breakdown['quality_bonus'] = 2;
        }

        // Overdue penalty - NEW LOGIC: based on email confirmation sending, not task completion
        if ($task->is_overdue) {
            $score -= 5;
            $breakdown['overdue_penalty'] = -5;
        }

        // Rejection penalty
        if ($task->status === 'rejected') {
            $score -= 8;
            $breakdown['rejection_penalty'] = -8;
        }

        // Experience multiplier (if user provided)
        if ($user) {
            $experienceMultiplier = $this->getExperienceMultiplier($user);
            if ($experienceMultiplier > 1.0) {
                $originalScore = $score;
                $score = $score * $experienceMultiplier;
                $breakdown['experience_multiplier'] = $experienceMultiplier;
                $breakdown['experience_bonus'] = $score - $originalScore;
            }
        }

        // Ensure score is not negative
        $score = max(0, round($score, 2));

        return [
            'score' => $score,
            'breakdown' => $breakdown,
            'status' => $task->status,
            'priority' => $task->priority,
            'is_overdue' => $task->is_overdue, // Use Task model's accessor (checks email confirmation)
            'is_on_time' => $task->status === 'completed' && $task->completed_at && $task->due_date && $task->completed_at <= $task->due_date,
            'has_been_rejected' => $this->hasBeenRejected($task),
        ];
    }

    /**
     * Get priority bonus based on task priority
     */
    private function getPriorityBonus($priority)
    {
        $bonuses = [
            'critical' => 6, // Critical - highest bonus
            'urgent' => 5,   // Urgent
            'high' => 3,     // High
            'medium' => 2,   // Medium
            'normal' => 1,   // Normal
            'low' => 0,      // Low - no bonus
        ];

        return $bonuses[$priority] ?? 0;
    }

    /**
     * Check if task has been rejected (simplified check)
     */
    private function hasBeenRejected($task)
    {
        // Check if task status is rejected
        return $task->status === 'rejected';
    }

    /**
     * Get experience multiplier for user
     */
    private function getExperienceMultiplier($user)
    {
        $totalTasks = $user->assignedTasks()->count();

        if ($totalTasks <= 5) return 1.0;      // New
        if ($totalTasks <= 15) return 1.1;     // Beginner
        if ($totalTasks <= 30) return 1.2;     // Experienced
        if ($totalTasks <= 50) return 1.3;     // Veteran
        return 1.4;                             // Expert
    }

    /**
     * Get score explanation for popover
     */
    public function getScoreExplanation($scoreData)
    {
        $explanations = [];

        if (isset($scoreData['breakdown']['completed'])) {
            $explanations[] = "✅ Completed task: +{$scoreData['breakdown']['completed']} points";
        }

        if (isset($scoreData['breakdown']['in_progress'])) {
            $explanations[] = "🔄 In progress: +{$scoreData['breakdown']['in_progress']} points";
        }

        if (isset($scoreData['breakdown']['on_time_email_bonus'])) {
            $explanations[] = "⏰ On-time email confirmation: +{$scoreData['breakdown']['on_time_email_bonus']} points";
        }

        if (isset($scoreData['breakdown']['late_email_penalty'])) {
            $explanations[] = "⏰ Late email confirmation: {$scoreData['breakdown']['late_email_penalty']} points";
        }

        if (isset($scoreData['breakdown']['priority_bonus'])) {
            $explanations[] = "⭐ Priority bonus: +{$scoreData['breakdown']['priority_bonus']} points";
        }

        if (isset($scoreData['breakdown']['quality_bonus'])) {
            $explanations[] = "🎯 Quality bonus: +{$scoreData['breakdown']['quality_bonus']} points";
        }

        if (isset($scoreData['breakdown']['overdue_penalty'])) {
            $explanations[] = "⚠️ Overdue penalty: {$scoreData['breakdown']['overdue_penalty']} points";
        }

        if (isset($scoreData['breakdown']['rejection_penalty'])) {
            $explanations[] = "❌ Rejection penalty: {$scoreData['breakdown']['rejection_penalty']} points";
        }

        if (isset($scoreData['breakdown']['experience_bonus'])) {
            $explanations[] = "🚀 Experience bonus: +{$scoreData['breakdown']['experience_bonus']} points";
        }

        return $explanations;
    }

    /**
     * Calculate aggregated task scores for a user in a specific period
     * This is for monthly/quarterly evaluations
     *
     * @param User $user
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculatePeriodScore($user, $startDate, $endDate)
    {
        // Get all tasks for this user in the period (assigned OR due in period)
        $tasks = $user->assignedTasks()
            ->forPeriod($startDate, $endDate)
            ->get();

        if ($tasks->isEmpty()) {
            return [
                'total_score' => 0,
                'average_score' => 0,
                'task_count' => 0,
                'completed_count' => 0,
                'task_scores' => [],
                'score_breakdown' => [
                    'total_base_score' => 0,
                    'total_priority_bonus' => 0,
                    'total_quality_bonus' => 0,
                    'total_penalties' => 0,
                ],
            ];
        }

        $taskScores = [];
        $totalScore = 0;
        $completedCount = 0;

        // Track breakdown totals
        $totalBaseScore = 0;
        $totalPriorityBonus = 0;
        $totalQualityBonus = 0;
        $totalPenalties = 0;

        foreach ($tasks as $task) {
            // Use final_score if task was closed by admin, otherwise calculate
            if ($task->final_score !== null) {
                $score = $task->final_score;
                $scoreData = [
                    'score' => $score,
                    'breakdown' => ['admin_closed' => $score],
                    'status' => $task->status,
                ];
            } else {
                $scoreData = $this->calculateTaskScore($task, $user);
                $score = $scoreData['score'];
            }

            $totalScore += $score;

            if ($task->status === 'completed') {
                $completedCount++;
            }

            // Aggregate breakdown components
            if (isset($scoreData['breakdown'])) {
                $breakdown = $scoreData['breakdown'];

                if (isset($breakdown['completed'])) $totalBaseScore += $breakdown['completed'];
                if (isset($breakdown['in_progress'])) $totalBaseScore += $breakdown['in_progress'];
                if (isset($breakdown['priority_bonus'])) $totalPriorityBonus += $breakdown['priority_bonus'];
                if (isset($breakdown['quality_bonus'])) $totalQualityBonus += $breakdown['quality_bonus'];

                // Penalties (negative values)
                if (isset($breakdown['overdue_penalty'])) $totalPenalties += $breakdown['overdue_penalty'];
                if (isset($breakdown['rejection_penalty'])) $totalPenalties += $breakdown['rejection_penalty'];
                if (isset($breakdown['late_email_penalty'])) $totalPenalties += $breakdown['late_email_penalty'];
            }

            $taskScores[] = [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'score' => $score,
                'status' => $task->status,
                'priority' => $task->priority,
                'is_overdue' => $task->is_overdue,
                'was_admin_closed' => $task->final_score !== null,
            ];
        }

        $averageScore = $totalScore / $tasks->count();

        return [
            'total_score' => round($totalScore, 2),
            'average_score' => round($averageScore, 2),
            'task_count' => $tasks->count(),
            'completed_count' => $completedCount,
            'completion_rate' => round(($completedCount / $tasks->count()) * 100, 2),
            'task_scores' => $taskScores,
            'score_breakdown' => [
                'total_base_score' => round($totalBaseScore, 2),
                'total_priority_bonus' => round($totalPriorityBonus, 2),
                'total_quality_bonus' => round($totalQualityBonus, 2),
                'total_penalties' => round($totalPenalties, 2),
            ],
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Calculate monthly task scores for a user
     *
     * @param User $user
     * @param int $year
     * @param int $month
     * @return array
     */
    public function calculateMonthlyScore($user, $year, $month)
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return $this->calculatePeriodScore($user, $startDate, $endDate);
    }

    /**
     * Calculate quarterly task scores for a user
     *
     * @param User $user
     * @param int $year
     * @param int $quarter (1-4)
     * @return array
     */
    public function calculateQuarterlyScore($user, $year, $quarter)
    {
        $startDate = \Carbon\Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
        $endDate = $startDate->copy()->addMonths(2)->endOfMonth();

        return $this->calculatePeriodScore($user, $startDate, $endDate);
    }
}
