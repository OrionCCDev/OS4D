<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Email;

echo "=== Email Show Access Test ===\n\n";

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

    // Get the first email
    $email = Email::first();
    if (!$email) {
        echo "ERROR: No emails found\n";
        exit(1);
    }

    echo "Testing with email ID: {$email->id}\n";
    echo "Email subject: {$email->subject}\n";
    echo "Email from: {$email->from_email}\n";
    echo "Email body length: " . strlen($email->body ?? '') . " characters\n\n";

    // Test the controller logic
    echo "Testing EmailFetchController::show logic...\n";

    if (!$user->isManager()) {
        echo "❌ ACCESS DENIED: Only managers can view emails\n";
        echo "This is why you can't see email content when clicking notifications!\n\n";

        echo "SOLUTION OPTIONS:\n";
        echo "1. Make the user a manager (change role to 'admin' or 'manager')\n";
        echo "2. Modify the controller to allow all users to view emails\n";
        echo "3. Create a separate route for non-managers to view emails\n";

    } else {
        echo "✅ ACCESS GRANTED: User can view emails\n";

        // Test if email body exists
        if (empty($email->body)) {
            echo "⚠️  WARNING: Email body is empty!\n";
        } else {
            echo "✅ Email body exists and has content\n";
        }
    }

    // Check if there are any notifications for this user
    $notifications = DB::table('notifications')
        ->where('user_id', $user->id)
        ->whereNotNull('email_id')
        ->count();

    echo "\nUser has {$notifications} email notifications\n";

    if ($notifications > 0) {
        $notification = DB::table('notifications')
            ->where('user_id', $user->id)
            ->whereNotNull('email_id')
            ->first();

        echo "Sample notification email_id: {$notification->email_id}\n";

        $notificationEmail = Email::find($notification->email_id);
        if ($notificationEmail) {
            echo "✅ Notification email exists in database\n";
        } else {
            echo "❌ Notification email NOT found in database\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
