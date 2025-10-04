<?php
/**
 * Test Real Email with CC
 *
 * This script tests the system with a real email that has CC data
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Test Real Email with CC ===\n";
echo "Testing the system with real email data\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Step 1: Check current users
    echo "\n--- Current Users ---\n";
    $users = \App\Models\User::select('id', 'name', 'email', 'role')->get();
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
    }

    // Step 2: Create a test email with proper CC data
    echo "\n--- Creating Test Email with Proper CC Data ---\n";
    $testUser = \App\Models\User::where('role', 'user')->first();

    if ($testUser) {
        echo "Test user: {$testUser->name} ({$testUser->email})\n";

        // Create a test email with CC data
        $testEmail = \App\Models\Email::create([
            'user_id' => 1, // Admin user
            'from_email' => 'test@example.com',
            'to_email' => 'engineering@orion-contracting.com',
            'cc' => $testUser->email . ', another@example.com',
            'cc_emails' => [$testUser->email, 'another@example.com'],
            'subject' => 'Test Email with CC - ' . now()->format('Y-m-d H:i:s'),
            'body' => 'This is a test email with proper CC data to verify user notifications.',
            'email_type' => 'received',
            'status' => 'received',
            'is_tracked' => false,
            'received_at' => now(),
            'message_id' => 'test-cc-email-' . time(),
        ]);

        echo "✅ Test email created with ID: {$testEmail->id}\n";
        echo "   From: {$testEmail->from_email}\n";
        echo "   To: {$testEmail->to_email}\n";
        echo "   CC: {$testEmail->cc}\n";
        echo "   Subject: {$testEmail->subject}\n";

        // Step 3: Test notification creation
        echo "\n--- Testing Notification Creation ---\n";
        $notificationService = app(\App\Services\NotificationService::class);

        // Create notifications for this email
        $notificationService->createNewEmailNotification($testEmail);
        echo "✅ Notification creation triggered\n";

        // Step 4: Check if notifications were created
        echo "\n--- Checking Created Notifications ---\n";
        $newNotifications = \App\Models\UnifiedNotification::where('email_id', $testEmail->id)->get();
        echo "Notifications created for test email: " . $newNotifications->count() . "\n";

        foreach ($newNotifications as $notif) {
            $user = \App\Models\User::find($notif->user_id);
            echo "  - User: {$user->name} ({$user->email}), Type: {$notif->type}\n";
            echo "    Message: " . substr($notif->message, 0, 60) . "...\n";
        }

        // Step 5: Test the user notification logic directly
        echo "\n--- Testing User Notification Logic ---\n";
        $notificationService->createUserEmailNotifications($testEmail);
        echo "✅ User notification creation triggered\n";

        // Check user notifications
        $userNotifications = \App\Models\UnifiedNotification::where('email_id', $testEmail->id)
            ->where('user_id', $testUser->id)
            ->get();
        echo "User notifications created: " . $userNotifications->count() . "\n";

        foreach ($userNotifications as $notif) {
            echo "  - User: {$testUser->name} ({$testUser->email}), Type: {$notif->type}\n";
            echo "    Message: " . substr($notif->message, 0, 60) . "...\n";
        }

    } else {
        echo "❌ No users with role 'user' found\n";
    }

    echo "\n=== Test Complete ===\n";
    echo "The system has been tested with proper CC data.\n";
    echo "If user notifications were created, the system is working correctly!\n\n";

    echo "Next steps:\n";
    echo "1. Send a real email to engineering@orion-contracting.com with a.sayed.xc@gmail.com in CC\n";
    echo "2. Run: php artisan emails:new-fetch --max-results=5\n";
    echo "3. Check if the user receives a notification\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
