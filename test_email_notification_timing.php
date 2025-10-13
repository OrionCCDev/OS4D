<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== EMAIL NOTIFICATION TIMING TEST ===\n\n";

// Step 1: Check current schedule configuration
echo "STEP 1: Checking Schedule Configuration...\n";
echo str_repeat("-", 60) . "\n";

// Read the console.php file to verify schedule
$consoleFile = file_get_contents(__DIR__ . '/routes/console.php');
if (strpos($consoleFile, '->everyMinute()') !== false && strpos($consoleFile, 'emails:new-fetch') !== false) {
    echo "✅ Email fetch is configured to run EVERY MINUTE\n";
} elseif (strpos($consoleFile, '->everyFiveMinutes()') !== false && strpos($consoleFile, 'emails:new-fetch') !== false) {
    echo "⚠️  Email fetch is configured to run EVERY 5 MINUTES (slower)\n";
} else {
    echo "❌ Could not determine email fetch schedule\n";
}

// Step 2: Check last email fetch time
echo "\n\nSTEP 2: Checking Last Email Fetch...\n";
echo str_repeat("-", 60) . "\n";

$lastEmail = \App\Models\Email::where('email_source', 'designers_inbox')
    ->orderBy('created_at', 'desc')
    ->first();

if ($lastEmail) {
    $timeSinceLastFetch = now()->diffInMinutes($lastEmail->created_at);
    echo "Last email fetched: {$lastEmail->created_at}\n";
    echo "Time since last fetch: {$timeSinceLastFetch} minutes ago\n";
    
    if ($timeSinceLastFetch <= 1) {
        echo "✅ Email was fetched very recently (within 1 minute)\n";
    } elseif ($timeSinceLastFetch <= 5) {
        echo "✅ Email was fetched recently (within 5 minutes)\n";
    } else {
        echo "⚠️  Last email fetch was {$timeSinceLastFetch} minutes ago\n";
    }
} else {
    echo "❌ No emails found in database\n";
}

// Step 3: Check notification creation timing
echo "\n\nSTEP 3: Checking Notification Creation Timing...\n";
echo str_repeat("-", 60) . "\n";

$lastNotification = \App\Models\UnifiedNotification::where('category', 'email')
    ->orderBy('created_at', 'desc')
    ->first();

if ($lastNotification) {
    $timeSinceLastNotif = now()->diffInMinutes($lastNotification->created_at);
    echo "Last notification created: {$lastNotification->created_at}\n";
    echo "Time since last notification: {$timeSinceLastNotif} minutes ago\n";
    
    if ($timeSinceLastNotif <= 1) {
        echo "✅ Notification was created very recently (within 1 minute)\n";
    } elseif ($timeSinceLastNotif <= 5) {
        echo "✅ Notification was created recently (within 5 minutes)\n";
    } else {
        echo "⚠️  Last notification was {$timeSinceLastNotif} minutes ago\n";
    }
    
    // Check if email and notification were created at similar times
    if ($lastEmail && $lastNotification) {
        $timeDiff = abs($lastEmail->created_at->diffInSeconds($lastNotification->created_at));
        echo "\nTime difference between email fetch and notification: {$timeDiff} seconds\n";
        
        if ($timeDiff <= 10) {
            echo "✅ Notifications are created immediately after email fetch (< 10 seconds)\n";
        } elseif ($timeDiff <= 60) {
            echo "✅ Notifications are created quickly after email fetch (< 1 minute)\n";
        } else {
            echo "⚠️  There's a delay between email fetch and notification creation\n";
        }
    }
} else {
    echo "❌ No email notifications found in database\n";
}

// Step 4: Test notification delivery to managers
echo "\n\nSTEP 4: Testing Notification Delivery to Managers...\n";
echo str_repeat("-", 60) . "\n";

$managers = \App\Models\User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();
echo "Found {$managers->count()} managers:\n";

