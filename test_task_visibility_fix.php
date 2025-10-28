<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST: Task Visibility Fix ===\n\n";

// Test with task ID 44 (from your URL)
$taskId = 44;
echo "Testing with Task ID: $taskId\n";

try {
    $task = Task::find($taskId);
    if (!$task) {
        echo "❌ Task not found!\n";
        exit(1);
    }

    echo "✅ Task found: {$task->title}\n";
    echo "   Status: {$task->status}\n";
    echo "   Assigned to: {$task->assigned_to}\n";

    // Get the assigned user
    $assignedUser = User::find($task->assigned_to);
    if (!$assignedUser) {
        echo "❌ Assigned user not found!\n";
        exit(1);
    }

    echo "✅ Assigned user: {$assignedUser->name} (ID: {$assignedUser->id})\n";

    // Test the assignedTasks relationship
    echo "\n🔍 Testing assignedTasks relationship:\n";
    $assignedTasks = $assignedUser->assignedTasks()->get();
    echo "   Total assigned tasks: " . $assignedTasks->count() . "\n";

    $taskInAssignedTasks = $assignedTasks->where('id', $taskId)->first();
    if ($taskInAssignedTasks) {
        echo "   ✅ Task found in assignedTasks relationship\n";
        echo "   Task status in relationship: {$taskInAssignedTasks->status}\n";
    } else {
        echo "   ❌ Task NOT found in assignedTasks relationship\n";
    }

    // Test dashboard data for the user
    echo "\n📊 Testing dashboard data for user:\n";
    $dashboardController = new \App\Http\Controllers\DashboardController();

    // Simulate the getUserDashboardData method
    $user = $assignedUser;
    $now = now();

    // Test recent tasks
    $recentTasks = $user->assignedTasks()->with(['project', 'folder'])
        ->latest()
        ->limit(10)
        ->get();

    echo "   Recent tasks count: " . $recentTasks->count() . "\n";
    $taskInRecent = $recentTasks->where('id', $taskId)->first();
    if ($taskInRecent) {
        echo "   ✅ Task found in recent tasks\n";
    } else {
        echo "   ❌ Task NOT found in recent tasks\n";
    }

    // Test upcoming tasks
    $upcomingTasks = $user->assignedTasks()->with(['project', 'folder'])
        ->whereBetween('due_date', [$now, $now->copy()->addDays(7)])
        ->whereNotIn('status', ['completed'])
        ->orderBy('due_date', 'asc')
        ->get();

    echo "   Upcoming tasks count: " . $upcomingTasks->count() . "\n";
    $taskInUpcoming = $upcomingTasks->where('id', $taskId)->first();
    if ($taskInUpcoming) {
        echo "   ✅ Task found in upcoming tasks\n";
    } else {
        echo "   ❌ Task NOT found in upcoming tasks\n";
    }

    // Test tasks by status with the new ordering
    echo "\n📋 Testing tasks by status ordering:\n";
    $tasksByStatus = $user->assignedTasks()->with(['project', 'folder'])
        ->orderByRaw("
            CASE
                WHEN due_date < NOW() AND status != 'completed' THEN 1
                WHEN status = 'in_progress' THEN 2
                WHEN status = 'assigned' THEN 3
                WHEN status = 'pending' THEN 4
                WHEN status = 'submitted_for_review' THEN 5
                WHEN status = 'in_review' THEN 6
                WHEN status = 'waiting_sending_client_consultant_approve' THEN 7
                WHEN status = 'waiting_client_consultant_approve' THEN 8
                WHEN status = 'approved' THEN 9
                WHEN status = 'ready_for_email' THEN 10
                WHEN status = 'on_client_consultant_review' THEN 11
                WHEN status = 'in_review_after_client_consultant_reply' THEN 12
                WHEN status = 're_submit_required' THEN 13
                WHEN status = 'completed' THEN 14
                WHEN status = 'cancelled' THEN 15
                ELSE 16
            END
        ")
        ->orderBy('due_date', 'asc')
        ->orderBy('created_at', 'desc')
        ->get();

    echo "   Tasks by status count: " . $tasksByStatus->count() . "\n";
    $taskInStatus = $tasksByStatus->where('id', $taskId)->first();
    if ($taskInStatus) {
        echo "   ✅ Task found in tasks by status\n";
        echo "   Task status: {$taskInStatus->status}\n";

        // Find the position in the ordered list
        $position = $tasksByStatus->search(function($item) use ($taskId) {
            return $item->id == $taskId;
        });
        echo "   Position in ordered list: " . ($position + 1) . "\n";
    } else {
        echo "   ❌ Task NOT found in tasks by status\n";
    }

    // Test task statistics
    echo "\n📈 Testing task statistics:\n";
    $userTasks = $user->assignedTasks();
    $taskStats = [
        'total' => $userTasks->count(),
        'completed' => $userTasks->where('status', 'completed')->count(),
        'in_progress' => $userTasks->where('status', 'in_progress')->count(),
        'pending' => $userTasks->where('status', 'pending')->count(),
        'assigned' => $userTasks->where('status', 'assigned')->count(),
        'in_review' => $userTasks->where('status', 'in_review')->count(),
        'on_client_consultant_review' => $userTasks->where('status', 'on_client_consultant_review')->count(),
    ];

    foreach ($taskStats as $status => $count) {
        echo "   {$status}: {$count}\n";
    }

    echo "\n=== Test Complete ===\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
