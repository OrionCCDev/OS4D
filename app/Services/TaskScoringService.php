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

        // On-time completion bonus
        if ($task->status === 'completed' && $task->completed_at && $task->due_date) {
            if ($task->completed_at <= $task->due_date) {
                $score += 3;
                $breakdown['on_time_bonus'] = 3;
            } else {
                $score -= 2;
                $breakdown['late_penalty'] = -2;
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

        // Overdue penalty
        if ($task->due_date && $task->due_date < now() && !in_array($task->status, ['completed', 'cancelled'])) {
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
            'is_overdue' => $task->due_date && $task->due_date < now() && !in_array($task->status, ['completed', 'cancelled']),
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
        // Check if task status is rejected or if there's a rejection history
        return $task->status === 'rejected' ||
               (method_exists($task, 'histories') &&
                $task->histories()->where('status', 'rejected')->exists());
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

        if (isset($scoreData['breakdown']['on_time_bonus'])) {
            $explanations[] = "⏰ On-time completion: +{$scoreData['breakdown']['on_time_bonus']} points";
        }

        if (isset($scoreData['breakdown']['late_penalty'])) {
            $explanations[] = "⏰ Late completion: {$scoreData['breakdown']['late_penalty']} points";
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
}
