<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Email;

echo "=== Notification Fix Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Get the first user
    $user = User::first();
    if (!$user) {
        echo "ERROR: No users found\n";
        exit(1);
    }

    echo "Testing with user: {$user->name} ({$user->email})\n";
    echo "User role: {$user->role}\n";
    echo "Is manager: " . ($user->isManager() ? 'Yes' : 'No') . "\n\n";

    // Check unified notifications with email_id
    echo "Checking unified notifications with email_id...\n";
    $notifications = DB::table('unified_notifications')
        ->where('user_id', $user->id)
        ->whereNotNull('email_id')
        ->where('category', 'email')
        ->get();

    echo "Found {$notifications->count()} email notifications\n\n";

    if ($notifications->count() > 0) {
        $notification = $notifications->first();
        echo "Sample notification:\n";
        echo "- ID: {$notification->id}\n";
        echo "- Title: {$notification->title}\n";
        echo "- Message: " . substr($notification->message, 0, 100) . "...\n";
        echo "- Email ID: {$notification->email_id}\n";
        echo "- Is Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n\n";

        // Check if the email exists
        $email = Email::find($notification->email_id);
        if ($email) {
            echo "✅ Associated email exists:\n";
            echo "- Email ID: {$email->id}\n";
            echo "- Subject: {$email->subject}\n";
            echo "- From: {$email->from_email}\n";
            echo "- Body length: " . strlen($email->body ?? '') . " characters\n";
            echo "- Status: {$email->status}\n\n";

            // Test the route
            try {
                $url = route('emails.show', $email->id);
                echo "✅ Email show route works: {$url}\n";
            } catch (Exception $e) {
                echo "❌ Email show route error: {$e->getMessage()}\n";
            }
        } else {
            echo "❌ Associated email does NOT exist (This is the problem!)\n";
        }
    }

    // Test all email notifications
    echo "\nTesting all email notifications...\n";
    $allEmailNotifications = DB::table('unified_notifications')
        ->where('user_id', $user->id)
        ->where('category', 'email')
        ->get();

    echo "Total email notifications: {$allEmailNotifications->count()}\n";

    $validNotifications = 0;
    $invalidNotifications = 0;

    foreach ($allEmailNotifications as $notification) {
        if ($notification->email_id) {
            $email = Email::find($notification->email_id);
            if ($email) {
                $validNotifications++;
            } else {
                $invalidNotifications++;
                echo "❌ Notification ID {$notification->id} references non-existent email ID {$notification->email_id}\n";
            }
        }
    }

    echo "Valid notifications: {$validNotifications}\n";
    echo "Invalid notifications: {$invalidNotifications}\n";

    if ($invalidNotifications > 0) {
        echo "\n⚠️  WARNING: Some notifications reference non-existent emails!\n";
        echo "This could be why clicking notifications doesn't work.\n";
    } else {
        echo "\n✅ All email notifications have valid email references.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
