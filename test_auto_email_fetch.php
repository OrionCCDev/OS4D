<?php
/**
 * Test script for automatic email fetching functionality
 * Run this script to test the new auto-email fetch system
 */

require_once 'vendor/autoload.php';

use App\Services\AutoEmailFetchService;
use App\Services\DesignersInboxEmailService;
use App\Services\DesignersInboxNotificationService;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Auto Email Fetch Test ===\n";
echo "Testing automatic email fetching and notification system...\n\n";

try {
    // Initialize services
    $notificationService = new DesignersInboxNotificationService();
    $emailService = new DesignersInboxEmailService($notificationService);
    $autoEmailService = new AutoEmailFetchService($emailService, $notificationService);

    echo "1. Testing auto-fetch service...\n";
    $result = $autoEmailService->autoFetchAndProcess();

    if ($result['success']) {
        echo "✅ Auto-fetch successful!\n";
        echo "   - Fetched: {$result['fetched']} emails\n";
        echo "   - Stored: {$result['stored']} new emails\n";
        echo "   - Skipped: {$result['skipped']} duplicates\n";
        echo "   - Notifications created: {$result['notifications_created']}\n";
    } else {
        echo "❌ Auto-fetch failed!\n";
        echo "   Errors: " . implode(', ', $result['errors']) . "\n";
    }

    echo "\n2. Testing notification count...\n";
    $unreadCount = $autoEmailService->getUnreadNotificationsCount();
    echo "   Unread notifications: {$unreadCount}\n";

    echo "\n3. Testing recent notifications...\n";
    $recentNotifications = $autoEmailService->getRecentNotifications(5);
    echo "   Recent notifications: " . count($recentNotifications) . "\n";

    echo "\n4. Testing fetch statistics...\n";
    $stats = $autoEmailService->getFetchStatistics();
    echo "   Total emails: " . ($stats['total_emails'] ?? 0) . "\n";
    echo "   Unread emails: " . ($stats['unread_emails'] ?? 0) . "\n";
    echo "   Unread notifications: " . ($stats['unread_notifications'] ?? 0) . "\n";
    echo "   Last fetch: " . ($stats['last_fetch_at'] ?? 'Never') . "\n";

    echo "\n=== Test completed successfully! ===\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
