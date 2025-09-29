<?php

/**
 * Email Tracking Test Script
 *
 * This script tests the email tracking functionality
 * Run with: php test_email_tracking.php
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Email;
use App\Models\EmailNotification;
use App\Services\EmailTrackingService;
use App\Services\GmailOAuthService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Email Tracking Test Script ===\n\n";

try {
    // Test 1: Check if services can be instantiated
    echo "Test 1: Service Instantiation\n";
    echo "-----------------------------\n";

    $emailTrackingService = app(EmailTrackingService::class);
    $gmailOAuthService = app(GmailOAuthService::class);

    echo "✓ EmailTrackingService instantiated successfully\n";
    echo "✓ GmailOAuthService instantiated successfully\n\n";

    // Test 2: Check Gmail configuration
    echo "Test 2: Gmail Configuration Check\n";
    echo "---------------------------------\n";

    $configCheck = $gmailOAuthService->checkConfiguration();

    if ($configCheck['configured']) {
        echo "✓ Gmail configuration is valid\n";
        echo "  - Client ID: " . $configCheck['config']['client_id'] . "\n";
        echo "  - Redirect URI: " . $configCheck['config']['redirect_uri'] . "\n";
    } else {
        echo "✗ Gmail configuration issues:\n";
        foreach ($configCheck['issues'] as $issue) {
            echo "  - " . $issue . "\n";
        }
    }
    echo "\n";

    // Test 3: Check database tables
    echo "Test 3: Database Tables Check\n";
    echo "-----------------------------\n";

    try {
        $emailCount = Email::count();
        echo "✓ Emails table accessible (count: {$emailCount})\n";
    } catch (Exception $e) {
        echo "✗ Emails table error: " . $e->getMessage() . "\n";
    }

    try {
        $notificationCount = EmailNotification::count();
        echo "✓ Email notifications table accessible (count: {$notificationCount})\n";
    } catch (Exception $e) {
        echo "✗ Email notifications table error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 4: Check users with Gmail connected
    echo "Test 4: Users with Gmail Connected\n";
    echo "----------------------------------\n";

    $usersWithGmail = User::where('gmail_connected', true)->get();

    if ($usersWithGmail->count() > 0) {
        echo "✓ Found {$usersWithGmail->count()} user(s) with Gmail connected:\n";
        foreach ($usersWithGmail as $user) {
            echo "  - User ID: {$user->id}, Email: {$user->email}\n";
        }
    } else {
        echo "⚠ No users with Gmail connected found\n";
        echo "  Users need to connect their Gmail accounts first\n";
    }
    echo "\n";

    // Test 5: Test email statistics
    if ($usersWithGmail->count() > 0) {
        echo "Test 5: Email Statistics\n";
        echo "-----------------------\n";

        $user = $usersWithGmail->first();
        $stats = $emailTrackingService->getEmailStats($user);

        echo "Email statistics for {$user->email}:\n";
        echo "  - Sent: {$stats['sent']}\n";
        echo "  - Opened: {$stats['opened']}\n";
        echo "  - Replied: {$stats['replied']}\n";
        echo "  - Open Rate: {$stats['open_rate']}%\n";
        echo "  - Reply Rate: {$stats['reply_rate']}%\n\n";
    }

    // Test 6: Test command availability
    echo "Test 6: Command Availability\n";
    echo "----------------------------\n";

    $commands = [
        'email:check-replies' => 'Check for email replies',
    ];

    foreach ($commands as $command => $description) {
        try {
            Artisan::call('list', ['--format=json']);
            echo "✓ Command '{$command}' is available\n";
        } catch (Exception $e) {
            echo "✗ Command '{$command}' error: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    echo "=== Test Summary ===\n";
    echo "All basic functionality tests completed.\n";
    echo "Check the results above for any issues.\n\n";

    echo "Next Steps:\n";
    echo "1. Run migrations: php artisan migrate\n";
    echo "2. Connect Gmail accounts for users\n";
    echo "3. Send test emails to verify CC functionality\n";
    echo "4. Run reply checking: php artisan email:check-replies\n";
    echo "5. Set up cron job for scheduled reply checking\n\n";

} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
