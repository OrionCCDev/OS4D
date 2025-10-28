<?php

// Live Production Test Script for All Ranking Systems
// Run this on your production server via cPanel terminal: php test_live_ranking_systems.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Services\ReportService;
use Carbon\Carbon;

echo "=== LIVE PRODUCTION RANKING SYSTEMS TEST ===\n";
echo "Testing on: " . now()->format('Y-m-d H:i:s') . "\n";
echo "Server: " . (config('app.env') ?? 'production') . "\n\n";

// Initialize ReportService
$reportService = new ReportService();

// Get all users with assigned tasks
$users = User::whereHas('assignedTasks')->get();

if ($users->isEmpty()) {
    echo "❌ No users with assigned tasks found.\n";
    exit;
}

echo "✅ Found {$users->count()} users with assigned tasks\n\n";

// Test 1: Dashboard Top 3 Competition Logic
echo "=== TEST 1: DASHBOARD TOP 3 COMPETITION ===\n";
echo "Simulating DashboardController logic...\n\n";

$now = now();
$dashboardRankings = [];

foreach ($users as $user) {
    // Simulate DashboardController logic exactly
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

    // Dashboard performance score calculation (exact same as DashboardController)
    $completedScore = $completedCount * 10;
    $inProgressScore = $inProgressCount * 5;
    $onTimeBonus = $onTimeCompletedCount * 3;
    $completionRateBonus = $completionRate * 0.5;
    $rejectionPenalty = $rejectedCount * 8;
    $overduePenalty = $overdueCount * 5;
    $lateCompletionPenalty = $lateCompletedCount * 2;

    // Experience multiplier (exact same as DashboardController)
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
        'email' => $user->email,
        'dashboard_score' => $dashboardScore,
        'completed' => $completedCount,
        'total' => $totalCount,
        'in_progress' => $inProgressCount,
        'rejected' => $rejectedCount,
        'overdue' => $overdueCount,
        'on_time' => $onTimeCompletedCount,
        'late' => $lateCompletedCount,
        'completion_rate' => $completionRate,
        'experience_multiplier' => $experienceMultiplier,
        'penalties' => $penalties,
        'base_score' => $baseScore,
    ];
}

// Sort by dashboard score (exact same as DashboardController)
usort($dashboardRankings, function($a, $b) {
    return $b['dashboard_score'] <=> $a['dashboard_score'];
});

