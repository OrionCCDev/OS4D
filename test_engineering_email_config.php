<?php
/**
 * Test Engineering Email Configuration
 *
 * This script tests the new engineering@orion-contracting.com email configuration
 * Run this to verify that all email settings are working correctly.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Engineering Email Configuration Test ===\n";
echo "Testing engineering@orion-contracting.com email setup\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Test 1: Check IMAP Configuration
    echo "\n--- IMAP Configuration Test ---\n";
    $imapHost = config('mail.imap.host');
    $imapPort = config('mail.imap.port');
    $imapUsername = config('mail.imap.username');
    $imapPassword = config('mail.imap.password');
    $imapFolder = config('mail.imap.folder');

    echo "IMAP Host: {$imapHost}\n";
    echo "IMAP Port: {$imapPort}\n";
    echo "IMAP Username: {$imapUsername}\n";
    echo "IMAP Password: " . ($imapPassword ? 'SET' : 'NOT SET') . "\n";
    echo "IMAP Folder: {$imapFolder}\n";

    if ($imapUsername === 'engineering@orion-contracting.com') {
        echo "✅ IMAP username correctly set to engineering@orion-contracting.com\n";
    } else {
        echo "❌ IMAP username is not set to engineering@orion-contracting.com\n";
    }

    // Test 2: Check SMTP Configuration
    echo "\n--- SMTP Configuration Test ---\n";
    $smtpHost = config('mail.host');
    $smtpPort = config('mail.port');
    $smtpUsername = config('mail.username');
    $smtpPassword = config('mail.password');
    $smtpFromAddress = config('mail.from.address');

    echo "SMTP Host: {$smtpHost}\n";
    echo "SMTP Port: {$smtpPort}\n";
    echo "SMTP Username: {$smtpUsername}\n";
    echo "SMTP Password: " . ($smtpPassword ? 'SET' : 'NOT SET') . "\n";
    echo "SMTP From Address: {$smtpFromAddress}\n";

    if ($smtpUsername === 'engineering@orion-contracting.com') {
        echo "✅ SMTP username correctly set to engineering@orion-contracting.com\n";
    } else {
        echo "❌ SMTP username is not set to engineering@orion-contracting.com\n";
    }

    // Test 3: Test IMAP Connection
    echo "\n--- IMAP Connection Test ---\n";
    try {
        $host = $imapHost;
        $port = $imapPort;
        $username = $imapUsername;
        $password = $imapPassword;
        $folder = $imapFolder;

        $connectionString = '{' . $host . ':' . $port . '/imap/ssl}' . $folder;
        echo "Connection String: {$connectionString}\n";

        $connection = imap_open($connectionString, $username, $password);
        if ($connection) {
            echo "✅ IMAP connection successful to engineering@orion-contracting.com\n";
            $messageCount = imap_num_msg($connection);
            echo "Message count: {$messageCount}\n";
            imap_close($connection);
        } else {
            echo "❌ IMAP connection failed: " . imap_last_error() . "\n";
        }
    } catch (Exception $e) {
        echo "❌ IMAP connection error: " . $e->getMessage() . "\n";
    }

    // Test 4: Test Email Fetch Command
    echo "\n--- Email Fetch Command Test ---\n";
    try {
        $exitCode = 0;
        $output = [];
        exec('php artisan emails:new-fetch --max-results=1 2>&1', $output, $exitCode);

        if ($exitCode === 0) {
            echo "✅ Email fetch command executed successfully\n";
            echo "Output: " . implode("\n", $output) . "\n";
        } else {
            echo "❌ Email fetch command failed with exit code: {$exitCode}\n";
            echo "Output: " . implode("\n", $output) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Email fetch command error: " . $e->getMessage() . "\n";
    }

    // Test 5: Check Database for Email Records
    echo "\n--- Database Email Records Test ---\n";
    try {
        $emailCount = \App\Models\Email::count();
        $recentEmails = \App\Models\Email::where('created_at', '>=', now()->subHours(24))->count();

        echo "Total emails in database: {$emailCount}\n";
        echo "Recent emails (last 24h): {$recentEmails}\n";

        if ($emailCount > 0) {
            echo "✅ Database contains email records\n";
        } else {
            echo "ℹ️  No email records found in database\n";
        }
    } catch (Exception $e) {
        echo "❌ Database query error: " . $e->getMessage() . "\n";
    }

    echo "\n=== Test Summary ===\n";
    echo "The engineering@orion-contracting.com email configuration has been tested.\n";
    echo "Please check the results above to ensure everything is working correctly.\n\n";

    echo "Next steps:\n";
    echo "1. Update your .env file with the new engineering@orion-contracting.com credentials\n";
    echo "2. Test sending an email to engineering@orion-contracting.com\n";
    echo "3. Check if the email is fetched and notifications are created\n";
    echo "4. Verify the email appears in the dashboard\n\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
