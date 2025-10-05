<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralEmail;
use App\Models\User;

echo "=== Send Email Test ===\n\n";

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

    echo "Testing with user: {$user->name} ({$user->email})\n\n";

    // Test 1: Try to send email with a simple test
    echo "1. Testing email sending...\n";

    $testEmail = new GeneralEmail(
        'Test Email from System',
        'This is a test email to verify the email system is working correctly.',
        $user,
        ['test@example.com']
    );

    try {
        // Send the email
        Mail::to('test@example.com')
            ->cc('engineering@orion-contracting.com')
            ->send($testEmail);

        echo "   ✅ Email sent successfully!\n";
        echo "   Check your email inbox for the test email.\n";

    } catch (Exception $e) {
        echo "   ❌ Email sending failed: " . $e->getMessage() . "\n";
        echo "   Error details: " . $e->getTraceAsString() . "\n";
    }

    echo "\n2. Testing with real email address...\n";

    // Test 2: Try to send to a real email address
    try {
        $realEmail = new GeneralEmail(
            'System Test Email',
            'This is a test email from the Orion Contracting system to verify email functionality.',
            $user,
            [$user->email] // Send to the user's own email
        );

        Mail::to($user->email)
            ->cc('engineering@orion-contracting.com')
            ->send($realEmail);

        echo "   ✅ Test email sent to {$user->email}\n";
        echo "   Check your inbox for the test email.\n";

    } catch (Exception $e) {
        echo "   ❌ Real email sending failed: " . $e->getMessage() . "\n";
    }

    echo "\n3. Checking mail logs...\n";

    // Check if there are any mail logs
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $mailLogs = substr_count($logContent, 'Mail');
        echo "   Mail-related log entries: {$mailLogs}\n";

        if ($mailLogs > 0) {
            echo "   ✅ Mail logs found - check storage/logs/laravel.log for details\n";
        }
    } else {
        echo "   ⚠️  No log file found\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
