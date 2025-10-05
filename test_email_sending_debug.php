<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Mail\GeneralEmail;
use App\Models\User;

echo "=== Email Sending Debug Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // 1. Check mail configuration
    echo "1. Checking mail configuration...\n";
    echo "   Driver: " . config('mail.default') . "\n";
    echo "   Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "   Port: " . config('mail.mailers.smtp.port') . "\n";
    echo "   Username: " . config('mail.mailers.smtp.username') . "\n";
    echo "   Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
    echo "   From Address: " . config('mail.from.address') . "\n";
    echo "   From Name: " . config('mail.from.name') . "\n\n";

    // 2. Check if logo file exists
    echo "2. Checking logo file...\n";
    $logoPath = public_path('uploads/logo-blue.webp');
    echo "   Logo path: {$logoPath}\n";
    echo "   Logo exists: " . (file_exists($logoPath) ? 'Yes' : 'No') . "\n";

    if (file_exists($logoPath)) {
        echo "   Logo size: " . filesize($logoPath) . " bytes\n";
    } else {
        echo "   ❌ Logo file not found! This will cause email sending to fail.\n";
    }
    echo "\n";

    // 3. Test GeneralEmail class
    echo "3. Testing GeneralEmail class...\n";
    try {
        $user = User::first();
        if (!$user) {
            echo "   ❌ No users found in database\n";
            exit(1);
        }

        $testEmail = new GeneralEmail(
            'Test Email Subject',
            'This is a test email body content.',
            $user,
            ['test@example.com']
        );

        echo "   ✅ GeneralEmail class instantiated successfully\n";
        echo "   Subject: " . $testEmail->subject . "\n";
        echo "   Sender: " . $testEmail->sender->name . "\n";
        echo "   Recipients: " . implode(', ', $testEmail->toEmails) . "\n";

    } catch (Exception $e) {
        echo "   ❌ GeneralEmail class error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 4. Test email template rendering
    echo "4. Testing email template rendering...\n";
    try {
        $user = User::first();
        $testEmail = new GeneralEmail(
            'Test Email Subject',
            'This is a test email body content.',
            $user,
            ['test@example.com']
        );

        // Test if we can render the email
        $rendered = $testEmail->render();
        echo "   ✅ Email template rendered successfully\n";
        echo "   Template length: " . strlen($rendered) . " characters\n";

        // Check if logo is referenced in the template
        if (strpos($rendered, 'logo-blue.webp') !== false) {
            echo "   ✅ Logo reference found in template\n";
        } else {
            echo "   ⚠️  Logo reference not found in template\n";
        }

    } catch (Exception $e) {
        echo "   ❌ Email template rendering error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 5. Test actual email sending (dry run)
    echo "5. Testing email sending (dry run)...\n";
    try {
        // Set mail to log driver for testing
        Config::set('mail.default', 'log');

        $user = User::first();
        $testEmail = new GeneralEmail(
            'Test Email Subject',
            'This is a test email body content.',
            $user,
            ['test@example.com']
        );

        // Send to log instead of actual email
        Mail::to('test@example.com')
            ->cc('engineering@orion-contracting.com')
            ->send($testEmail);

        echo "   ✅ Email sent to log successfully\n";
        echo "   Check storage/logs/laravel.log for the email content\n";

    } catch (Exception $e) {
        echo "   ❌ Email sending error: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    echo "\n";

    // 6. Check routes
    echo "6. Checking email routes...\n";
    $routes = [
        'emails.send-form' => 'GET /emails/send',
        'emails.send-general' => 'POST /emails/send'
    ];

    foreach ($routes as $name => $path) {
        try {
            $url = route($name);
            echo "   ✅ Route '{$name}': {$url}\n";
        } catch (Exception $e) {
            echo "   ❌ Route '{$name}': ERROR - {$e->getMessage()}\n";
        }
    }
    echo "\n";

    // 7. Check if logo directory exists
    echo "7. Checking uploads directory...\n";
    $uploadsDir = public_path('uploads');
    echo "   Uploads directory: {$uploadsDir}\n";
    echo "   Directory exists: " . (is_dir($uploadsDir) ? 'Yes' : 'No') . "\n";

    if (is_dir($uploadsDir)) {
        $files = scandir($uploadsDir);
        echo "   Files in uploads directory: " . implode(', ', array_filter($files, function($f) { return $f !== '.' && $f !== '..'; })) . "\n";
    } else {
        echo "   ❌ Uploads directory does not exist!\n";
    }

    echo "\n=== Debug Summary ===\n";
    echo "Common issues that prevent email sending:\n";
    echo "1. Logo file not found (will cause attachment error)\n";
    echo "2. Mail configuration incorrect\n";
    echo "3. SMTP server not accessible\n";
    echo "4. Missing email template\n";
    echo "5. Route not found\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
