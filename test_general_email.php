<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Mail\GeneralEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing General Email Functionality\n";
echo "==================================\n\n";

// Step 1: Get a user
echo "1. Getting user...\n";
$user = User::first();
if (!$user) {
    echo "   ERROR: No user found\n";
    exit(1);
}
echo "   User: {$user->name} (ID: {$user->id})\n";
echo "   Email: {$user->email}\n\n";

// Step 2: Test email creation
echo "2. Testing email creation...\n";
try {
    $recipients = ['test@example.com', 'client@company.com'];
    $subject = 'Test Email from Orion Contracting';
    $body = "This is a test email to verify the general email functionality.\n\nBest regards,\n{$user->name}";

    $email = new GeneralEmail($subject, $body, $user, $recipients);
    echo "   SUCCESS: Email object created successfully\n";
    echo "   Subject: {$subject}\n";
    echo "   Recipients: " . implode(', ', $recipients) . "\n";
    echo "   Sender: {$user->name} ({$user->email})\n\n";

} catch (Exception $e) {
    echo "   ERROR: Failed to create email - {$e->getMessage()}\n";
    exit(1);
}

// Step 3: Test email template rendering
echo "3. Testing email template rendering...\n";
try {
    Auth::login($user);

    // Test if the email template can be rendered
    $view = view('emails.general-email', [
        'subject' => $subject,
        'body' => $body,
        'sender' => $user,
        'recipients' => $recipients
    ]);

    $rendered = $view->render();
    echo "   SUCCESS: Email template rendered successfully\n";
    echo "   Template size: " . strlen($rendered) . " characters\n";

    // Check if logo path is included
    if (strpos($rendered, 'logo-blue.webp') !== false) {
        echo "   SUCCESS: Logo path found in template\n";
    } else {
        echo "   WARNING: Logo path not found in template\n";
    }

    // Check if CC note is included
    if (strpos($rendered, 'engineering@orion-contracting.com') !== false) {
        echo "   SUCCESS: CC note found in template\n";
    } else {
        echo "   WARNING: CC note not found in template\n";
    }

    Auth::logout();

} catch (Exception $e) {
    echo "   ERROR: Failed to render email template - {$e->getMessage()}\n";
    Auth::logout();
}

echo "\nTest Complete!\n";
echo "==============\n";
echo "If you see 'SUCCESS' messages above, the email system is working correctly.\n";
echo "You can now access the email form at: /emails/send\n";
