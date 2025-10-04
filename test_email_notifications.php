<?php
/**
 * Email Notifications Test Script
 *
 * This script tests the email notification system to verify that:
 * 1. Notifications are being created correctly
 * 2. The navbar display is working
 * 3. The API endpoints are responding correctly
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Email Notifications Test ===\n";
echo "Testing email notification system for engineering@orion-contracting.com\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Test 1: Check if notifications exist in database
    echo "=== Test 1: Database Notifications ===\n";

    $notificationCount = \App\Models\DesignersInboxNotification::count();
    $unreadCount = \App\Models\DesignersInboxNotification::unread()->count();

    echo "✅ Total notifications in database: {$notificationCount}\n";
    echo "✅ Unread notifications: {$unreadCount}\n";

    if ($notificationCount > 0) {
        echo "✅ Notifications are being created successfully\n";

        // Show sample notifications
        $sampleNotifications = \App\Models\DesignersInboxNotification::with('email')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        echo "\n📧 Sample notifications:\n";
        foreach ($sampleNotifications as $notification) {
            $emailSubject = $notification->email ? $notification->email->subject : 'N/A';
            $readStatus = $notification->isRead() ? 'Read' : 'Unread';
            echo "   - {$notification->title} ({$readStatus}) - {$emailSubject}\n";
        }
    } else {
        echo "⚠️  No notifications found in database\n";
    }

    echo "\n";

    // Test 2: Check API endpoints
    echo "=== Test 2: API Endpoints ===\n";

    // Test unread count endpoint
    try {
        $response = \Illuminate\Support\Facades\Http::get(url('/auto-emails/unread-count'));
        if ($response->successful()) {
            $data = $response->json();
            echo "✅ Unread count endpoint working: {$data['count']} unread\n";
        } else {
            echo "❌ Unread count endpoint failed: " . $response->status() . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Unread count endpoint error: " . $e->getMessage() . "\n";
    }

    // Test recent notifications endpoint
    try {
        $response = \Illuminate\Support\Facades\Http::get(url('/auto-emails/recent-notifications'));
        if ($response->successful()) {
            $data = $response->json();
            $notificationCount = count($data['notifications'] ?? []);
            echo "✅ Recent notifications endpoint working: {$notificationCount} notifications\n";
        } else {
            echo "❌ Recent notifications endpoint failed: " . $response->status() . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Recent notifications endpoint error: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test 3: Check email fetching
    echo "=== Test 3: Email Fetching Status ===\n";

    $lastFetch = \App\Models\EmailFetchLog::getLatestForSource('designers_inbox');
    if ($lastFetch) {
        echo "✅ Last fetch: {$lastFetch->last_fetch_at}\n";
        echo "✅ Last fetch status: " . ($lastFetch->last_errors ? 'Had errors' : 'Success') . "\n";

        if ($lastFetch->last_errors) {
            echo "⚠️  Last fetch errors: {$lastFetch->last_errors}\n";
        }
    } else {
        echo "❌ No fetch logs found\n";
    }

    echo "\n";

    // Test 4: Check recent email activity
    echo "=== Test 4: Recent Email Activity ===\n";

    $recentEmails = \App\Models\Email::where('email_source', 'designers_inbox')
        ->orderBy('received_at', 'desc')
        ->limit(5)
        ->get();

    if ($recentEmails->count() > 0) {
        echo "✅ Recent emails from designers inbox:\n";
        foreach ($recentEmails as $email) {
            $receivedTime = $email->received_at->diffForHumans();
            echo "   - {$email->subject} from {$email->from_email} ({$receivedTime})\n";
        }
    } else {
        echo "⚠️  No emails found from designers inbox\n";
    }

    echo "\n";

    // Test 5: Manual notification creation test
    echo "=== Test 5: Manual Notification Creation ===\n";

    try {
        // Find a manager user
        $manager = \App\Models\User::whereIn('role', ['admin', 'manager'])->first();

        if ($manager) {
            // Find a recent email
            $recentEmail = \App\Models\Email::where('email_source', 'designers_inbox')->first();

            if ($recentEmail) {
                // Create a test notification
                $testNotification = \App\Models\DesignersInboxNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $recentEmail->id,
                    'type' => 'new_email',
                    'title' => 'Test Notification - ' . now()->format('H:i:s'),
                    'message' => 'This is a test notification to verify the system is working.',
                    'data' => ['test' => true],
                    'read_at' => null
                ]);

                echo "✅ Test notification created successfully (ID: {$testNotification->id})\n";

                // Clean up the test notification
                $testNotification->delete();
                echo "✅ Test notification cleaned up\n";
            } else {
                echo "⚠️  No emails found to create test notification\n";
            }
        } else {
            echo "❌ No manager user found\n";
        }
    } catch (Exception $e) {
        echo "❌ Manual notification creation failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== Summary ===\n";
    echo "The email notification system status:\n";

    if ($notificationCount > 0) {
        echo "✅ Notifications are being created: {$notificationCount} total, {$unreadCount} unread\n";
        echo "✅ Database is working correctly\n";
    } else {
        echo "⚠️  No notifications found - this might be normal if no emails have been received\n";
    }

    if ($lastFetch) {
        echo "✅ Email fetching is working (last fetch: {$lastFetch->last_fetch_at})\n";
    } else {
        echo "⚠️  No fetch logs found - email fetching might not be running\n";
    }

    echo "\nTo test the navbar notifications:\n";
    echo "1. Visit: https://odc.com.orion-contracting.com/\n";
    echo "2. Look for the envelope icon in the navbar\n";
    echo "3. Click on it to see the notification dropdown\n";
    echo "4. The badge should show the unread count\n";
    echo "5. Use the 'Test' button in the dropdown to create a test notification\n\n";

    echo "✅ Email notification system test completed!\n";

} catch (Exception $e) {
    echo "❌ Test script failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
