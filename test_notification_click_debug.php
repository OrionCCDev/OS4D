<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Email;

echo "=== Notification Click Debug Test ===\n\n";

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

    // Check all notification tables for this user
    echo "Checking notifications for user ID: {$user->id}\n\n";

    // 1. Laravel notifications table
    echo "1. Laravel notifications table:\n";
    try {
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\\Models\\User')
            ->get();

        echo "   Count: " . $notifications->count() . "\n";

        if ($notifications->count() > 0) {
            $notification = $notifications->first();
            echo "   Sample notification:\n";
            echo "   - ID: {$notification->id}\n";
            echo "   - Type: {$notification->type}\n";
            echo "   - Read at: " . ($notification->read_at ?? 'NULL') . "\n";
            echo "   - Data: " . substr($notification->data, 0, 200) . "...\n";
        }
    } catch (Exception $e) {
        echo "   ERROR: {$e->getMessage()}\n";
    }

    // 2. Unified notifications table
    echo "\n2. Unified notifications table:\n";
    try {
        $notifications = DB::table('unified_notifications')
            ->where('user_id', $user->id)
            ->get();

        echo "   Count: " . $notifications->count() . "\n";

        if ($notifications->count() > 0) {
            $notification = $notifications->first();
            echo "   Sample notification:\n";
            echo "   - ID: {$notification->id}\n";
            echo "   - Title: {$notification->title}\n";
            echo "   - Message: " . substr($notification->message, 0, 100) . "...\n";
            echo "   - Email ID: " . ($notification->email_id ?? 'NULL') . "\n";
            echo "   - Is Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n";
        }
    } catch (Exception $e) {
        echo "   ERROR: {$e->getMessage()}\n";
    }

    // 3. Designers inbox notifications table
    echo "\n3. Designers inbox notifications table:\n";
    try {
        $notifications = DB::table('designers_inbox_notifications')
            ->where('user_id', $user->id)
            ->get();

        echo "   Count: " . $notifications->count() . "\n";

        if ($notifications->count() > 0) {
            $notification = $notifications->first();
            echo "   Sample notification:\n";
            echo "   - ID: {$notification->id}\n";
            echo "   - Title: {$notification->title}\n";
            echo "   - Message: " . substr($notification->message, 0, 100) . "...\n";
            echo "   - Email ID: " . ($notification->email_id ?? 'NULL') . "\n";
            echo "   - Read at: " . ($notification->read_at ?? 'NULL') . "\n";

            // Check if the email exists
            if ($notification->email_id) {
                $email = Email::find($notification->email_id);
                if ($email) {
                    echo "   - Associated email exists: Yes\n";
                    echo "   - Email subject: {$email->subject}\n";
                    echo "   - Email from: {$email->from_email}\n";
                } else {
                    echo "   - Associated email exists: No (This is the problem!)\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "   ERROR: {$e->getMessage()}\n";
    }

    // 4. Test email access
    echo "\n4. Testing email access:\n";
    $emails = Email::limit(3)->get();
    echo "   Available emails: " . $emails->count() . "\n";

    foreach ($emails as $email) {
        echo "   Email ID {$email->id}: {$email->subject}\n";
        echo "   - From: {$email->from_email}\n";
        echo "   - Body length: " . strlen($email->body ?? '') . " characters\n";
        echo "   - Status: {$email->status}\n";
    }

    // 5. Test route generation
    echo "\n5. Testing route generation:\n";
    try {
        $email = Email::first();
        if ($email) {
            $url = route('emails.show', $email->id);
            echo "   Email show URL: {$url}\n";
        }
    } catch (Exception $e) {
        echo "   Route generation ERROR: {$e->getMessage()}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
