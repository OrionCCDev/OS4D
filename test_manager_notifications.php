<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\UnifiedNotification;
use App\Models\Task;
use App\Models\TaskEmailPreparation;

echo "=== MANAGER NOTIFICATION TEST ===\n\n";

// 1. Check what managers exist
echo "STEP 1: Checking managers...\n";
$managers = User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();
echo "Found " . $managers->count() . " managers:\n";
foreach ($managers as $manager) {
    echo "- {$manager->name} ({$manager->email}) - Role: {$manager->role}\n";
}

if ($managers->isEmpty()) {
    echo "❌ No managers found! This is why notifications aren't working.\n";
    exit;
}

// 2. Check if there are any existing notifications
echo "\nSTEP 2: Checking existing notifications...\n";
$totalNotifications = UnifiedNotification::count();
$taskNotifications = UnifiedNotification::where('category', 'task')->count();
$unreadNotifications = UnifiedNotification::where('is_read', false)->count();

echo "Total notifications: {$totalNotifications}\n";
echo "Task notifications: {$taskNotifications}\n";
echo "Unread notifications: {$unreadNotifications}\n";

// 3. Test creating a notification for each manager
echo "\nSTEP 3: Testing notification creation...\n";
$testTaskId = 1; // Use a test task ID
$testEmailPreparation = (object) [
    'id' => 999,
    'subject' => 'Test Email Subject',
    'to_emails' => 'test@example.com, client@example.com'
];

foreach ($managers as $manager) {
    echo "Creating test notification for manager: {$manager->name}...\n";

    try {
        $notification = UnifiedNotification::createTaskNotification(
            $manager->id,
            'email_marked_sent',
            'Email Marked as Sent',
            'Test User marked confirmation email as sent for task "Test Task" to: test@example.com, client@example.com',
            [
                'task_id' => $testTaskId,
                'task_title' => 'Test Task',
                'sender_id' => 999,
                'sender_name' => 'Test User',
                'email_preparation_id' => 999,
                'to_emails' => 'test@example.com, client@example.com',
                'subject' => 'Test Email Subject',
                'action_url' => route('tasks.show', $testTaskId)
            ],
            $testTaskId,
            'normal'
        );

        if ($notification) {
            echo "✅ Notification created successfully (ID: {$notification->id})\n";
        } else {
            echo "❌ Failed to create notification\n";
        }
    } catch (Exception $e) {
        echo "❌ Error creating notification: " . $e->getMessage() . "\n";
    }
}

// 4. Check notifications after creation
echo "\nSTEP 4: Checking notifications after creation...\n";
$newTotalNotifications = UnifiedNotification::count();
$newTaskNotifications = UnifiedNotification::where('category', 'task')->count();
$newUnreadNotifications = UnifiedNotification::where('is_read', false)->count();

echo "Total notifications after: {$newTotalNotifications}\n";
echo "Task notifications after: {$newTaskNotifications}\n";
echo "Unread notifications after: {$newUnreadNotifications}\n";

// 5. Show recent task notifications
echo "\nSTEP 5: Recent task notifications:\n";
$recentNotifications = UnifiedNotification::where('category', 'task')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

foreach ($recentNotifications as $notification) {
    $manager = User::find($notification->user_id);
    echo "- ID: {$notification->id} | User: {$manager->name} | Type: {$notification->type} | Title: {$notification->title}\n";
    echo "  Message: {$notification->message}\n";
    echo "  Created: {$notification->created_at}\n";
    echo "  Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n\n";
}

echo "=== TEST COMPLETE ===\n";
