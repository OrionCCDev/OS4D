<?php

// Test script to check Top 3 Competition ranking calculation
// Run this from cPanel terminal: php test_top_3_ranking.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use Carbon\Carbon;

echo "=== TOP 3 COMPETITION RANKING TEST ===\n\n";

// Get all users with assigned tasks
$users = User::whereHas('assignedTasks')->get();

if ($users->isEmpty()) {
    echo "No users with assigned tasks found.\n";
    exit;
}

echo "Found {$users->count()} users with assigned tasks\n\n";

// Calculate monthly performance for each user
$userScores = [];
$now = now();

foreach ($users as $user) {
    // Get counts for current month
    $completedCount = $user->assignedTasks()
        ->where('status', 'completed')
        ->where(function($q) use ($now) {
            $q->where(function($subQ) use ($now) {
                $subQ->whereMonth('completed_at', $now->month)
                     ->whereYear('completed_at', $now->year);
            })
            ->orWhere(function($subQ) use ($now) {
                $subQ->whereNull('completed_at')
                     ->whereMonth('updated_at', $now->month)
                     ->whereYear('updated_at', $now->year);
            });
        })
        ->count();

    $totalCount = $user->assignedTasks()
        ->where(function($q) use ($now) {
            $q->whereMonth('created_at', $now->month)
              ->whereYear('created_at', $now->year);
        })
        ->count();

    $inProgressCount = $user->assignedTasks()
        ->whereIn('status', ['in_progress', 'workingon', 'assigned'])
        ->count();

    $rejectedCount = $user->assignedTasks()
        ->where('status', 'rejected')
        ->count();

    $overdueCount = $user->assignedTasks()
        ->where('due_date', '<', now())
        ->whereNotIn('status', ['completed', 'cancelled'])
        ->count();

    $onTimeCompletedCount = $user->assignedTasks()
        ->where('status', 'completed')
        ->whereRaw('completed_at <= due_date')
        ->count();

    $lateCompletedCount = $user->assignedTasks()
        ->where('status', 'completed')
        ->whereRaw('completed_at > due_date')
        ->count();

    // Calculate completion rate
    $completionRate = $totalCount > 0
        ? round(($completedCount / $totalCount) * 100, 1)
        : 0;

    // Calculate Performance Score (same formula as DashboardController)
    $completedScore = $completedCount * 10;
    $inProgressScore = $inProgressCount * 5;
    $onTimeBonus = $onTimeCompletedCount * 3;
    $completionRateBonus = $completionRate * 0.5;
    $rejectionPenalty = $rejectedCount * 8;
    $overduePenalty = $overdueCount * 5;
    $lateCompletionPenalty = $lateCompletedCount * 2;

    // Experience multiplier
    $totalTasksAllTime = $user->assignedTasks()->count();
    $experienceMultiplier = 1.0;
    if ($totalTasksAllTime > 0) {
        if ($totalTasksAllTime <= 5) $experienceMultiplier = 1.0;
        elseif ($totalTasksAllTime <= 15) $experienceMultiplier = 1.1;
        elseif ($totalTasksAllTime <= 30) $experienceMultiplier = 1.2;
        elseif ($totalTasksAllTime <= 50) $experienceMultiplier = 1.3;
        else $experienceMultiplier = 1.4;
    }

    $baseScore = $completedScore + $inProgressScore + $onTimeBonus + $completionRateBonus;
    $penalties = $rejectionPenalty + $overduePenalty + $lateCompletionPenalty;
    $finalScore = ($baseScore * $experienceMultiplier) - $penalties;
    $finalScore = max(0, round($finalScore, 2));

    // Additional metrics
    $rejectionRate = $totalCount > 0
        ? round(($rejectedCount / $totalCount) * 100, 1)
        : 0;

    $overdueRate = $totalCount > 0
        ? round(($overdueCount / $totalCount) * 100, 1)
        : 0;

    $userScores[] = [
        'user_id' => $user->id,
        'name' => $user->name,
        'completed_count' => $completedCount,
        'total_count' => $totalCount,
        'in_progress_count' => $inProgressCount,
        'rejected_count' => $rejectedCount,
        'overdue_count' => $overdueCount,
        'on_time_count' => $onTimeCompletedCount,
        'late_count' => $lateCompletedCount,
        'completion_rate' => $completionRate,
        'rejection_rate' => $rejectionRate,
        'overdue_rate' => $overdueRate,
        'experience_multiplier' => $experienceMultiplier,
        'performance_score' => $finalScore,
        'details' => [
            'completed_score' => $completedScore,
            'in_progress_score' => $inProgressScore,
            'on_time_bonus' => $onTimeBonus,
            'completion_rate_bonus' => $completionRateBonus,
            'rejection_penalty' => $rejectionPenalty,
            'overdue_penalty' => $overduePenalty,
            'late_penalty' => $lateCompletionPenalty,
            'base_score' => $baseScore,
            'penalties' => $penalties,
            'total_tasks_all_time' => $totalTasksAllTime,
        ]
    ];
}

// Sort by performance score DESC
usort($userScores, function($a, $b) {
    return $b['performance_score'] <=> $a['performance_score'];
});

// Display results
echo "RANKING RESULTS:\n";
echo str_repeat("=", 120) . "\n";
printf("%-3s | %-20s | %-8s | %-5s | %-5s | %-6s | %-6s | %-8s | %-12s\n",
    "Rnk", "Name", "Score", "Comp", "Total", "Reject", "Overdue", "Exp Mult", "Completion%");
echo str_repeat("-", 120) . "\n";

foreach ($userScores as $index => $user) {
    $rank = $index + 1;
    printf("%-3d | %-20s | %-8.2f | %-5d | %-5d | %-6d | %-6d | %-8.2f | %-12s\n",
        $rank,
        substr($user['name'], 0, 20),
        $user['performance_score'],
        $user['completed_count'],
        $user['total_count'],
        $user['rejected_count'],
        $user['overdue_count'],
        $user['experience_multiplier'],
        $user['completion_rate'] . "%"
    );

    // Show top 3 in detail
    if ($rank <= 3) {
        echo "\nTop " . $rank . " Details:\n";
        echo "  Performance Score: " . $user['performance_score'] . "\n";
        echo "  Completed: {$user['completed_count']} (Score: " . $user['details']['completed_score'] . ")\n";
        echo "  In Progress: {$user['in_progress_count']} (Score: " . $user['details']['in_progress_score'] . ")\n";
        echo "  On Time: {$user['on_time_count']} (Bonus: " . $user['details']['on_time_bonus'] . ")\n";
        echo "  Completion Rate: " . $user['completion_rate'] . "% (Bonus: " . $user['details']['completion_rate_bonus'] . ")\n";
        echo "  Rejected: {$user['rejected_count']} (Penalty: " . $user['details']['rejection_penalty'] . ")\n";
        echo "  Overdue: {$user['overdue_count']} (Penalty: " . $user['details']['overdue_penalty'] . ")\n";
        echo "  Late Completed: {$user['late_count']} (Penalty: " . $user['details']['late_penalty'] . ")\n";
        echo "  Experience Multiplier: " . $user['experience_multiplier'] . "\n";
        echo "  Base Score: " . $user['details']['base_score'] . "\n";
        echo "  Penalties: " . $user['details']['penalties'] . "\n";
        echo "  Final: ({$user['details']['base_score']} × {$user['experience_multiplier']}) - {$user['details']['penalties']} = {$user['performance_score']}\n";
        echo "\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";

