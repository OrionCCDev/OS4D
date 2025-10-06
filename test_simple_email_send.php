<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Models\User;

echo "=== Simple Email Send Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = User::first();
    if (!$user) {
        echo "ERROR: No users found\n";
        exit(1);
    }

    echo "Sending simple test email to: {$user->email}\n\n";

    // Send a very simple email
    Mail::raw('This is a simple test email to verify delivery. If you receive this, the email system is working correctly.', function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Simple Test Email - No Attachments')
                ->from('engineering@orion-contracting.com', 'Orion Contracting');
    });

    echo "✅ Simple email sent successfully!\n";
    echo "📧 Check your inbox and spam folder for this email.\n";
    echo "📧 Email should be from: engineering@orion-contracting.com\n";
    echo "📧 Subject: Simple Test Email - No Attachments\n\n";

    // Send another email with different content
    Mail::raw('Hello! This is another test email. Please check if you received this message. Thank you!', function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Another Test Email')
                ->from('engineering@orion-contracting.com', 'Orion Contracting');
    });

    echo "✅ Second test email sent!\n";
    echo "📧 Check for: Another Test Email\n\n";

    echo "=== What to Check ===\n";
    echo "1. Check your INBOX for these emails\n";
    echo "2. Check your SPAM/JUNK folder\n";
    echo "3. Check your PROMOTIONS tab (if using Gmail)\n";
    echo "4. Look for emails from: engineering@orion-contracting.com\n\n";

    echo "If you find the emails in SPAM folder:\n";
    echo "- Mark them as 'Not Spam'\n";
    echo "- Add engineering@orion-contracting.com to your contacts\n";
    echo "- This will help future emails go to your inbox\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