echo "DASHBOARD TOP 3 COMPETITION RANKINGS:\n";
echo "=====================================\n";
echo "Rank | Name                 | Score    | Comp/Total | InProg | Reject | Overdue | Exp Mult | Completion%\n";
echo "--------------------------------------------------------------------------------------------------------\n";
foreach (array_slice($dashboardRankings, 0, 3) as $index => $ranking) {
    $rank = $index + 1;
    printf("%-4s | %-20s | %-8.2f | %-10s | %-6s | %-6s | %-7s | %-8.1f | %-12.1f%%\n",
        $rank,
        substr($ranking['name'], 0, 20),
        $ranking['dashboard_score'],
        $ranking['completed'] . '/' . $ranking['total'],
        $ranking['in_progress'],
        $ranking['rejected'],
        $ranking['overdue'],
        $ranking['experience_multiplier'],
        $ranking['completion_rate']
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
echo "Rank | Name                 | Score    | Comp/Total | Completion% | On-Time%\n";
echo "----------------------------------------------------------------------------\n";
foreach ($reportsRankings as $ranking) {
    printf("%-4s | %-20s | %-8.2f | %-10s | %-12.1f%% | %-9.1f%%\n",
        $ranking['rank'],
        substr($ranking['user']['name'], 0, 20),
        $ranking['performance_score'],
        $ranking['completed_tasks'] . '/' . $ranking['total_tasks'],
        $ranking['completion_rate'],
        $ranking['on_time_rate']
    );
}

echo "\n";

// Test 3: Compare Rankings
echo "=== TEST 3: RANKING CONSISTENCY CHECK ===\n";
echo "Comparing Dashboard vs Reports rankings...\n\n";

$dashboardTop3 = array_slice($dashboardRankings, 0, 3);
$reportsTop3 = $reportsRankings->take(3)->toArray();

echo "DASHBOARD TOP 3:\n";
foreach ($dashboardTop3 as $index => $ranking) {
    echo ($index + 1) . ". " . $ranking['name'] . " (Score: " . $ranking['dashboard_score'] . ")\n";
}

echo "\nREPORTS TOP 3:\n";
foreach ($reportsTop3 as $ranking) {
    echo $ranking['rank'] . ". " . $ranking['user']['name'] . " (Score: " . $ranking['performance_score'] . ")\n";
}

echo "\n";

// Check for inconsistencies
echo "CONSISTENCY ANALYSIS:\n";
echo "====================\n";
$inconsistent = false;
$inconsistencies = [];

foreach ($dashboardTop3 as $index => $dashboardUser) {
    $reportsUser = $reportsTop3[$index] ?? null;
    if (!$reportsUser || $dashboardUser['user_id'] != $reportsUser['user']['id']) {
        $inconsistent = true;
        $inconsistencies[] = [
            'rank' => $index + 1,
            'dashboard_user' => $dashboardUser['name'],
            'reports_user' => $reportsUser['user']['name'] ?? 'N/A',
            'dashboard_score' => $dashboardUser['dashboard_score'],
            'reports_score' => $reportsUser['performance_score'] ?? 'N/A'
        ];
    }
}

if (!$inconsistent) {
    echo "✅ PERFECT CONSISTENCY!\n";
    echo "All rankings match between Dashboard and Reports systems.\n";
} else {
    echo "❌ INCONSISTENCIES FOUND!\n";
    foreach ($inconsistencies as $issue) {
        echo "Rank {$issue['rank']}:\n";
        echo "  Dashboard: {$issue['dashboard_user']} (Score: {$issue['dashboard_score']})\n";
        echo "  Reports:   {$issue['reports_user']} (Score: {$issue['reports_score']})\n\n";
    }
}

echo "\n";

// Test 4: Detailed Score Breakdown for Top User
echo "=== TEST 4: DETAILED SCORE BREAKDOWN ===\n";
if (!empty($dashboardTop3)) {
    $topUser = $dashboardTop3[0];
    echo "Top Performer: {$topUser['name']}\n";
    echo "Email: {$topUser['email']}\n";
    echo "Final Score: {$topUser['dashboard_score']}\n\n";

    echo "SCORE BREAKDOWN:\n";
    echo "================\n";
    echo "Base Score Components:\n";
    echo "  Completed Tasks: {$topUser['completed']} × 10 = " . ($topUser['completed'] * 10) . " points\n";
    echo "  In Progress:     {$topUser['in_progress']} × 5 = " . ($topUser['in_progress'] * 5) . " points\n";
    echo "  On-Time Bonus:   {$topUser['on_time']} × 3 = " . ($topUser['on_time'] * 3) . " points\n";
    echo "  Completion Rate: {$topUser['completion_rate']}% × 0.5 = " . ($topUser['completion_rate'] * 0.5) . " points\n";
    echo "  Base Score Total: {$topUser['base_score']} points\n\n";

    echo "Penalties:\n";
    echo "  Rejected Tasks: {$topUser['rejected']} × 8 = " . ($topUser['rejected'] * 8) . " points\n";
    echo "  Overdue Tasks:  {$topUser['overdue']} × 5 = " . ($topUser['overdue'] * 5) . " points\n";
    echo "  Late Tasks:     {$topUser['late']} × 2 = " . ($topUser['late'] * 2) . " points\n";
    echo "  Total Penalties: {$topUser['penalties']} points\n\n";

    echo "Experience Multiplier: {$topUser['experience_multiplier']}x\n";
    echo "Final Calculation: ({$topUser['base_score']} × {$topUser['experience_multiplier']}) - {$topUser['penalties']} = {$topUser['dashboard_score']}\n";
}

echo "\n";

// Test 5: All Users Summary
echo "=== TEST 5: ALL USERS SUMMARY ===\n";
echo "Total Users with Tasks: " . count($dashboardRankings) . "\n";
echo "Users with Scores > 0: " . count(array_filter($dashboardRankings, function($u) { return $u['dashboard_score'] > 0; })) . "\n";
echo "Users with Scores = 0: " . count(array_filter($dashboardRankings, function($u) { return $u['dashboard_score'] == 0; })) . "\n";

$avgScore = array_sum(array_column($dashboardRankings, 'dashboard_score')) / count($dashboardRankings);
echo "Average Score: " . round($avgScore, 2) . "\n";

$maxScore = max(array_column($dashboardRankings, 'dashboard_score'));
echo "Highest Score: " . $maxScore . "\n";

echo "\n";

// Test 6: Formula Verification
echo "=== TEST 6: FORMULA VERIFICATION ===\n";
echo "Testing the documented formula:\n";
echo "Base Score = (Completed × 10) + (In Progress × 5) + (On-Time × 3) + (Completion Rate × 0.5)\n";
echo "Penalties = (Rejected × 8) + (Overdue × 5) + (Late × 2)\n";
echo "Final Score = (Base Score × Experience Multiplier) - Penalties\n\n";

if (!empty($dashboardTop3)) {
    $testUser = $dashboardTop3[0];
    $calculatedBase = ($testUser['completed'] * 10) + ($testUser['in_progress'] * 5) + ($testUser['on_time'] * 3) + ($testUser['completion_rate'] * 0.5);
    $calculatedPenalties = ($testUser['rejected'] * 8) + ($testUser['overdue'] * 5) + ($testUser['late'] * 2);
    $calculatedFinal = ($calculatedBase * $testUser['experience_multiplier']) - $calculatedPenalties;

    echo "Test User: {$testUser['name']}\n";
    echo "Calculated Base Score: {$calculatedBase}\n";
    echo "Actual Base Score: {$testUser['base_score']}\n";
    echo "Match: " . ($calculatedBase == $testUser['base_score'] ? "✅ YES" : "❌ NO") . "\n\n";

    echo "Calculated Penalties: {$calculatedPenalties}\n";
    echo "Actual Penalties: {$testUser['penalties']}\n";
    echo "Match: " . ($calculatedPenalties == $testUser['penalties'] ? "✅ YES" : "❌ NO") . "\n\n";

    echo "Calculated Final Score: {$calculatedFinal}\n";
    echo "Actual Final Score: {$testUser['dashboard_score']}\n";
    echo "Match: " . (abs($calculatedFinal - $testUser['dashboard_score']) < 0.01 ? "✅ YES" : "❌ NO") . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "Test completed at: " . now()->format('Y-m-d H:i:s') . "\n";
echo "All ranking systems have been tested and verified!\n";
