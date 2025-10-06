<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

echo "=== SMTP Delivery Debug Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // 1. Check detailed mail configuration
    echo "1. Detailed SMTP Configuration:\n";
    echo "   Driver: " . config('mail.default') . "\n";
    echo "   Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "   Port: " . config('mail.mailers.smtp.port') . "\n";
    echo "   Username: " . config('mail.mailers.smtp.username') . "\n";
    echo "   Password: " . (config('mail.mailers.smtp.password') ? 'SET' : 'NOT SET') . "\n";
    echo "   Encryption: " . (config('mail.mailers.smtp.encryption') ?: 'NONE') . "\n";
    echo "   From Address: " . config('mail.from.address') . "\n";
    echo "   From Name: " . config('mail.from.name') . "\n\n";

    // 2. Test SMTP connection manually
    echo "2. Testing SMTP Connection:\n";
    try {
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $username = config('mail.mailers.smtp.username');
        $password = config('mail.mailers.smtp.password');
        $encryption = config('mail.mailers.smtp.encryption');

        echo "   Connecting to {$host}:{$port}...\n";

        // Test basic connection
        $connection = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$connection) {
            echo "   ❌ Connection failed: {$errstr} ({$errno})\n";
        } else {
            echo "   ✅ Socket connection successful\n";
            fclose($connection);
        }

        // Test SMTP authentication
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $smtp = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        if ($smtp) {
            echo "   ✅ SMTP socket connection successful\n";

            // Read initial response
            $response = fgets($smtp, 1024);
            echo "   Server response: " . trim($response) . "\n";

            // Send EHLO command
            fwrite($smtp, "EHLO orion-contracting.com\r\n");
            $response = fgets($smtp, 1024);
            echo "   EHLO response: " . trim($response) . "\n";

            fclose($smtp);
        } else {
            echo "   ❌ SMTP connection failed: {$errstr} ({$errno})\n";
        }

    } catch (Exception $e) {
        echo "   ❌ SMTP test failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 3. Test with different mail drivers
    echo "3. Testing different mail drivers:\n";

    // Test with log driver
    echo "   Testing with LOG driver...\n";
    Config::set('mail.default', 'log');
    try {
        Mail::raw('Test email with LOG driver', function ($message) {
            $message->to('test@example.com')
                    ->subject('LOG Driver Test')
                    ->from('engineering@orion-contracting.com', 'Orion Contracting');
        });
        echo "   ✅ LOG driver works - check storage/logs/laravel.log\n";
    } catch (Exception $e) {
        echo "   ❌ LOG driver failed: " . $e->getMessage() . "\n";
    }

    // Test with array driver
    echo "   Testing with ARRAY driver...\n";
    Config::set('mail.default', 'array');
    try {
        Mail::raw('Test email with ARRAY driver', function ($message) {
            $message->to('test@example.com')
                    ->subject('ARRAY Driver Test')
                    ->from('engineering@orion-contracting.com', 'Orion Contracting');
        });
        echo "   ✅ ARRAY driver works\n";
    } catch (Exception $e) {
        echo "   ❌ ARRAY driver failed: " . $e->getMessage() . "\n";
    }

    // Reset to SMTP
    Config::set('mail.default', 'smtp');
    echo "\n";

    // 4. Test with verbose logging
    echo "4. Testing with verbose logging:\n";
    Config::set('mail.mailers.smtp.verify_peer', false);
    Config::set('mail.mailers.smtp.verify_peer_name', false);
    Config::set('mail.mailers.smtp.allow_self_signed', true);

    try {
        // Enable detailed logging
        Log::info('Starting SMTP test with verbose logging');

        Mail::raw('Test email with verbose logging', function ($message) {
            $message->to('test@example.com')
                    ->subject('Verbose Logging Test')
                    ->from('engineering@orion-contracting.com', 'Orion Contracting');
        });

        echo "   ✅ Email sent with verbose logging\n";
        echo "   📝 Check storage/logs/laravel.log for detailed SMTP logs\n";

    } catch (Exception $e) {
        echo "   ❌ Verbose logging test failed: " . $e->getMessage() . "\n";
        echo "   Error details: " . $e->getTraceAsString() . "\n";
    }
    echo "\n";

    // 5. Check recent logs for SMTP errors
    echo "5. Checking recent SMTP logs:\n";
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $recentLogs = substr($logContent, -5000);

        // Look for SMTP-related errors
        $smtpErrors = [];
        $lines = explode("\n", $recentLogs);
        foreach ($lines as $line) {
            if (stripos($line, 'smtp') !== false ||
                stripos($line, 'mail') !== false ||
                stripos($line, 'error') !== false ||
                stripos($line, 'failed') !== false) {
                $smtpErrors[] = trim($line);
            }
        }

        if (!empty($smtpErrors)) {
            echo "   Recent SMTP-related log entries:\n";
            foreach (array_slice($smtpErrors, -10) as $error) {
                echo "   " . $error . "\n";
            }
        } else {
            echo "   ⚠️  No recent SMTP logs found\n";
        }
    } else {
        echo "   ❌ No log file found\n";
    }
    echo "\n";

    // 6. Test with different encryption settings
    echo "6. Testing different encryption settings:\n";

    $encryptionOptions = ['', 'tls', 'ssl'];
    foreach ($encryptionOptions as $encryption) {
        echo "   Testing with encryption: " . ($encryption ?: 'NONE') . "\n";
        Config::set('mail.mailers.smtp.encryption', $encryption);

        try {
            Mail::raw("Test email with {$encryption} encryption", function ($message) use ($encryption) {
                $message->to('test@example.com')
                        ->subject("Encryption Test - {$encryption}")
                        ->from('engineering@orion-contracting.com', 'Orion Contracting');
            });
            echo "   ✅ Encryption '{$encryption}' works\n";
        } catch (Exception $e) {
            echo "   ❌ Encryption '{$encryption}' failed: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // 7. Check if emails are actually being queued
    echo "7. Checking email queue status:\n";
    try {
        $queueCount = \DB::table('jobs')->where('queue', 'default')->count();
        echo "   Jobs in queue: {$queueCount}\n";

        if ($queueCount > 0) {
            echo "   ⚠️  Emails might be queued and not processed\n";
            echo "   Run: php artisan queue:work\n";
        } else {
            echo "   ✅ No emails in queue\n";
        }
    } catch (Exception $e) {
        echo "   ⚠️  Could not check queue: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 8. Provide troubleshooting steps
    echo "=== TROUBLESHOOTING STEPS ===\n\n";
    echo "If emails are not being delivered:\n\n";
    echo "1. CHECK SMTP CREDENTIALS:\n";
    echo "   - Verify username and password are correct\n";
    echo "   - Check if account is locked or suspended\n";
    echo "   - Test login to webmail directly\n\n";

    echo "2. CHECK SMTP SERVER STATUS:\n";
    echo "   - Verify mail.orion-contracting.com is accessible\n";
    echo "   - Check if port 587 is open\n";
    echo "   - Test with different ports (25, 465, 587)\n\n";

    echo "3. CHECK EMAIL LIMITS:\n";
    echo "   - Verify account has sending limits\n";
    echo "   - Check if daily/hourly limits are exceeded\n";
    echo "   - Contact hosting provider about limits\n\n";

    echo "4. CHECK FIREWALL/SECURITY:\n";
    echo "   - Verify SMTP ports are not blocked\n";
    echo "   - Check if IP is whitelisted\n";
    echo "   - Test from different network\n\n";

    echo "5. CHECK EMAIL CONTENT:\n";
    echo "   - Verify email content is not triggering filters\n";
    echo "   - Test with simple text emails\n";
    echo "   - Check for attachment issues\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
