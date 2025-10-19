<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Http\Controllers\DashboardController;
use Carbon\Carbon;

echo "🏆 Debugging Competition Board Data\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // Test 1: Check basic data
    echo "1. Checking basic data...\n";
    $totalUsers = User::count();
    $totalTasks = Task::count();
    $completedTasks = Task::where('status', 'completed')->count();
    $assignedTasks = Task::whereNotNull('assigned_to')->count();
    
    echo "   Total users: {$totalUsers}\n";
    echo "   Total tasks: {$totalTasks}\n";
    echo "   Completed tasks: {$completedTasks}\n";
    echo "   Assigned tasks: {$assignedTasks}\n";
    
    // Test 2: Check users with assigned tasks
    echo "\n2. Checking users with assigned tasks...\n";
    $usersWithTasks = User::whereHas('assignedTasks')->get();
    echo "   Users with assigned tasks: " . $usersWithTasks->count() . "\n";
    
    foreach ($usersWithTasks as $user) {
        $userTaskCount = $user->assignedTasks()->count();
        $userCompletedCount = $user->assignedTasks()->where('status', 'completed')->count();
        echo "   - {$user->name} (ID: {$user->id}): {$userTaskCount} total, {$userCompletedCount} completed\n";
    }
    
    // Test 3: Check the exact query from DashboardController
    echo "\n3. Testing DashboardController query...\n";
    
    $controller = new DashboardController();
    $dashboardData = $controller->getDashboardData();
    
    echo "   Monthly top performers count: " . $dashboardData['monthly_top_performers']->count() . "\n";
    echo "   Quarterly top performers count: " . $dashboardData['quarterly_top_performers']->count() . "\n";
    echo "   Yearly top performers count: " . $dashboardData['yearly_top_performers']->count() . "\n";
    
    if ($dashboardData['monthly_top_performers']->count() > 0) {
        echo "   Monthly top performers:\n";
        foreach ($dashboardData['monthly_top_performers'] as $index => $user) {
            echo "     " . ($index + 1) . ". {$user->name} - Completed: {$user->completed_tasks_count}, Total: {$user->total_tasks_count}, Rate: {$user->completion_rate}%\n";
        }
    }
    
    // Test 4: Test simplified query
    echo "\n4. Testing simplified query...\n";
    
    $simpleQuery = User::withCount(['assignedTasks as completed_tasks_count' => function($query) {
            $query->where('status', 'completed');
        }])
        ->withCount(['assignedTasks as total_tasks_count'])
        ->whereHas('assignedTasks', function($query) {
            $query->where('status', 'completed');
        })
        ->orderBy('completed_tasks_count', 'desc')
        ->limit(5)
        ->get();
    
    echo "   Simplified query results: " . $simpleQuery->count() . "\n";
    
    if ($simpleQuery->count() > 0) {
        echo "   Results:\n";
        foreach ($simpleQuery as $user) {
            echo "     - {$user->name}: {$user->completed_tasks_count} completed, {$user->total_tasks_count} total\n";
        }
    } else {
        echo "   No results from simplified query!\n";
    }
    
    // Test 5: Check if there are any completed tasks this month
    echo "\n5. Checking completed tasks this month...\n";
    
    $thisMonthCompleted = Task::where('status', 'completed')
        ->where(function($q) {
            $q->where(function($subQ) {
                $subQ->whereMonth('completed_at', now()->month)
                     ->whereYear('completed_at', now()->year);
            })
            ->orWhere(function($subQ) {
                $subQ->whereNull('completed_at')
                     ->whereMonth('updated_at', now()->month)
                     ->whereYear('updated_at', now()->year);
            });
        })
        ->count();
    
    echo "   Completed tasks this month: {$thisMonthCompleted}\n";
    
    // Test 6: Check all completed tasks regardless of date
    echo "\n6. Checking all completed tasks...\n";
    
    $allCompleted = Task::where('status', 'completed')->get();
    echo "   All completed tasks: " . $allCompleted->count() . "\n";
    
    if ($allCompleted->count() > 0) {
        echo "   Completed tasks details:\n";
        foreach ($allCompleted as $task) {
            $assignee = $task->assignee ? $task->assignee->name : 'Unassigned';
            $completedAt = $task->completed_at ? $task->completed_at->format('Y-m-d H:i') : 'No date';
            echo "     - Task: {$task->title} | Assignee: {$assignee} | Completed: {$completedAt}\n";
        }
    }
    
    echo "\n✅ Debug completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error during debugging: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Debug completed at: " . date('Y-m-d H:i:s') . "\n";
