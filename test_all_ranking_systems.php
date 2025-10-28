<?php

// Comprehensive test script to verify all ranking systems work correctly
// Run this from cPanel terminal: php test_all_ranking_systems.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Services\ReportService;
use Carbon\Carbon;

echo "=== COMPREHENSIVE RANKING SYSTEMS TEST ===\n\n";

// Initialize ReportService
$reportService = new ReportService();

// Get all users with assigned tasks
$users = User::whereHas('assignedTasks')->get();

if ($users->isEmpty()) {
    echo "No users with assigned tasks found.\n";
    exit;
}

echo "Found {$users->count()} users with assigned tasks\n\n";

// Test 1: Dashboard Top 3 Competition Logic
echo "=== TEST 1: DASHBOARD TOP 3 COMPETITION ===\n";
echo "Testing DashboardController logic...\n\n";

$now = now();
$dashboardRankings = [];

foreach ($users as $user) {
    // Simulate DashboardController logic
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

    // Dashboard performance score calculation (raw score)
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
    $dashboardScore = ($baseScore * $experienceMultiplier) - $penalties;
    $dashboardScore = max(0, round($dashboardScore, 2));

    $dashboardRankings[] = [
        'user_id' => $user->id,
        'name' => $user->name,
        'dashboard_score' => $dashboardScore,
        'completed' => $completedCount,
        'total' => $totalCount,
        'experience_multiplier' => $experienceMultiplier,
        'penalties' => $penalties,
    ];
}

// Sort by dashboard score
usort($dashboardRankings, function($a, $b) {
    return $b['dashboard_score'] <=> $a['dashboard_score'];
});

echo "DASHBOARD TOP 3 COMPETITION RANKINGS:\n";
echo "=====================================\n";
echo "Rank | Name                 | Score    | Comp/Total | Exp Mult | Penalties\n";
echo "--------------------------------------------------------------------\n";
foreach (array_slice($dashboardRankings, 0, 3) as $index => $ranking) {
    $rank = $index + 1;
    printf("%-4s | %-20s | %-8.2f | %-10s | %-8.1f | %-8.2f\n",
        $rank,
        substr($ranking['name'], 0, 20),
        $ranking['dashboard_score'],
        $ranking['completed'] . '/' . $ranking['total'],
        $ranking['experience_multiplier'],
        $ranking['penalties']
    );
}

echo "\n";

// Test 2: Reports Top Performers Logic
echo "=== TEST 2: REPORTS TOP PERFORMERS ===\n";
echo "Testing ReportService logic...\n\n";

$reportsRankings = $reportService->getEmployeeRankings([
    'date_from' => Carbon::now()->startOfMonth(),
    'date_to' => Carbon::now()->endOfMonth(),
])->take(5);

echo "REPORTS TOP PERFORMERS RANKINGS:\n";
echo "===============================\n";
echo "Rank | Name                 | Score    | Comp/Total | Completion%\n";
echo "----------------------------------------------------------------\n";
foreach ($reportsRankings as $ranking) {
    printf("%-4s | %-20s | %-8.2f | %-10s | %-12.1f%%\n",
        $ranking['rank'],
        substr($ranking['user']['name'], 0, 20),
        $ranking['performance_score'],
        $ranking['completed_tasks'] . '/' . $ranking['total_tasks'],
        $ranking['completion_rate']
    );
}

echo "\n";

// Test 3: Compare Rankings
echo "=== TEST 3: RANKING COMPARISON ===\n";
echo "Comparing Dashboard vs Reports rankings...\n\n";

echo "DASHBOARD TOP 3:\n";
foreach (array_slice($dashboardRankings, 0, 3) as $index => $ranking) {
    echo ($index + 1) . ". " . $ranking['name'] . " (Score: " . $ranking['dashboard_score'] . ")\n";
}

echo "\nREPORTS TOP 3:\n";
foreach ($reportsRankings->take(3) as $ranking) {
    echo $ranking['rank'] . ". " . $ranking['user']['name'] . " (Score: " . $ranking['performance_score'] . "%)\n";
}

echo "\n";

// Check for inconsistencies
echo "=== CONSISTENCY CHECK ===\n";
$dashboardTop3 = array_slice($dashboardRankings, 0, 3);
$reportsTop3 = $reportsRankings->take(3)->toArray();

$inconsistent = false;
foreach ($dashboardTop3 as $index => $dashboardUser) {
    $reportsUser = $reportsTop3[$index] ?? null;
    if (!$reportsUser || $dashboardUser['user_id'] != $reportsUser['user']['id']) {
        $inconsistent = true;
        echo "❌ INCONSISTENCY FOUND!\n";
        echo "Dashboard rank " . ($index + 1) . ": " . $dashboardUser['name'] . "\n";
        echo "Reports rank " . ($index + 1) . ": " . ($reportsUser['user']['name'] ?? 'N/A') . "\n\n";
    }
}

if (!$inconsistent) {
    echo "✅ Rankings are consistent between Dashboard and Reports!\n";
} else {
    echo "❌ Rankings are INCONSISTENT! This needs to be fixed.\n";
}

echo "\n=== TEST COMPLETE ===\n";
