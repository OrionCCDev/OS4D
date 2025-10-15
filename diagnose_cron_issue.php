<?php

/**
 * Comprehensive Cron Job Diagnostic Script
 * This script checks all aspects of the email notification system
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 COMPREHENSIVE CRON JOB DIAGNOSTIC\n";
echo "=====================================\n\n";

// 1. Check if cron is configured
echo "1️⃣ CRON CONFIGURATION:\n";
echo "   Run this command to check your crontab:\n";
echo "   crontab -l | grep artisan\n\n";
echo "   Expected output:\n";
echo "   * * * * * cd /home/edlb2bdo7yna/public_html/odc.com && php artisan schedule:run >> /dev/null 2>&1\n\n";

// 2. Check Laravel scheduler
echo "2️⃣ LARAVEL SCHEDULER STATUS:\n";
try {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
    $events = $schedule->events();
    echo "   Total scheduled commands: " . count($events) . "\n";

    foreach ($events as $event) {
        $command = $event->command ?? $event->description ?? 'Unknown';
        echo "   - " . $command . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 3. Check cache lock status
echo "3️⃣ CACHE LOCK STATUS:\n";
try {
    $lockExists = \Illuminate\Support\Facades\Cache::has('new-email-fetch:running');
    echo "   Lock exists: " . ($lockExists ? "YES ⚠️" : "NO ✅") . "\n";

    if ($lockExists) {
        $lockValue = \Illuminate\Support\Facades\Cache::get('new-email-fetch:running');
        $lockTime = explode('-', $lockValue)[0] ?? 0;
        $age = time() - $lockTime;
        echo "   Lock age: " . $age . " seconds\n";
        echo "   Lock is " . ($age > 120 ? "STALE (will be auto-cleared)" : "FRESH") . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 4. Check IMAP connection
echo "4️⃣ IMAP CONNECTION TEST:\n";
try {
    $host = config('mail.imap.host');
    $port = config('mail.imap.port');
    $username = config('mail.imap.username');

    echo "   Host: " . $host . "\n";
    echo "   Port: " . $port . "\n";
    echo "   Username: " . $username . "\n";

    $connection = @imap_open(
        "{{$host}:{$port}/imap/ssl}INBOX",
        $username,
        config('mail.imap.password')
    );

    if ($connection) {
        $mailboxInfo = imap_check($connection);
        echo "   ✅ IMAP connection successful!\n";
        echo "   Total messages in inbox: " . $mailboxInfo->Nmsgs . "\n";
        imap_close($connection);
    } else {
        echo "   ❌ IMAP connection failed: " . imap_last_error() . "\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 5. Check recent emails in database
echo "5️⃣ RECENT EMAILS IN DATABASE:\n";
try {
    $recentEmails = \App\Models\Email::orderBy('created_at', 'desc')->take(5)->get();
    echo "   Total emails in DB: " . \App\Models\Email::count() . "\n";
    echo "   Latest 5 emails:\n";
    foreach ($recentEmails as $email) {
        echo "   - ID: {$email->id} | From: {$email->from_email}\n";
        echo "     Subject: {$email->subject}\n";
        echo "     Received: {$email->received_at}\n";
        echo "     Created in DB: {$email->created_at}\n\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 6. Check managers
echo "6️⃣ MANAGERS IN SYSTEM:\n";
try {
    $managers = \App\Models\User::whereIn('role', ['admin', 'manager'])->get();
    echo "   Total managers/admins: " . $managers->count() . "\n\n";

    foreach ($managers as $manager) {
        $unreadCount = \App\Models\UnifiedNotification::where('user_id', $manager->id)
            ->where('category', 'email')
            ->where('is_read', false)
            ->count();

        $totalCount = \App\Models\UnifiedNotification::where('user_id', $manager->id)
            ->where('category', 'email')
            ->count();

        echo "   Manager: {$manager->name} (ID: {$manager->id})\n";
        echo "   Email: {$manager->email}\n";
        echo "   Role: {$manager->role}\n";
        echo "   Total email notifications: {$totalCount}\n";
        echo "   Unread email notifications: {$unreadCount}\n\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 7. Check when schedule:run was last executed
echo "7️⃣ SCHEDULER EXECUTION CHECK:\n";
echo "   To check if the scheduler is running, look at the cache:\n";
try {
    $cacheDriver = config('cache.default');
    echo "   Cache driver: {$cacheDriver}\n";

    // Check for schedule mutex
    $scheduleMutex = \Illuminate\Support\Facades\Cache::get('illuminate:schedule:running');
    if ($scheduleMutex) {
        echo "   ⚠️ Schedule is currently running\n";
    } else {
        echo "   ℹ️ Schedule is not currently running\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 8. Test manual email fetch
echo "8️⃣ MANUAL FETCH TEST:\n";
echo "   Run this command to manually test:\n";
echo "   php artisan emails:new-fetch --max-results=10\n\n";

// 9. Check log file
echo "9️⃣ LOG FILE STATUS:\n";
try {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logSize = filesize($logPath);
        echo "   Log file size: " . number_format($logSize / 1024 / 1024, 2) . " MB\n";

        if ($logSize > 10 * 1024 * 1024) {
            echo "   ⚠️ Log file is large (>10MB), consider clearing it\n";
        } else {
            echo "   ✅ Log file size is normal\n";
        }
    } else {
        echo "   ℹ️ Log file doesn't exist yet\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// 10. Recommendations
echo "🔧 RECOMMENDATIONS:\n";
echo "=====================================\n\n";

$lockExists = \Illuminate\Support\Facades\Cache::has('new-email-fetch:running');
if ($lockExists) {
    echo "⚠️ ISSUE: Lock is stuck\n";
    echo "   Solution: Run this command:\n";
    echo "   php artisan tinker --execute=\"DB::table('cache')->where('key', 'like', '%email-fetch%')->delete(); echo 'Lock cleared';\"\n\n";
}

echo "✅ TO FIX AUTOMATIC FETCHING:\n";
echo "   1. Verify cron is running:\n";
echo "      crontab -l | grep artisan\n\n";
echo "   2. If cron is missing, add it:\n";
echo "      crontab -e\n";
echo "      Then add this line:\n";
echo "      * * * * * cd /home/edlb2bdo7yna/public_html/odc.com && php artisan schedule:run >> /dev/null 2>&1\n\n";
echo "   3. Test the scheduler manually:\n";
echo "      php artisan schedule:run\n\n";
echo "   4. Check if emails are being fetched:\n";
echo "      tail -f storage/logs/laravel.log\n\n";
echo "   5. Send a test email and wait 1-2 minutes\n\n";

echo "✅ DIAGNOSTIC COMPLETE!\n";

