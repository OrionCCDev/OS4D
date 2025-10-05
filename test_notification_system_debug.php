<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Email;

echo "=== Notification System Debug Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // 1. Check current user
    echo "1. Checking current user...\n";
    $user = User::first();
    if ($user) {
        echo "   User: {$user->name} ({$user->email})\n";
        echo "   Role: {$user->role}\n";
        echo "   Is Manager: " . ($user->isManager() ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ERROR: No users found in database\n";
        exit(1);
    }

    // 2. Check available notification tables
    echo "\n2. Checking notification tables...\n";
    $tables = [
        'notifications' => 'Laravel default notifications',
        'unified_notifications' => 'Unified notifications system',
        'custom_notifications' => 'Custom notifications',
        'designers_inbox_notifications' => 'Designers inbox notifications',
        'task_notifications' => 'Task notifications'
    ];

    foreach ($tables as $table => $description) {
        try {
            $count = DB::table($table)->count();
            echo "   {$table}: {$count} records ({$description})\n";
        } catch (Exception $e) {
            echo "   {$table}: ERROR - {$e->getMessage()}\n";
        }
    }

    // 3. Check emails
    echo "\n3. Checking emails...\n";
    $emailCount = Email::count();
    echo "   Total emails: {$emailCount}\n";

    if ($emailCount > 0) {
        $latestEmail = Email::latest()->first();
        echo "   Latest email ID: {$latestEmail->id}\n";
        echo "   Latest email subject: {$latestEmail->subject}\n";
        echo "   Latest email from: {$latestEmail->from_email}\n";
        echo "   Latest email body length: " . strlen($latestEmail->body ?? '') . " characters\n";
    }

    // 4. Check which notification system is being used
    echo "\n4. Checking notification system usage...\n";

    // Check Laravel notifications
    try {
        $laravelNotifications = DB::table('notifications')->where('notifiable_id', $user->id)->count();
        echo "   Laravel notifications for user: {$laravelNotifications}\n";

        if ($laravelNotifications > 0) {
            $notification = DB::table('notifications')->where('notifiable_id', $user->id)->first();
            echo "   Sample notification type: {$notification->type}\n";
            echo "   Sample notification data: " . substr($notification->data, 0, 100) . "...\n";
        }
    } catch (Exception $e) {
        echo "   Laravel notifications: ERROR - {$e->getMessage()}\n";
    }

    // Check unified notifications
    try {
        $unifiedNotifications = DB::table('unified_notifications')->where('user_id', $user->id)->count();
        echo "   Unified notifications for user: {$unifiedNotifications}\n";

        if ($unifiedNotifications > 0) {
            $notification = DB::table('unified_notifications')->where('user_id', $user->id)->first();
            echo "   Sample unified notification: {$notification->title}\n";
            echo "   Sample unified notification message: " . substr($notification->message, 0, 100) . "...\n";
        }
    } catch (Exception $e) {
        echo "   Unified notifications: ERROR - {$e->getMessage()}\n";
    }

    // Check designers inbox notifications
    try {
        $designersNotifications = DB::table('designers_inbox_notifications')->where('user_id', $user->id)->count();
        echo "   Designers inbox notifications for user: {$designersNotifications}\n";

        if ($designersNotifications > 0) {
            $notification = DB::table('designers_inbox_notifications')->where('user_id', $user->id)->first();
            echo "   Sample designers notification: {$notification->title}\n";
            echo "   Sample designers notification message: " . substr($notification->message, 0, 100) . "...\n";
            echo "   Sample designers notification email_id: " . ($notification->email_id ?? 'NULL') . "\n";
        }
    } catch (Exception $e) {
        echo "   Designers inbox notifications: ERROR - {$e->getMessage()}\n";
    }

    // 5. Test email show access
    echo "\n5. Testing email show access...\n";
    if ($emailCount > 0) {
        $testEmail = Email::first();
        echo "   Testing access to email ID: {$testEmail->id}\n";

        if (!$user->isManager()) {
            echo "   ❌ ACCESS DENIED: Only managers can view emails\n";
        } else {
            echo "   ✅ ACCESS GRANTED: User can view emails\n";
        }
    }

    // 6. Check notification routes
    echo "\n6. Checking notification routes...\n";
    $routes = [
        'emails.show' => '/emails/{id}',
        'notifications.mark-read' => '/notifications/{id}/mark-read',
        'email-monitoring.unread-count' => '/email-monitoring/unread-count'
    ];

    foreach ($routes as $name => $path) {
        try {
            $url = route($name, ['id' => 1]);
            echo "   Route '{$name}': {$url}\n";
        } catch (Exception $e) {
            echo "   Route '{$name}': ERROR - {$e->getMessage()}\n";
        }
    }

    echo "\n=== Debug Summary ===\n";
    echo "The issue with email notifications not showing content is likely due to:\n";
    echo "1. Wrong notification table being used in the code\n";
    echo "2. Missing or incorrect notification model\n";
    echo "3. Database schema mismatch\n";
    echo "4. Route or controller issues\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
