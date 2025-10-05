<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\GeneralEmail;
use App\Models\User;

echo "=== Email Delivery Debug Test ===\n\n";

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

    // 1. Check mail configuration in detail
    echo "1. Detailed mail configuration...\n";
    echo "   Driver: " . config('mail.default') . "\n";
    echo "   Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "   Port: " . config('mail.mailers.smtp.port') . "\n";
    echo "   Username: " . config('mail.mailers.smtp.username') . "\n";
    echo "   Password: " . (config('mail.mailers.smtp.password') ? '***SET***' : 'NOT SET') . "\n";
    echo "   Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
    echo "   From Address: " . config('mail.from.address') . "\n";
    echo "   From Name: " . config('mail.from.name') . "\n\n";

    // 2. Test SMTP connection
    echo "2. Testing SMTP connection...\n";
    try {
        $transport = new \Swift_SmtpTransport(
            config('mail.mailers.smtp.host'),
            config('mail.mailers.smtp.port'),
            config('mail.mailers.smtp.encryption')
        );
        $transport->setUsername(config('mail.mailers.smtp.username'));
        $transport->setPassword(config('mail.mailers.smtp.password'));

        $mailer = new \Swift_Mailer($transport);
        $mailer->getTransport()->start();
        echo "   ✅ SMTP connection successful\n";
        $mailer->getTransport()->stop();
    } catch (Exception $e) {
        echo "   ❌ SMTP connection failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 3. Test with different email providers
    echo "3. Testing email delivery to different providers...\n";

    $testEmails = [
        'gmail.com' => 'test@gmail.com',
        'yahoo.com' => 'test@yahoo.com',
        'outlook.com' => 'test@outlook.com',
        'hotmail.com' => 'test@hotmail.com'
    ];

    foreach ($testEmails as $provider => $email) {
        echo "   Testing {$provider}...\n";
        try {
            $testEmail = new GeneralEmail(
                'Test Email Delivery - ' . $provider,
                'This is a test email to verify delivery to ' . $provider . '.',
                $user,
                [$email]
            );

            Mail::to($email)
                ->cc('engineering@orion-contracting.com')
                ->send($testEmail);

            echo "   ✅ Email sent to {$email}\n";

        } catch (Exception $e) {
            echo "   ❌ Failed to send to {$email}: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // 4. Check recent mail logs for errors
    echo "4. Checking recent mail logs...\n";
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $recentLogs = substr($logContent, -5000); // Last 5000 characters

        // Look for mail-related errors
        if (strpos($recentLogs, 'Mail') !== false) {
            echo "   ✅ Mail logs found\n";

            // Extract mail-related lines
            $lines = explode("\n", $recentLogs);
            $mailLines = array_filter($lines, function($line) {
                return stripos($line, 'mail') !== false || stripos($line, 'smtp') !== false;
            });

            echo "   Recent mail-related log entries:\n";
            foreach (array_slice($mailLines, -5) as $line) {
                echo "   " . trim($line) . "\n";
            }
        } else {
            echo "   ⚠️  No recent mail logs found\n";
        }
    } else {
        echo "   ❌ No log file found\n";
    }
    echo "\n";

    // 5. Test with a simple email (no attachments)
    echo "5. Testing simple email (no attachments)...\n";
    try {
        // Create a simple email without attachments
        $simpleEmail = new \App\Mail\SimpleTestEmail(
            'Simple Test Email',
            'This is a simple test email without attachments.',
            $user
        );

        Mail::to('test@example.com')->send($simpleEmail);
        echo "   ✅ Simple email sent successfully\n";

    } catch (Exception $e) {
        echo "   ❌ Simple email failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 6. Check DNS and domain reputation
    echo "6. Checking domain and DNS...\n";
    $domain = 'orion-contracting.com';
    echo "   Domain: {$domain}\n";

    // Check if domain has proper DNS records
    $mxRecords = dns_get_record($domain, DNS_MX);
    if ($mxRecords) {
        echo "   ✅ MX records found:\n";
        foreach ($mxRecords as $mx) {
            echo "     - {$mx['target']} (Priority: {$mx['pri']})\n";
        }
    } else {
        echo "   ❌ No MX records found for {$domain}\n";
    }

    $spfRecord = dns_get_record($domain, DNS_TXT);
    $hasSpf = false;
    foreach ($spfRecord as $txt) {
        if (strpos($txt['txt'], 'v=spf1') !== false) {
            $hasSpf = true;
            echo "   ✅ SPF record found: {$txt['txt']}\n";
            break;
        }
    }
    if (!$hasSpf) {
        echo "   ⚠️  No SPF record found (may affect delivery)\n";
    }
    echo "\n";

    echo "=== Common Email Delivery Issues ===\n";
    echo "1. Emails going to spam folder\n";
    echo "2. Domain reputation issues\n";
    echo "3. Missing SPF/DKIM records\n";
    echo "4. SMTP authentication problems\n";
    echo "5. Email provider blocking\n";
    echo "6. Large attachments causing issues\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
