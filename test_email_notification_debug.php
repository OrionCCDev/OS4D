<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Email;
use App\Models\Notification;

echo "=== Email Notification Debug Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // 1. Check current user and their role
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

    // 2. Check if there are any emails in the database
    echo "\n2. Checking emails in database...\n";
    $emailCount = Email::count();
    echo "   Total emails: {$emailCount}\n";

    if ($emailCount > 0) {
        $latestEmail = Email::latest()->first();
        echo "   Latest email ID: {$latestEmail->id}\n";
        echo "   Latest email subject: {$latestEmail->subject}\n";
        echo "   Latest email from: {$latestEmail->from_email}\n";
        echo "   Latest email body length: " . strlen($latestEmail->body ?? '') . " characters\n";
        echo "   Latest email status: {$latestEmail->status}\n";
    }

    // 3. Check notifications
    echo "\n3. Checking notifications...\n";
    $notificationCount = Notification::count();
    echo "   Total notifications: {$notificationCount}\n";

    if ($notificationCount > 0) {
        $latestNotification = Notification::latest()->first();
        echo "   Latest notification ID: {$latestNotification->id}\n";
        echo "   Latest notification message: {$latestNotification->message}\n";
        echo "   Latest notification email_id: " . ($latestNotification->email_id ?? 'NULL') . "\n";
        echo "   Latest notification is_read: " . ($latestNotification->is_read ? 'Yes' : 'No') . "\n";
    }

    // 4. Test email show access
    echo "\n4. Testing email show access...\n";
    if ($emailCount > 0) {
        $testEmail = Email::first();
        echo "   Testing access to email ID: {$testEmail->id}\n";

        // Simulate the controller logic
        if (!$user->isManager()) {
            echo "   ERROR: Access denied. Only managers can view emails.\n";
            echo "   This is why you can't see email content!\n";
        } else {
            echo "   SUCCESS: User has manager access to view emails.\n";
        }
    }

    // 5. Check routes
    echo "\n5. Checking email routes...\n";
    $routes = [
        'emails.show' => '/emails/{id}',
        'emails.index' => '/emails',
        'email-tracker.mark-read' => '/email-tracker/{id}/mark-read'
    ];

    foreach ($routes as $name => $path) {
        try {
            $url = route($name, ['id' => 1]);
            echo "   Route '{$name}': {$url}\n";
        } catch (Exception $e) {
            echo "   Route '{$name}': ERROR - {$e->getMessage()}\n";
        }
    }

    // 6. Check if user can access email notifications
    echo "\n6. Testing notification access...\n";
    if ($notificationCount > 0) {
        $userNotifications = Notification::where('user_id', $user->id)->count();
        echo "   User notifications: {$userNotifications}\n";

        if ($userNotifications > 0) {
            $userNotification = Notification::where('user_id', $user->id)->first();
            echo "   User notification email_id: " . ($userNotification->email_id ?? 'NULL') . "\n";

            if ($userNotification->email_id) {
                $email = Email::find($userNotification->email_id);
                if ($email) {
                    echo "   Associated email exists: Yes\n";
                    echo "   Associated email subject: {$email->subject}\n";
                } else {
                    echo "   Associated email exists: No (This could be the problem!)\n";
                }
            }
        }
    }

    echo "\n=== Debug Summary ===\n";
    echo "If you can't see email content when clicking notifications, the likely causes are:\n";
    echo "1. User is not a manager (only managers can view emails)\n";
    echo "2. Email ID in notification doesn't exist in emails table\n";
    echo "3. Route or controller issue\n";
    echo "4. Database connection issue\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
