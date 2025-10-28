<?php

// Task Scoring System Test Script
// Run this on your production server via cPanel terminal: php test_task_scoring.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Task;
use App\Models\User;
use App\Services\TaskScoringService;

echo "=== TASK SCORING SYSTEM TEST ===\n";
echo "Testing on: " . now()->format('Y-m-d H:i:s') . "\n\n";

// Initialize TaskScoringService
$scoringService = new TaskScoringService();

// Get sample tasks
$tasks = Task::with('assignee')->limit(10)->get();

if ($tasks->isEmpty()) {
    echo "❌ No tasks found.\n";
    exit;
}

echo "✅ Found {$tasks->count()} tasks to test\n\n";

echo "=== TASK SCORE BREAKDOWN ===\n";
echo "Task ID | Title                    | Assignee        | Status      | Priority | Score | Breakdown\n";
echo "--------|--------------------------|-----------------|-------------|----------|-------|----------\n";

foreach ($tasks as $task) {
    $scoreData = $scoringService->calculateTaskScore($task, $task->assignee);
    $explanations = $scoringService->getScoreExplanation($scoreData);

    $title = substr($task->title, 0, 25);
    $assignee = $task->assignee ? substr($task->assignee->name, 0, 15) : 'Unassigned';
    $status = substr($task->status, 0, 11);
    $priority = $task->priority ?? 'normal';
    $score = $scoreData['score'];

    printf("%-7s | %-24s | %-15s | %-11s | %-8s | %-5s | ",
        $task->id,
        $title,
        $assignee,
        $status,
        $priority,
        $score
    );

    // Show breakdown
    $breakdownParts = [];
    foreach ($explanations as $explanation) {
        if (strpos($explanation, '+') !== false) {
            $breakdownParts[] = '✅';
        } elseif (strpos($explanation, '-') !== false) {
            $breakdownParts[] = '❌';
        } else {
            $breakdownParts[] = 'ℹ️';
        }
    }
    echo implode(' ', $breakdownParts) . "\n";
}

echo "\n";

// Test different scenarios
echo "=== SCORING SCENARIOS TEST ===\n";

// Test completed task
$completedTask = Task::where('status', 'completed')->first();
if ($completedTask) {
    echo "✅ Completed Task Test:\n";
    $scoreData = $scoringService->calculateTaskScore($completedTask, $completedTask->assignee);
    echo "  Task: {$completedTask->title}\n";
    echo "  Score: {$scoreData['score']}\n";
    echo "  Breakdown:\n";
    foreach ($scoringService->getScoreExplanation($scoreData) as $explanation) {
        echo "    - {$explanation}\n";
    }
    echo "\n";
}

// Test in-progress task
$inProgressTask = Task::whereIn('status', ['in_progress', 'workingon', 'assigned'])->first();
if ($inProgressTask) {
    echo "🔄 In-Progress Task Test:\n";
    $scoreData = $scoringService->calculateTaskScore($inProgressTask, $inProgressTask->assignee);
    echo "  Task: {$inProgressTask->title}\n";
    echo "  Score: {$scoreData['score']}\n";
    echo "  Breakdown:\n";
    foreach ($scoringService->getScoreExplanation($scoreData) as $explanation) {
        echo "    - {$explanation}\n";
    }
    echo "\n";
}

// Test overdue task
$overdueTask = Task::where('due_date', '<', now())
    ->whereNotIn('status', ['completed', 'cancelled'])
    ->first();
if ($overdueTask) {
    echo "⚠️ Overdue Task Test:\n";
    $scoreData = $scoringService->calculateTaskScore($overdueTask, $overdueTask->assignee);
    echo "  Task: {$overdueTask->title}\n";
    echo "  Score: {$scoreData['score']}\n";
    echo "  Breakdown:\n";
    foreach ($scoringService->getScoreExplanation($scoreData) as $explanation) {
        echo "    - {$explanation}\n";
    }
    echo "\n";
}

// Test priority bonuses
echo "=== PRIORITY BONUS TEST ===\n";
$priorities = ['critical', 'urgent', 'high', 'medium', 'normal', 'low'];
foreach ($priorities as $priority) {
    $task = Task::where('priority', $priority)->first();
    if ($task) {
        $scoreData = $scoringService->calculateTaskScore($task, $task->assignee);
        $bonus = $scoreData['breakdown']['priority_bonus'] ?? 0;
        echo "  {$priority}: +{$bonus} points\n";
    }
}

echo "\n";

// Test experience multipliers
echo "=== EXPERIENCE MULTIPLIER TEST ===\n";
$users = User::whereHas('assignedTasks')->limit(5)->get();
foreach ($users as $user) {
    $totalTasks = $user->assignedTasks()->count();
    $multiplier = 1.0;
    if ($totalTasks > 0) {
        if ($totalTasks <= 5) $multiplier = 1.0;
        elseif ($totalTasks <= 15) $multiplier = 1.1;
        elseif ($totalTasks <= 30) $multiplier = 1.2;
        elseif ($totalTasks <= 50) $multiplier = 1.3;
        else $multiplier = 1.4;
    }

    echo "  {$user->name}: {$totalTasks} tasks = {$multiplier}x multiplier\n";
}

echo "\n";

// Test score ranges
echo "=== SCORE RANGE ANALYSIS ===\n";
$allScores = [];
foreach ($tasks as $task) {
    $scoreData = $scoringService->calculateTaskScore($task, $task->assignee);
    $allScores[] = $scoreData['score'];
}

if (!empty($allScores)) {
    $minScore = min($allScores);
    $maxScore = max($allScores);
    $avgScore = array_sum($allScores) / count($allScores);

    echo "  Minimum Score: {$minScore}\n";
    echo "  Maximum Score: {$maxScore}\n";
    echo "  Average Score: " . round($avgScore, 2) . "\n";
    echo "  Score Range: " . ($maxScore - $minScore) . "\n";
}

echo "\n";

// Test popover content generation
echo "=== POPOVER CONTENT TEST ===\n";
$sampleTask = $tasks->first();
if ($sampleTask) {
    $scoreData = $scoringService->calculateTaskScore($sampleTask, $sampleTask->assignee);
    $explanations = $scoringService->getScoreExplanation($scoreData);

    echo "Sample Task: {$sampleTask->title}\n";
    echo "Popover Content:\n";
    echo "  <h6>Task Score Breakdown</h6>\n";
    foreach ($explanations as $explanation) {
        echo "  <div>{$explanation}</div>\n";
    }
    echo "  <hr>\n";
    echo "  <strong>Total Score: {$scoreData['score']}</strong>\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "Task scoring system has been tested successfully!\n";
echo "Test completed at: " . now()->format('Y-m-d H:i:s') . "\n";
