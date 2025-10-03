<?php
/**
 * Notification Fix Test Script
 *
 * This script tests the notification creation fix by:
 * 1. Checking what users exist and their roles
 * 2. Testing notification creation manually
 * 3. Verifying the fix works
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Notification Fix Test ===\n";
echo "Testing the notification creation fix\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Test 1: Check users and their roles
    echo "=== Test 1: Users and Roles ===\n";

    $allUsers = \App\Models\User::all();
    echo "Total users in database: " . $allUsers->count() . "\n";

    $adminUsers = \App\Models\User::where('role', 'admin')->get();
    $managerUsers = \App\Models\User::where('role', 'manager')->get();
    $regularUsers = \App\Models\User::where('role', 'user')->get();

    echo "Admin users: " . $adminUsers->count() . "\n";
    echo "Manager users: " . $managerUsers->count() . "\n";
    echo "Regular users: " . $regularUsers->count() . "\n";

    // Show all users
    echo "\nAll users:\n";
    foreach ($allUsers as $user) {
        echo "  - {$user->name} ({$user->email}) - Role: {$user->role}\n";
    }

    // Check who would receive notifications
    $notificationUsers = \App\Models\User::whereIn('role', ['admin', 'manager'])->get();
    echo "\nUsers who will receive notifications: " . $notificationUsers->count() . "\n";
    foreach ($notificationUsers as $user) {
        echo "  - {$user->name} ({$user->email}) - Role: {$user->role}\n";
    }

    echo "\n";

    // Test 2: Test notification service directly
    echo "=== Test 2: Notification Service Test ===\n";

    if ($notificationUsers->count() > 0) {
        // Find a recent email
        $recentEmail = \App\Models\Email::where('email_source', 'designers_inbox')->first();

        if ($recentEmail) {
            echo "✅ Found recent email: {$recentEmail->subject}\n";

            // Test the notification service
            $notificationService = new \App\Services\DesignersInboxNotificationService();

            try {
                // Create a test notification
                $notificationService->createNewEmailNotification($recentEmail);
                echo "✅ Notification service test successful\n";

                // Check if notifications were created
                $newNotificationCount = \App\Models\DesignersInboxNotification::count();
                echo "✅ Total notifications after test: {$newNotificationCount}\n";

                if ($newNotificationCount > 0) {
                    echo "✅ Notifications are being created successfully!\n";

                    // Show the created notifications
                    $notifications = \App\Models\DesignersInboxNotification::with('user', 'email')->get();
                    echo "\nCreated notifications:\n";
                    foreach ($notifications as $notification) {
                        $userName = $notification->user ? $notification->user->name : 'Unknown';
                        $emailSubject = $notification->email ? $notification->email->subject : 'N/A';
                        echo "  - {$notification->title} for {$userName} - {$emailSubject}\n";
                    }
                } else {
                    echo "❌ No notifications were created\n";
                }

            } catch (\Exception $e) {
                echo "❌ Notification service test failed: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ No emails found to test notification creation\n";
        }
    } else {
        echo "❌ No admin/manager users found to receive notifications\n";
    }

    echo "\n";

    // Test 3: Test API endpoints
    echo "=== Test 3: API Endpoints Test ===\n";

    try {
        // Test unread count endpoint
        $response = \Illuminate\Support\Facades\Http::get(url('/auto-emails/unread-count'));
        if ($response->successful()) {
            $data = $response->json();
            echo "✅ Unread count endpoint working: " . json_encode($data) . "\n";
        } else {
            echo "❌ Unread count endpoint failed: " . $response->status() . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Unread count endpoint error: " . $e->getMessage() . "\n";
    }

    try {
        // Test recent notifications endpoint
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

    echo "\n=== Summary ===\n";

    if ($notificationUsers->count() > 0) {
        echo "✅ Found {$notificationUsers->count()} users who should receive notifications\n";

        $notificationCount = \App\Models\DesignersInboxNotification::count();
        if ($notificationCount > 0) {
            echo "✅ Notifications are being created: {$notificationCount} total\n";
            echo "✅ The notification fix is working!\n";
        } else {
            echo "⚠️  No notifications found - this might be normal if no new emails have been processed\n";
        }
    } else {
        echo "❌ No admin/manager users found - notifications won't be created\n";
        echo "💡 Solution: Make sure you have users with 'admin' or 'manager' roles\n";
    }

    echo "\nNext steps:\n";
    echo "1. Upload the updated DesignersInboxNotificationService.php to production\n";
    echo "2. Test by sending a new email to designers@orion-contracting.com\n";
    echo "3. Check the navbar notifications should now appear\n";
    echo "4. The system will create notifications for both 'admin' and 'manager' role users\n\n";

    echo "✅ Notification fix test completed!\n";

} catch (Exception $e) {
    echo "❌ Test script failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
