<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Task;
use App\Models\UnifiedNotification;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Task Status Change Notifications\n";
echo "======================================\n\n";

// Step 1: Get a manager user
echo "1. Getting manager user...\n";
$manager = User::whereIn('role', ['admin', 'manager'])->first();
if (!$manager) {
    echo "   ERROR: No manager found\n";
    exit(1);
}
echo "   Manager: {$manager->name} (ID: {$manager->id})\n\n";

// Step 2: Get a regular user
echo "2. Getting regular user...\n";
$regularUser = User::where('role', 'user')->first();
if (!$regularUser) {
    echo "   ERROR: No regular user found\n";
    exit(1);
}
echo "   Regular user: {$regularUser->name} (ID: {$regularUser->id})\n\n";

// Step 3: Get a task assigned to the regular user
echo "3. Getting task assigned to regular user...\n";
$task = Task::where('assigned_to', $regularUser->id)->first();
if (!$task) {
    echo "   ERROR: No task found assigned to regular user\n";
    exit(1);
}
echo "   Task: {$task->title} (ID: {$task->id})\n";
echo "   Current status: {$task->status}\n";
echo "   Assigned to: {$task->assignee->name}\n\n";

// Step 4: Check current notification count for the user
echo "4. Checking current notification count...\n";
$initialCount = UnifiedNotification::getUnreadCountForUser($regularUser->id);
echo "   Initial unread notifications: {$initialCount}\n\n";

// Step 5: Login as manager and change task status
echo "5. Testing status change notification...\n";
Auth::login($manager);
try {
    $oldStatus = $task->status;
    $newStatus = ($task->status === 'completed') ? 'in_progress' : 'completed';

    echo "   Changing status from '{$oldStatus}' to '{$newStatus}'...\n";
    $task->changeStatus($newStatus, 'Test notification - Manager changed status');

    echo "   SUCCESS: Status changed successfully\n";
    echo "   New status: {$task->fresh()->status}\n";

    // Check if notification was created
    $newCount = UnifiedNotification::getUnreadCountForUser($regularUser->id);
    echo "   New unread notifications: {$newCount}\n";

    if ($newCount > $initialCount) {
        echo "   SUCCESS: Notification sent to assigned user!\n";

        // Show the latest notification
        $latestNotification = UnifiedNotification::forUser($regularUser->id)
            ->latest()
            ->first();
        if ($latestNotification) {
            echo "   Latest notification: {$latestNotification->title}\n";
            echo "   Message: {$latestNotification->message}\n";
        }
    } else {
        echo "   WARNING: No new notification found\n";
    }

} catch (Exception $e) {
    echo "   ERROR: Failed to change status - {$e->getMessage()}\n";
}
Auth::logout();
echo "\n";

echo "Test Complete!\n";
echo "==============\n";
echo "If you see 'SUCCESS: Notification sent to assigned user!' above,\n";
echo "then the notification system is working correctly.\n";
