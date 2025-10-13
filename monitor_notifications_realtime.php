<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== REAL-TIME NOTIFICATION MONITOR ===\n";
echo "This script will monitor for new notifications in real-time.\n";
echo "Press Ctrl+C to stop monitoring.\n\n";

// Get baseline counts
$lastEmailId = \App\Models\Email::where('email_source', 'designers_inbox')->max('id') ?? 0;
$lastNotificationId = \App\Models\UnifiedNotification::max('id') ?? 0;

echo "Starting monitoring...\n";
echo "Last Email ID: {$lastEmailId}\n";
echo "Last Notification ID: {$lastNotificationId}\n";
echo str_repeat("-", 60) . "\n\n";

$checkCount = 0;

while (true) {
    $checkCount++;
    $currentTime = now()->format('Y-m-d H:i:s');
    
    // Check for new emails
    $newEmails = \App\Models\Email::where('email_source', 'designers_inbox')
        ->where('id', '>', $lastEmailId)
        ->orderBy('id', 'asc')
        ->get();
    
    if ($newEmails->count() > 0) {
        foreach ($newEmails as $email) {
            echo "🆕 [{$currentTime}] NEW EMAIL DETECTED!\n";
            echo "   ID: {$email->id}\n";
            echo "   From: {$email->from_email}\n";
            echo "   To: {$email->to_email}\n";
            echo "   CC: {$email->cc}\n";
            echo "   Subject: " . substr($email->subject, 0, 50) . "...\n";
            echo "   Created: {$email->created_at}\n";
            echo "\n";
            
            $lastEmailId = $email->id;
        }
    }
    
    // Check for new notifications
    $newNotifications = \App\Models\UnifiedNotification::where('id', '>', $lastNotificationId)
        ->orderBy('id', 'asc')
        ->get();
    
    if ($newNotifications->count() > 0) {
        foreach ($newNotifications as $notification) {
            $user = \App\Models\User::find($notification->user_id);
            
            echo "🔔 [{$currentTime}] NEW NOTIFICATION CREATED!\n";
            echo "   ID: {$notification->id}\n";
            echo "   User: {$user->name} ({$user->email}) - Role: {$user->role}\n";
            echo "   Type: {$notification->type}\n";
            echo "   Title: {$notification->title}\n";
            echo "   Message: " . substr($notification->message, 0, 60) . "...\n";
            echo "   Created: {$notification->created_at}\n";
            
            // Check if this is an engineering inbox notification
            if (in_array($notification->type, ['engineering_inbox_received', 'engineering_inbox_user_involved'])) {
                echo "   ✅ This is an ENGINEERING INBOX notification!\n";
            }
            
            echo "\n";
            
            $lastNotificationId = $notification->id;
        }
    }
    
    // Show periodic status (every 10 checks = ~1 minute)
    if ($checkCount % 10 == 0) {
        echo "⏰ [{$currentTime}] Still monitoring... (checked {$checkCount} times)\n";
        echo "   Last Email ID: {$lastEmailId}\n";
        echo "   Last Notification ID: {$lastNotificationId}\n";
        echo "\n";
    }
    
    // Wait 6 seconds between checks (10 checks per minute)
    sleep(6);
}

