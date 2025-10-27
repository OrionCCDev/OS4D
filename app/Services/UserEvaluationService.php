<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserEvaluationService
{
    /**
     * Calculate comprehensive and fair evaluation for a user
     * Ensures equality by normalizing scores against team averages
     */
    public function calculateUserEvaluation(User $user, $period = 'month')
    {
        $periodDates = $this->getPeriodDates($period);
        $now = now();
        
        // Get user's tasks for the period
        $userTasks = $user->assignedTasks()
            ->whereBetween('created_at', $periodDates)
            ->get();

        if ($userTasks->isEmpty()) {
            return $this->getEmptyEvaluation();
        }

        // Calculate basic metrics
        $metrics = [
            'total_tasks' => $userTasks->count(),
            'completed_tasks' => $userTasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $userTasks->where('status', 'in_progress')->count(),
            'rejected_tasks' => $userTasks->where('status', 'rejected')->count(),
            'overdue_tasks' => $userTasks->filter(function($task) use ($now) {
                return $task->due_date && $task->due_date < $now && !in_array($task->status, ['completed', 'approved']);
            })->count(),
            'on_time_completions' => $userTasks->filter(function($task) {
                return $task->status === 'completed' && $task->completed_at && $task->due_date && 
                       $task->completed_at <= $task->due_date;
            })->count(),
            'late_completions' => $userTasks->filter(function($task) {
                return $task->status === 'completed' && $task->completed_at && $task->due_date && 
                       $task->completed_at > $task->due_date;
            })->count(),
        ];

        // Calculate progress-weighted metrics
        $metrics['average_progress'] = $this->calculateAverageProgress($userTasks);
        $metrics['completion_rate'] = $this->calculateCompletionRate($userTasks);
        $metrics['quality_score'] = $this->calculateQualityScore($metrics);
        $metrics['timeliness_score'] = $this->calculateTimelinessScore($metrics);
        $metrics['productivity_score'] = $this->calculateProductivityScore($userTasks);
        
        // Get team averages for fair comparison
        $teamAverages = $this->getTeamAverages($periodDates, $user->id);
        
        // Calculate normalized scores (fairness factor)
        $scores = [
            'completion_score' => $this->normalizeScore($metrics['completion_rate'], $teamAverages['completion_rate']),
            'quality_score' => $this->normalizeScore($metrics['quality_score'], $teamAverages['quality_score']),
            'timeliness_score' => $this->normalizeScore($metrics['timeliness_score'], $teamAverages['timeliness_score']),
            'productivity_score' => $this->normalizeScore($metrics['productivity_score'], $teamAverages['productivity_score']),
        ];

        // Calculate overall score with weighted components
        $overallScore = ($scores['completion_score'] * 0.25) + 
                       ($scores['quality_score'] * 0.35) + 
                       ($scores['timeliness_score'] * 0.25) + 
                       ($scores['productivity_score'] * 0.15);

        // Determine grade
        $grade = $this->determineGrade($overallScore);

        // Calculate rank
        $rank = $this->calculateRank($overallScore, $periodDates);

        return [
            'metrics' => $metrics,
            'scores' => $scores,
            'overall_score' => round($overallScore, 2),
            'grade' => $grade,
            'rank' => $rank,
            'team_averages' => $teamAverages,
            'period' => $period,
            'period_dates' => $periodDates,
        ];
    }

    /**
     * Calculate average progress percentage across all tasks
     */
    private function calculateAverageProgress($tasks)
    {
        if ($tasks->isEmpty()) return 0;
        
        $totalProgress = $tasks->sum(function($task) {
            return $task->progress_percentage ?? 0;
        });
        
        return round($totalProgress / $tasks->count(), 2);
    }

    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate($tasks)
    {
        if ($tasks->isEmpty()) return 0;
        
        $completed = $tasks->where('status', 'completed')->count();
        return round(($completed / $tasks->count()) * 100, 2);
    }

    /**
     * Calculate quality score (based on rejection rate)
     * Lower rejection rate = higher quality score
     */
    private function calculateQualityScore($metrics)
    {
        $totalTasks = $metrics['total_tasks'];
        if ($totalTasks == 0) return 100;
        
        $rejectionRate = ($metrics['rejected_tasks'] / $totalTasks) * 100;
        
        // Quality score = 100 - rejection rate
        // If rejection rate is 0%, quality is 100
        // If rejection rate is 50%, quality is 50
        $qualityScore = max(0, 100 - $rejectionRate);
        
        return round($qualityScore, 2);
    }

    /**
     * Calculate timeliness score
     */
    private function calculateTimelinessScore($metrics)
    {
        $totalCompleted = $metrics['completed_tasks'];
        if ($totalCompleted == 0) return 100;
        
        $onTimeRate = ($metrics['on_time_completions'] / $totalCompleted) * 100;
        
        return round($onTimeRate, 2);
    }

    /**
     * Calculate productivity score based on task completion speed
     */
    private function calculateProductivityScore($tasks)
    {
        $completedTasks = $tasks->filter(function($task) {
            return $task->status === 'completed' && $task->assigned_at && $task->completed_at;
        });

        if ($completedTasks->isEmpty()) return 100; // Neutral score if no completed tasks

        $totalDays = 0;
        foreach ($completedTasks as $task) {
            $daysTaken = $task->assigned_at->diffInDays($task->completed_at);
            $totalDays += $daysTaken;
        }

        $avgDaysPerTask = $totalDays / $completedTasks->count();
        
        // Productivity score: Faster = Better
        // If avg is 5 days, productivity is 80
        // If avg is 10 days, productivity is 60
        $productivityScore = max(0, 100 - ($avgDaysPerTask * 10));
        
        return round($productivityScore, 2);
    }

    /**
     * Get team averages for normalization (ensures fairness)
     */
    private function getTeamAverages($periodDates, $excludeUserId = null)
    {
        $allUsers = User::whereHas('assignedTasks')->get();
        
        if ($excludeUserId) {
            $allUsers = $allUsers->reject(function($user) use ($excludeUserId) {
                return $user->id === $excludeUserId;
            });
        }

        if ($allUsers->isEmpty()) {
            return [
                'completion_rate' => 100,
                'quality_score' => 100,
                'timeliness_score' => 100,
                'productivity_score' => 100,
            ];
        }

        $teamScores = collect([]);
        
        foreach ($allUsers as $user) {
            $tasks = $user->assignedTasks()->whereBetween('created_at', $periodDates)->get();
            
            if ($tasks->isEmpty()) continue;
            
            $metrics = [
                'total' => $tasks->count(),
                'completed' => $tasks->where('status', 'completed')->count(),
                'rejected' => $tasks->where('status', 'rejected')->count(),
                'on_time' => $tasks->filter(function($task) {
                    return $task->status === 'completed' && $task->completed_at && $task->due_date && 
                           $task->completed_at <= $task->due_date;
                })->count(),
            ];
            
            $completionRate = ($metrics['completed'] / $metrics['total']) * 100;
            $qualityScore = max(0, 100 - (($metrics['rejected'] / $metrics['total']) * 100));
            $timelinessScore = $metrics['completed'] > 0 ? ($metrics['on_time'] / $metrics['completed']) * 100 : 100;
            
            $teamScores->push([
                'completion_rate' => $completionRate,
                'quality_score' => $qualityScore,
                'timeliness_score' => $timelinessScore,
            ]);
        }
        
        if ($teamScores->isEmpty()) {
            return [
                'completion_rate' => 100,
                'quality_score' => 100,
                'timeliness_score' => 100,
                'productivity_score' => 100,
            ];
        }

        return [
            'completion_rate' => round($teamScores->avg('completion_rate'), 2),
            'quality_score' => round($teamScores->avg('quality_score'), 2),
            'timeliness_score' => round($teamScores->avg('timeliness_score'), 2),
            'productivity_score' => 100, // Default
        ];
    }

    /**
     * Normalize score against team average (fairness factor)
     * Score above average = positive, below = negative
     */
    private function normalizeScore($userScore, $teamAverage)
    {
        if ($teamAverage == 0) return $userScore;
        
        // Ratio-based normalization
        // If user is at team average = 50
        // If user is above average = >50
        // If user is below average = <50
        $ratio = $userScore / $teamAverage;
        $normalizedScore = ($ratio * 50);
        
        // Cap at 100
        return min(100, max(0, round($normalizedScore, 2)));
    }

    /**
     * Calculate rank based on overall score
     */
    private function calculateRank($score, $periodDates)
    {
        $allScores = User::whereHas('assignedTasks')->get()->map(function($user) use ($periodDates) {
            $eval = $this->calculateUserEvaluation($user, 'custom');
            return $eval['overall_score'];
        })->sort()->reverse()->values();
        
        $rank = $allScores->search($score);
        
        return $rank !== false ? $rank + 1 : null;
    }

    /**
     * Determine letter grade
     */
    private function determineGrade($score)
    {
        if ($score >= 90) return 'A+';
        if ($score >= 85) return 'A';
        if ($score >= 80) return 'A-';
        if ($score >= 75) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 65) return 'B-';
        if ($score >= 60) return 'C+';
        if ($score >= 55) return 'C';
        if ($score >= 50) return 'C-';
        return 'D';
    }

    /**
     * Get period dates
     */
    private function getPeriodDates($period)
    {
        $now = now();
        
        switch ($period) {
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'quarter':
                return [
                    'start' => $now->copy()->startOfQuarter(),
                    'end' => $now->copy()->endOfQuarter(),
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                ];
            case 'custom':
                return [
                    'start' => $now->copy()->subMonths(3),
                    'end' => $now,
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
        }
    }

    /**
     * Get empty evaluation for users with no tasks
     */
    private function getEmptyEvaluation()
    {
        return [
            'metrics' => [
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'in_progress_tasks' => 0,
                'rejected_tasks' => 0,
                'overdue_tasks' => 0,
                'on_time_completions' => 0,
                'late_completions' => 0,
                'average_progress' => 0,
                'completion_rate' => 0,
                'quality_score' => 0,
                'timeliness_score' => 0,
                'productivity_score' => 0,
            ],
            'scores' => [
                'completion_score' => 0,
                'quality_score' => 0,
                'timeliness_score' => 0,
                'productivity_score' => 0,
            ],
            'overall_score' => 0,
            'grade' => 'N/A',
            'rank' => null,
            'team_averages' => [],
        ];
    }
}

