<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Models\User;

echo "=== User Email Sending Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = User::first();
    if (!$user) {
        echo "ERROR: No users found\n";
        exit(1);
    }

    echo "Testing email sending from user's own email: {$user->email}\n\n";

    // Test 1: Send from user's email with SMTP authentication
    echo "1. Testing with user's email as sender...\n";
    try {
        Mail::raw('This is a test email sent from the user\'s own email address. This should have better deliverability.', function ($message) use ($user) {
            $message->to('test@example.com')
                    ->subject('Test Email from User - ' . $user->name)
                    ->from($user->email, $user->name)
                    ->replyTo($user->email, $user->name);
        });
        echo "   ✅ Email sent from user's email successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Email from user's email failed: " . $e->getMessage() . "\n";
    }

    // Test 2: Send to user's own email
    echo "\n2. Testing sending to user's own email...\n";
    try {
        Mail::raw('This is a test email sent to your own email address. This should definitely be delivered.', function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Test Email to Self - ' . $user->name)
                    ->from($user->email, $user->name)
                    ->replyTo($user->email, $user->name);
        });
        echo "   ✅ Email sent to user's own email successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Email to user's own email failed: " . $e->getMessage() . "\n";
    }

    // Test 3: Send with different user emails
    echo "\n3. Testing with different user emails...\n";
    $users = User::limit(3)->get();
    foreach ($users as $testUser) {
        echo "   Testing with user: {$testUser->name} ({$testUser->email})\n";
        try {
            Mail::raw("This is a test email from {$testUser->name}.", function ($message) use ($testUser) {
                $message->to('test@example.com')
                        ->subject('Test from ' . $testUser->name)
                        ->from($testUser->email, $testUser->name);
            });
            echo "   ✅ Email sent from {$testUser->email}\n";
        } catch (Exception $e) {
            echo "   ❌ Email from {$testUser->email} failed: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== ADVANTAGES OF USER EMAIL SENDING ===\n\n";
    echo "✅ Better deliverability (emails from known addresses)\n";
    echo "✅ Recipients recognize the sender\n";
    echo "✅ Less likely to go to spam folder\n";
    echo "✅ More personal and professional\n";
    echo "✅ Recipients can reply directly\n\n";

    echo "=== IMPLEMENTATION OPTIONS ===\n\n";
    echo "1. USE USER'S EMAIL AS SENDER:\n";
    echo "   - From: user@theircompany.com\n";
    echo "   - Reply-To: user@theircompany.com\n";
    echo "   - Authentication: Use system SMTP but user's email\n\n";

    echo "2. USE USER'S SMTP CREDENTIALS:\n";
    echo "   - Each user provides their own SMTP settings\n";
    echo "   - More complex but better deliverability\n";
    echo "   - Requires user configuration\n\n";

    echo "3. HYBRID APPROACH:\n";
    echo "   - Use system SMTP but user's email as sender\n";
    echo "   - Best balance of simplicity and deliverability\n";
    echo "   - Easy to implement\n\n";

    echo "=== CHECK YOUR EMAIL NOW ===\n\n";
    echo "Look for these emails in your inbox:\n";
    echo "- 'Test Email to Self - {$user->name}'\n";
    echo "- From: {$user->email}\n";
    echo "- Check both inbox and spam folder\n\n";

    echo "If you receive the email from your own address:\n";
    echo "✅ This approach will work!\n";
    echo "✅ We can implement this for all users\n";
    echo "✅ Much better deliverability\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
