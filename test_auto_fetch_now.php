<?php
/**
 * Quick test script to manually trigger auto-fetch
 * Run this to test the system immediately
 */

require_once 'vendor/autoload.php';

use App\Services\AutoEmailFetchService;
use App\Services\DesignersInboxEmailService;
use App\Services\DesignersInboxNotificationService;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Manual Auto-Fetch Test ===\n";
echo "Testing auto-fetch system immediately...\n\n";

try {
    // Initialize services
    $notificationService = new DesignersInboxNotificationService();
    $emailService = new DesignersInboxEmailService($notificationService);
    $autoEmailService = new AutoEmailFetchService($emailService, $notificationService);

    echo "1. Running auto-fetch...\n";
    $result = $autoEmailService->autoFetchAndProcess();

    if ($result['success']) {
        echo "✅ Auto-fetch successful!\n";
        echo "   - Fetched: {$result['fetched']} emails\n";
        echo "   - Stored: {$result['stored']} new emails\n";
        echo "   - Skipped: {$result['skipped']} duplicates\n";
        echo "   - Notifications created: {$result['notifications_created']}\n";
        echo "   - Message: {$result['message']}\n";
    } else {
        echo "❌ Auto-fetch failed!\n";
        echo "   Errors: " . implode(', ', $result['errors']) . "\n";
    }

    echo "\n2. Checking notification count...\n";
    $unreadCount = $autoEmailService->getUnreadNotificationsCount();
    echo "   Unread notifications: {$unreadCount}\n";

    echo "\n=== Test completed! ===\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
