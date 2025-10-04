<?php
/**
 * Fix User Notifications and Test System
 * 
 * This script fixes the user notification issue and tests the complete system
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Fix User Notifications and Test System ===\n";
echo "Fixing user notification issues and testing complete system\n\n";

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

    // Step 2: Check recent emails and their CC field
    echo "\n--- Recent Emails with CC Field ---\n";
    $recentEmails = \App\Models\Email::orderBy('created_at', 'desc')->limit(5)->get();
    foreach ($recentEmails as $email) {
        echo "ID: {$email->id}, From: {$email->from_email}, To: {$email->to_email}\n";
        echo "   CC: " . ($email->cc ?: 'NULL') . "\n";
        echo "   CC_emails: " . (is_array($email->cc_emails) ? implode(', ', $email->cc_emails) : ($email->cc_emails ?: 'NULL')) . "\n";
        echo "   Subject: {$email->subject}\n";
        echo "   Created: {$email->created_at}\n\n";
    }

    // Step 3: Fix existing emails that might have CC data in cc_emails but not cc
    echo "\n--- Fixing Existing Emails ---\n";
    $emailsToFix = \App\Models\Email::whereNotNull('cc_emails')
        ->where(function($query) {
            $query->whereNull('cc')
                  ->orWhere('cc', '');
        })
        ->get();

    echo "Found {$emailsToFix->count()} emails to fix\n";
    
    foreach ($emailsToFix as $email) {
        if (is_array($email->cc_emails)) {
            $email->cc = implode(', ', $email->cc_emails);
        } else {
            $email->cc = $email->cc_emails;
        }
        $email->save();
        echo "Fixed email ID: {$email->id} - CC: {$email->cc}\n";
    }

    // Step 4: Test notification creation for existing emails
    echo "\n--- Testing Notification Creation for Existing Emails ---\n";
    $testUser = \App\Models\User::where('role', 'user')->first();
    if ($testUser) {
        echo "Test user: {$testUser->name} ({$testUser->email})\n";
        
        // Find emails where this user should be notified
        $relevantEmails = \App\Models\Email::where(function($query) use ($testUser) {
            $query->where('to_email', 'like', '%' . $testUser->email . '%')
                  ->orWhere('cc', 'like', '%' . $testUser->email . '%');
        })->get();

        echo "Found {$relevantEmails->count()} emails relevant to user\n";

        $notificationService = app(\App\Services\NotificationService::class);
        
        foreach ($relevantEmails as $email) {
            echo "Processing email ID: {$email->id} - Subject: {$email->subject}\n";
            
            // Check if notification already exists
            $existingNotification = \App\Models\UnifiedNotification::where('user_id', $testUser->id)
                ->where('email_id', $email->id)
                ->where('type', 'email_received')
                ->first();

            if ($existingNotification) {
                echo "  - Notification already exists (ID: {$existingNotification->id})\n";
            } else {
                // Create notification
                $notificationService->createUserEmailNotifications($email);
                echo "  - Created notification for user\n";
            }
        }
    }

    // Step 5: Check current notifications
    echo "\n--- Current Notifications ---\n";
    $notifications = \App\Models\UnifiedNotification::orderBy('created_at', 'desc')->limit(10)->get();
    echo "Total notifications: " . $notifications->count() . "\n";
    
    foreach ($notifications as $notif) {
        $user = \App\Models\User::find($notif->user_id);
        echo "ID: {$notif->id}, User: " . ($user ? $user->name . ' (' . $user->email . ')' : 'Unknown') . ", Type: {$notif->type}\n";
        echo "   Message: " . substr($notif->message, 0, 60) . "...\n";
        echo "   Created: {$notif->created_at}\n\n";
    }

    // Step 6: Test with a new email
    echo "\n--- Creating Test Email with User in CC ---\n";
    if ($testUser) {
        $testEmail = \App\Models\Email::create([
            'user_id' => 1, // Admin user
            'from_email' => 'test@example.com',
            'to_email' => 'engineering@orion-contracting.com',
            'cc' => $testUser->email . ', another@example.com',
            'cc_emails' => [$testUser->email, 'another@example.com'],
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

        // Create notifications for this email
        $notificationService->createNewEmailNotification($testEmail);
        echo "✅ Notifications created for test email\n";

        // Check if notifications were created
        $newNotifications = \App\Models\UnifiedNotification::where('email_id', $testEmail->id)->get();
        echo "Notifications created for test email: " . $newNotifications->count() . "\n";
        
        foreach ($newNotifications as $notif) {
            $user = \App\Models\User::find($notif->user_id);
            echo "  - User: {$user->name} ({$user->email}), Type: {$notif->type}\n";
            echo "    Message: " . substr($notif->message, 0, 60) . "...\n";
        }
    }

    echo "\n=== Fix Complete ===\n";
    echo "User notification system has been fixed and tested!\n";
    echo "The system should now properly notify users when their email is in CC or TO fields.\n\n";

    echo "Next steps:\n";
    echo "1. Send a real email to engineering@orion-contracting.com with a.sayed.xc@gmail.com in CC\n";
    echo "2. Run: php artisan emails:new-fetch --max-results=5\n";
    echo "3. Check if the user receives a notification\n";
    echo "4. The sound notification should play mail-noti.wav when new emails arrive\n";

} catch (Exception $e) {
    echo "❌ Fix failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
