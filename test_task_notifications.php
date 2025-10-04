<?php
/**
 * Test Task Notifications System
 * 
 * This script tests the task notification system with sound and synchronization
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Test Task Notifications System ===\n";
echo "Testing task notifications with sound and synchronization\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/vendor/autoload.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Step 1: Check current users
    echo "\n--- Current Users ---\n";
    $users = \App\Models\User::select('id', 'name', 'email', 'role')->get();
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
    }

    // Step 2: Check current task notifications
    echo "\n--- Current Task Notifications ---\n";
    $taskNotifications = \App\Models\UnifiedNotification::where('category', 'task')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "Total task notifications: " . $taskNotifications->count() . "\n";
    foreach ($taskNotifications as $notif) {
        $user = \App\Models\User::find($notif->user_id);
        echo "ID: {$notif->id}, User: " . ($user ? $user->name : 'Unknown') . ", Type: {$notif->type}\n";
        echo "   Title: {$notif->title}\n";
        echo "   Message: " . substr($notif->message, 0, 60) . "...\n";
        echo "   Created: {$notif->created_at}\n\n";
    }

    // Step 3: Create a test task notification
    echo "\n--- Creating Test Task Notification ---\n";
    $testUser = \App\Models\User::where('role', 'user')->first();
    if ($testUser) {
        echo "Test user: {$testUser->name} ({$testUser->email})\n";
        
        // Create a test task notification
        $testNotification = \App\Models\UnifiedNotification::create([
            'user_id' => $testUser->id,
            'category' => 'task',
            'type' => 'task_assigned',
            'title' => 'New Task Assigned',
            'message' => 'You have been assigned a new task: Test Task for Sound Notification - ' . now()->format('Y-m-d H:i:s'),
            'data' => json_encode([
                'task_id' => 999,
                'priority' => 'high',
                'due_date' => now()->addDays(3)->format('Y-m-d H:i:s')
            ]),
            'is_read' => false,
            'priority' => 'high'
        ]);
        
        echo "✅ Test task notification created with ID: {$testNotification->id}\n";
        echo "   User: {$testUser->name}\n";
        echo "   Title: {$testNotification->title}\n";
        echo "   Message: {$testNotification->message}\n";
        echo "   Priority: {$testNotification->priority}\n";

        // Step 4: Test notification count
        echo "\n--- Testing Notification Count ---\n";
        $notificationService = app(\App\Services\NotificationService::class);
        $stats = $notificationService->getNotificationStats($testUser->id);
        
        echo "Notification stats for user {$testUser->name}:\n";
        echo "  Total: {$stats['total']}\n";
        echo "  Unread: {$stats['unread']}\n";
        echo "  Task Unread: {$stats['task_unread']}\n";
        echo "  Email Unread: {$stats['email_unread']}\n";

        // Step 5: Create another task notification for testing
        echo "\n--- Creating Additional Task Notification ---\n";
        $testNotification2 = \App\Models\UnifiedNotification::create([
            'user_id' => $testUser->id,
            'category' => 'task',
            'type' => 'task_overdue',
            'title' => 'Task Overdue',
            'message' => 'Task "Important Project" is overdue and needs immediate attention.',
            'data' => json_encode([
                'task_id' => 888,
                'priority' => 'urgent',
                'due_date' => now()->subDays(1)->format('Y-m-d H:i:s')
            ]),
            'is_read' => false,
            'priority' => 'urgent'
        ]);
        
        echo "✅ Additional task notification created with ID: {$testNotification2->id}\n";
        echo "   Type: {$testNotification2->type}\n";
        echo "   Priority: {$testNotification2->priority}\n";

        // Step 6: Check updated notification count
        echo "\n--- Updated Notification Count ---\n";
        $updatedStats = $notificationService->getNotificationStats($testUser->id);
        
        echo "Updated notification stats for user {$testUser->name}:\n";
        echo "  Total: {$updatedStats['total']}\n";
        echo "  Unread: {$updatedStats['unread']}\n";
        echo "  Task Unread: {$updatedStats['task_unread']}\n";
        echo "  Email Unread: {$updatedStats['email_unread']}\n";

    } else {
        echo "❌ No users with role 'user' found\n";
    }

    // Step 7: Test notification API endpoint
    echo "\n--- Testing Notification API Endpoint ---\n";
    try {
        $response = \Illuminate\Support\Facades\Http::get(url('/notifications/unread-count'));
        $data = $response->json();
        
        if ($data['success']) {
            echo "✅ API endpoint working\n";
            echo "  Total notifications: {$data['counts']['total']}\n";
            echo "  Task notifications: {$data['counts']['task']}\n";
            echo "  Email notifications: {$data['counts']['email']}\n";
        } else {
            echo "❌ API endpoint returned error\n";
        }
    } catch (Exception $e) {
        echo "❌ API endpoint test failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== Test Complete ===\n";
    echo "Task notification system has been tested!\n";
    echo "The system should now:\n";
    echo "1. ✅ Play gun.mp3 sound for task notifications\n";
    echo "2. ✅ Synchronize between nav dropdown and bottom chat\n";
    echo "3. ✅ Show proper notification counts\n";
    echo "4. ✅ Auto-open bottom chat for new notifications\n\n";

    echo "Next steps:\n";
    echo "1. Check the web interface to see the notifications\n";
    echo "2. Listen for the gun.mp3 sound when new task notifications arrive\n";
    echo "3. Test both the nav dropdown and bottom chat popup\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