foreach ($managers as $manager) {
    echo "\n{$manager->name} ({$manager->email}):\n";
    
    // Check unread notifications
    $unreadCount = \App\Models\UnifiedNotification::where('user_id', $manager->id)
        ->where('is_read', false)
        ->count();
    
    // Check recent notifications (last 10 minutes)
    $recentCount = \App\Models\UnifiedNotification::where('user_id', $manager->id)
        ->where('created_at', '>=', now()->subMinutes(10))
        ->count();
    
    echo "  - Unread notifications: {$unreadCount}\n";
    echo "  - Notifications in last 10 minutes: {$recentCount}\n";
    
    if ($recentCount > 0) {
        echo "  ✅ Manager is receiving notifications\n";
        
        // Show most recent notification
        $lastNotif = \App\Models\UnifiedNotification::where('user_id', $manager->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastNotif) {
            echo "  Last notification: [{$lastNotif->type}] {$lastNotif->title}\n";
            echo "  Created: {$lastNotif->created_at}\n";
        }
    } else {
        echo "  ⚠️  No recent notifications in last 10 minutes\n";
    }
}

// Step 5: Test notification delivery to users
echo "\n\nSTEP 5: Testing Notification Delivery to Users...\n";
echo str_repeat("-", 60) . "\n";

$users = \App\Models\User::where('role', 'user')->get();
echo "Found {$users->count()} users:\n";

foreach ($users as $user) {
    echo "\n{$user->name} ({$user->email}):\n";
    
    // Check unread notifications
    $unreadCount = \App\Models\UnifiedNotification::where('user_id', $user->id)
        ->where('is_read', false)
        ->count();
    
    // Check recent notifications (last 10 minutes)
    $recentCount = \App\Models\UnifiedNotification::where('user_id', $user->id)
        ->where('created_at', '>=', now()->subMinutes(10))
        ->count();
    
    // Check engineering inbox notifications
    $engineeringCount = \App\Models\UnifiedNotification::where('user_id', $user->id)
        ->where('type', 'engineering_inbox_user_involved')
        ->where('created_at', '>=', now()->subMinutes(10))
        ->count();
    
    echo "  - Unread notifications: {$unreadCount}\n";
    echo "  - Notifications in last 10 minutes: {$recentCount}\n";
    echo "  - Engineering inbox notifications (last 10 min): {$engineeringCount}\n";
    
    if ($recentCount > 0) {
        echo "  ✅ User is receiving notifications\n";
        
        // Show most recent notification
        $lastNotif = \App\Models\UnifiedNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastNotif) {
            echo "  Last notification: [{$lastNotif->type}] {$lastNotif->title}\n";
            echo "  Created: {$lastNotif->created_at}\n";
        }
    } else {
        echo "  ⚠️  No recent notifications in last 10 minutes\n";
    }
}

// Step 6: Provide testing instructions
echo "\n\n" . str_repeat("=", 60) . "\n";
echo "STEP 6: MANUAL TESTING INSTRUCTIONS\n";
echo str_repeat("=", 60) . "\n\n";

echo "To test the notification system:\n\n";
echo "1. Send a test email to: engineering@orion-contracting.com\n";
echo "   - Include one of these emails in TO/CC:\n";
foreach ($users as $user) {
    echo "     • {$user->email} ({$user->name})\n";
}
echo "\n";
echo "2. Wait 1-2 minutes for the cron job to run\n";
echo "\n";
echo "3. Run this command again to check if notifications were created:\n";
echo "   php test_email_notification_timing.php\n";
echo "\n";
echo "4. Check the UI:\n";
echo "   - Log in as a manager and check the notification bell\n";
echo "   - Log in as a user (if their email was in TO/CC) and check notifications\n";
echo "\n";
echo "5. Check logs for any errors:\n";
echo "   tail -50 storage/logs/laravel.log\n";
echo "\n";

// Step 7: Quick cron status check
echo "\n" . str_repeat("=", 60) . "\n";
echo "STEP 7: CRON STATUS CHECK\n";
echo str_repeat("=", 60) . "\n\n";

echo "Current time: " . now()->format('Y-m-d H:i:s') . "\n";
echo "Timezone: " . config('app.timezone') . "\n";
echo "\n";
echo "To verify cron is running:\n";
echo "1. Check crontab: crontab -l\n";
echo "2. Check if schedule is running: ps aux | grep 'schedule:run'\n";
echo "3. Manually trigger email fetch: php artisan emails:new-fetch --max-results=5\n";
echo "\n";

echo "\n=== TEST COMPLETE ===\n";
echo "\nIf you see ✅ marks above, the system is working correctly!\n";
echo "If you see ⚠️ or ❌ marks, there may be issues that need fixing.\n\n";

