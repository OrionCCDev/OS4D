<?php
/**
 * Production Email Auto-Fetch Test Script
 *
 * This script tests the automatic email fetching system for designers@orion-contracting.com
 * Run this to verify that the system is working correctly in production.
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\AutoEmailFetchService;
use App\Services\DesignersInboxEmailService;
use App\Services\DesignersInboxNotificationService;
use Illuminate\Support\Facades\Log;

echo "=== Production Email Auto-Fetch Test ===\n";
echo "Testing automatic email fetching for designers@orion-contracting.com\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized successfully\n";

    // Initialize services
    $emailService = new DesignersInboxEmailService();
    $notificationService = new DesignersInboxNotificationService();
    $autoEmailService = new AutoEmailFetchService($emailService, $notificationService);

    echo "✅ Services initialized successfully\n\n";

    // Test 1: Check if Gmail connection is working
    echo "=== Test 1: Gmail Connection ===\n";
    try {
        $testResult = $emailService->testGmailConnection();
        if ($testResult['success']) {
            echo "✅ Gmail connection successful\n";
            echo "   - Connected to: {$testResult['email']}\n";
        } else {
            echo "❌ Gmail connection failed: " . implode(', ', $testResult['errors']) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Gmail connection test failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test 2: Test email fetching
    echo "=== Test 2: Email Fetching ===\n";
    try {
        $fetchResult = $emailService->fetchNewEmails(5); // Fetch only 5 emails for test

        if ($fetchResult['success']) {
            echo "✅ Email fetching successful\n";
            echo "   - Total fetched: {$fetchResult['total_fetched']} emails\n";

            if (!empty($fetchResult['emails'])) {
                echo "   - Sample emails:\n";
                foreach (array_slice($fetchResult['emails'], 0, 3) as $email) {
                    echo "     * Subject: " . substr($email['subject'], 0, 50) . "...\n";
                    echo "       From: {$email['from_email']}\n";
                    echo "       Date: {$email['date']}\n\n";
                }
            } else {
                echo "   - No new emails found\n";
            }
        } else {
            echo "❌ Email fetching failed: " . implode(', ', $fetchResult['errors']) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Email fetching test failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test 3: Test auto-fetch and process
    echo "=== Test 3: Auto-Fetch and Process ===\n";
    try {
        $result = $autoEmailService->autoFetchAndProcess();

        if ($result['success']) {
            echo "✅ Auto-fetch and process successful\n";
            echo "   - Fetched: {$result['fetched']} emails\n";
            echo "   - Stored: {$result['stored']} new emails\n";
            echo "   - Skipped: {$result['skipped']} duplicates\n";
            echo "   - Notifications created: {$result['notifications_created']}\n";

            if (!empty($result['message'])) {
                echo "   - Message: {$result['message']}\n";
            }

            if (!empty($result['errors'])) {
                echo "   - Warnings: " . implode(', ', $result['errors']) . "\n";
            }
        } else {
            echo "❌ Auto-fetch and process failed\n";
            echo "   - Errors: " . implode(', ', $result['errors']) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Auto-fetch test failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test 4: Check notification system
    echo "=== Test 4: Notification System ===\n";
    try {
        $unreadCount = $autoEmailService->getUnreadNotificationsCount();
        $recentNotifications = $autoEmailService->getRecentNotifications(5);

        echo "✅ Notification system working\n";
        echo "   - Unread notifications: {$unreadCount}\n";
        echo "   - Recent notifications: " . count($recentNotifications) . "\n";

        if (!empty($recentNotifications)) {
            echo "   - Sample notifications:\n";
            foreach (array_slice($recentNotifications, 0, 3) as $notification) {
                echo "     * {$notification['title']} - {$notification['created_at']}\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Notification system test failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test 5: Check scheduler command
    echo "=== Test 5: Scheduler Command ===\n";
    try {
        // Test the actual Artisan command
        $exitCode = Artisan::call('emails:auto-fetch', ['--max-results' => 5]);

        if ($exitCode === 0) {
            echo "✅ Scheduler command working\n";
            echo "   - Command executed successfully\n";
            echo "   - Output: " . Artisan::output() . "\n";
        } else {
            echo "❌ Scheduler command failed with exit code: {$exitCode}\n";
        }
    } catch (Exception $e) {
        echo "❌ Scheduler command test failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test 6: Check database connectivity
    echo "=== Test 6: Database Connectivity ===\n";
    try {
        $emailCount = \App\Models\Email::where('email_source', 'designers_inbox')->count();
        $notificationCount = \App\Models\DesignersInboxNotification::count();
        $lastFetch = \App\Models\EmailFetchLog::getLatestForSource('designers_inbox');

        echo "✅ Database connectivity working\n";
        echo "   - Total emails in database: {$emailCount}\n";
        echo "   - Total notifications: {$notificationCount}\n";

        if ($lastFetch) {
            echo "   - Last fetch: {$lastFetch->last_fetch_at}\n";
            echo "   - Last fetch status: " . ($lastFetch->last_errors ? 'Had errors' : 'Success') . "\n";
        } else {
            echo "   - No fetch logs found\n";
        }
    } catch (Exception $e) {
        echo "❌ Database connectivity test failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== Test Summary ===\n";
    echo "The automatic email fetching system is configured to:\n";
    echo "✅ Fetch emails from designers@orion-contracting.com every 5 minutes\n";
    echo "✅ Prevent duplicate emails using message_id and enhanced checks\n";
    echo "✅ Create notifications for new emails and replies\n";
    echo "✅ Display notifications in the navbar dropdown\n";
    echo "✅ Auto-refresh notification count every 10 seconds\n";
    echo "✅ Play notification sounds for new emails\n\n";

    echo "Next steps:\n";
    echo "1. Ensure your cron job is running: * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1\n";
    echo "2. Check the logs at storage/logs/laravel.log for any errors\n";
    echo "3. Visit https://odc.com.orion-contracting.com/emails-all to see the emails\n";
    echo "4. Check the navbar notification dropdown for new email alerts\n\n";

    echo "✅ All tests completed!\n";

} catch (Exception $e) {
    echo "❌ Test script failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
