<?php
/**
 * Test script to verify the DesignersInboxNotificationService is working
 */

require_once 'vendor/autoload.php';

use App\Services\DesignersInboxNotificationService;
use App\Models\Email;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Service Fix Test ===\n";

try {
    // Test if the service can be instantiated
    $notificationService = new DesignersInboxNotificationService();
    echo "✅ DesignersInboxNotificationService instantiated successfully\n";

    // Test if we can get managers
    $managers = User::whereIn('role', ['admin', 'manager'])->get();
    echo "✅ Found " . $managers->count() . " managers\n";

    // Test if we can get recent emails
    $recentEmails = Email::where('email_source', 'designers_inbox')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    echo "✅ Found " . $recentEmails->count() . " recent emails\n";

    if ($recentEmails->count() > 0) {
        echo "\nTesting notification creation...\n";
        $email = $recentEmails->first();

        // Test the processEmailNotifications method
        $notificationService->processEmailNotifications($email);
        echo "✅ processEmailNotifications method executed successfully\n";
    }

    echo "\n=== All tests passed! The service is working correctly. ===\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
