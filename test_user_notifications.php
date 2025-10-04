<?php
/**
 * Test User Notification System
 * 
 * This script tests the new user notification system for emails
 * where users (role: 'user') get notified about relevant emails.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== User Notification System Test ===\n";
echo "Testing user notifications for relevant emails\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Test 1: Check current users
    echo "\n--- Current Users ---\n";
    $users = \App\Models\User::select('id', 'name', 'email', 'role')->get();
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
    }

    // Test 2: Check current notifications
    echo "\n--- Current Notifications ---\n";
    $notifications = \App\Models\UnifiedNotification::orderBy('created_at', 'desc')->limit(10)->get();
    echo "Total notifications: " . $notifications->count() . "\n";
    foreach ($notifications as $notif) {
        echo "ID: {$notif->id}, User: {$notif->user_id}, Type: {$notif->type}, Message: " . substr($notif->message, 0, 50) . "...\n";
    }

    // Test 3: Create a test email with user in CC
    echo "\n--- Creating Test Email with User in CC ---\n";
    $testUser = \App\Models\User::where('role', 'user')->first();
    if ($testUser) {
        echo "Test user found: {$testUser->name} ({$testUser->email})\n";
        
        // Create a test email
        $testEmail = \App\Models\Email::create([
            'user_id' => 1, // Admin user
            'from_email' => 'test@example.com',
            'to_email' => 'engineering@orion-contracting.com',
            'cc' => $testUser->email . ', another@example.com',
            'subject' => 'Test Email for User Notifications - ' . now()->format('Y-m-d H:i:s'),
            'body' => 'This is a test email to verify user notifications are working correctly.',
            'email_type' => 'received',
            'status' => 'received',
            'is_tracked' => false,
            'received_at' => now(),
            'message_id' => 'test-user-notification-' . time(),
        ]);
        
        echo "✅ Test email created with ID: {$testEmail->id}\n";
        echo "   From: {$testEmail->from_email}\n";
        echo "   To: {$testEmail->to_email}\n";
        echo "   CC: {$testEmail->cc}\n";
        echo "   Subject: {$testEmail->subject}\n";

        // Test 4: Test notification creation
        echo "\n--- Testing Notification Creation ---\n";
        $notificationService = app(\App\Services\NotificationService::class);
        
        // Create notifications for this email
        $notificationService->createNewEmailNotification($testEmail);
        
        echo "✅ Notification creation triggered\n";

        // Test 5: Check if notifications were created
        echo "\n--- Checking Created Notifications ---\n";
        $newNotifications = \App\Models\UnifiedNotification::where('email_id', $testEmail->id)->get();
        echo "Notifications created for test email: " . $newNotifications->count() . "\n";
        
        foreach ($newNotifications as $notif) {
            $user = \App\Models\User::find($notif->user_id);
            echo "  - User: {$user->name} ({$user->email}), Type: {$notif->type}, Message: " . substr($notif->message, 0, 60) . "...\n";
        }

        // Test 6: Test reply notification
        echo "\n--- Testing Reply Notification ---\n";
        $replyEmail = \App\Models\Email::create([
            'user_id' => 1, // Admin user
            'from_email' => 'reply@example.com',
            'to_email' => 'engineering@orion-contracting.com',
            'cc' => $testUser->email,
            'subject' => 'Re: ' . $testEmail->subject,
            'body' => 'This is a reply to the test email.',
            'email_type' => 'received',
            'status' => 'received',
            'is_tracked' => false,
            'received_at' => now(),
            'message_id' => 'test-reply-' . time(),
            'in_reply_to_email_id' => $testEmail->id,
        ]);
        
        echo "✅ Reply email created with ID: {$replyEmail->id}\n";
        
        // Create reply notifications
        $notificationService->createReplyNotification($replyEmail);
        
        echo "✅ Reply notification creation triggered\n";

        // Check reply notifications
        $replyNotifications = \App\Models\UnifiedNotification::where('email_id', $replyEmail->id)->get();
        echo "Reply notifications created: " . $replyNotifications->count() . "\n";
        
        foreach ($replyNotifications as $notif) {
            $user = \App\Models\User::find($notif->user_id);
            echo "  - User: {$user->name} ({$user->email}), Type: {$notif->type}, Message: " . substr($notif->message, 0, 60) . "...\n";
        }

    } else {
        echo "❌ No users with role 'user' found\n";
    }

    echo "\n=== Test Summary ===\n";
    echo "User notification system test completed!\n";
    echo "Check the notifications above to see if users are getting notified about relevant emails.\n\n";

    echo "Next steps:\n";
    echo "1. Send a real email to engineering@orion-contracting.com with a user in CC\n";
    echo "2. Check if the user receives a notification\n";
    echo "3. Reply to that email and check if the user gets a reply notification\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
