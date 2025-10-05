<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\GeneralEmail;
use App\Mail\SimpleTestEmail;
use App\Models\User;

echo "=== Email Delivery Issues Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = User::first();
    if (!$user) {
        echo "ERROR: No users found\n";
        exit(1);
    }

    echo "Testing with user: {$user->name} ({$user->email})\n\n";

    // 1. Test with simple email (no attachments)
    echo "1. Testing simple email (no attachments)...\n";
    try {
        $simpleEmail = new SimpleTestEmail(
            'Simple Test Email - No Attachments',
            'This is a simple test email without any attachments to test basic delivery.',
            $user
        );

        Mail::to('test@example.com')->send($simpleEmail);
        echo "   ✅ Simple email sent successfully\n";

    } catch (Exception $e) {
        echo "   ❌ Simple email failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 2. Test with GeneralEmail (with attachments)
    echo "2. Testing GeneralEmail (with attachments)...\n";
    try {
        $generalEmail = new GeneralEmail(
            'General Email Test - With Attachments',
            'This is a test email with logo attachment to test delivery with attachments.',
            $user,
            ['test@example.com']
        );

        Mail::to('test@example.com')
            ->cc('engineering@orion-contracting.com')
            ->send($generalEmail);

        echo "   ✅ GeneralEmail sent successfully\n";

    } catch (Exception $e) {
        echo "   ❌ GeneralEmail failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 3. Test with different recipient domains
    echo "3. Testing delivery to different email providers...\n";

    $testRecipients = [
        'Gmail' => 'test@gmail.com',
        'Yahoo' => 'test@yahoo.com',
        'Outlook' => 'test@outlook.com',
        'Hotmail' => 'test@hotmail.com'
    ];

    foreach ($testRecipients as $provider => $email) {
        echo "   Testing {$provider} ({$email})...\n";
        try {
            $testEmail = new SimpleTestEmail(
                "Test Email to {$provider}",
                "This is a test email to verify delivery to {$provider}.",
                $user
            );

            Mail::to($email)->send($testEmail);
            echo "   ✅ Email sent to {$provider}\n";

        } catch (Exception $e) {
            echo "   ❌ Failed to send to {$provider}: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // 4. Check mail configuration
    echo "4. Checking mail configuration...\n";
    echo "   SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "   SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
    echo "   SMTP Username: " . config('mail.mailers.smtp.username') . "\n";
    echo "   SMTP Password: " . (config('mail.mailers.smtp.password') ? 'SET' : 'NOT SET') . "\n";
    echo "   SMTP Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
    echo "   From Address: " . config('mail.from.address') . "\n";
    echo "   From Name: " . config('mail.from.name') . "\n\n";

    // 5. Check for common delivery issues
    echo "5. Checking for common delivery issues...\n";

    // Check if emails are being sent to spam
    echo "   ⚠️  IMPORTANT: Check the following:\n";
    echo "   1. Check SPAM/JUNK folders in recipient email accounts\n";
    echo "   2. Verify the sender domain (orion-contracting.com) is not blacklisted\n";
    echo "   3. Check if the recipient email addresses are valid\n";
    echo "   4. Verify SMTP credentials are correct\n";
    echo "   5. Check if the email server has sending limits\n\n";

    // 6. Test with a real email address
    echo "6. Testing with your real email address...\n";
    try {
        $realEmail = new SimpleTestEmail(
            'Real Email Test',
            'This is a test email sent to your real email address to verify delivery.',
            $user
        );

        Mail::to($user->email)->send($realEmail);
        echo "   ✅ Test email sent to {$user->email}\n";
        echo "   📧 Check your inbox (and spam folder) for this email\n";

    } catch (Exception $e) {
        echo "   ❌ Failed to send to real email: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 7. Check recent logs
    echo "7. Checking recent mail logs...\n";
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $recentLogs = substr($logContent, -3000);

        if (strpos($recentLogs, 'Mail') !== false) {
            echo "   ✅ Recent mail logs found\n";

            // Look for error messages
            if (strpos($recentLogs, 'error') !== false || strpos($recentLogs, 'failed') !== false) {
                echo "   ⚠️  Error messages found in logs:\n";
                $lines = explode("\n", $recentLogs);
                $errorLines = array_filter($lines, function($line) {
                    return stripos($line, 'error') !== false || stripos($line, 'failed') !== false;
                });

                foreach (array_slice($errorLines, -3) as $line) {
                    echo "     " . trim($line) . "\n";
                }
            } else {
                echo "   ✅ No error messages found in recent logs\n";
            }
        }
    }
    echo "\n";

    // 8. Provide troubleshooting steps
    echo "=== Troubleshooting Steps ===\n";
    echo "If emails are not being received:\n\n";
    echo "1. CHECK SPAM FOLDER:\n";
    echo "   - Look in the recipient's spam/junk folder\n";
    echo "   - Mark as 'Not Spam' if found there\n\n";

    echo "2. VERIFY EMAIL ADDRESSES:\n";
    echo "   - Make sure recipient email addresses are correct\n";
    echo "   - Test with a known working email address\n\n";

    echo "3. CHECK DOMAIN REPUTATION:\n";
    echo "   - Visit: https://mxtoolbox.com/blacklists.aspx\n";
    echo "   - Check if orion-contracting.com is blacklisted\n\n";

    echo "4. VERIFY SMTP SETTINGS:\n";
    echo "   - Confirm SMTP credentials are correct\n";
    echo "   - Check if the email server has sending limits\n\n";

    echo "5. TEST WITH DIFFERENT EMAIL PROVIDERS:\n";
    echo "   - Try sending to Gmail, Yahoo, Outlook\n";
    echo "   - Some providers may block certain domains\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
