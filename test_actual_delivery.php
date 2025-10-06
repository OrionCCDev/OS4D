<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Models\User;

echo "=== Actual Email Delivery Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = User::first();
    if (!$user) {
        echo "ERROR: No users found\n";
        exit(1);
    }

    echo "Testing actual email delivery to: {$user->email}\n\n";

    // Test 1: Send a simple email
    echo "1. Sending simple test email...\n";
    try {
        Mail::raw('This is a simple test email to verify delivery. Please check your inbox AND spam folder.', function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('SIMPLE TEST - Check Inbox AND Spam Folder')
                    ->from('engineering@orion-contracting.com', 'Orion Contracting');
        });
        echo "   ✅ Simple email sent successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Simple email failed: " . $e->getMessage() . "\n";
    }

    // Test 2: Send with different subject
    echo "\n2. Sending email with different subject...\n";
    try {
        Mail::raw('This is another test email. Please check both your inbox and spam folder for this message.', function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('IMPORTANT: Check Spam Folder - Orion Contracting')
                    ->from('engineering@orion-contracting.com', 'Orion Contracting');
        });
        echo "   ✅ Second email sent successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Second email failed: " . $e->getMessage() . "\n";
    }

    // Test 3: Send to a different email provider
    echo "\n3. Testing with Gmail...\n";
    try {
        Mail::raw('This is a test email to Gmail. Please check your inbox and spam folder.', function ($message) {
            $message->to('test@gmail.com')
                    ->subject('Test Email to Gmail - Orion Contracting')
                    ->from('engineering@orion-contracting.com', 'Orion Contracting');
        });
        echo "   ✅ Gmail test email sent\n";
    } catch (Exception $e) {
        echo "   ❌ Gmail test failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== WHAT TO CHECK NOW ===\n\n";
    echo "1. CHECK YOUR EMAIL INBOX:\n";
    echo "   - Look for: 'SIMPLE TEST - Check Inbox AND Spam Folder'\n";
    echo "   - Look for: 'IMPORTANT: Check Spam Folder - Orion Contracting'\n\n";

    echo "2. CHECK YOUR SPAM FOLDER:\n";
    echo "   - Look for emails from: engineering@orion-contracting.com\n";
    echo "   - Look for emails with: Orion Contracting\n";
    echo "   - Check both subject lines above\n\n";

    echo "3. IF YOU FIND THEM IN SPAM:\n";
    echo "   - Mark as 'Not Spam' or 'Not Junk'\n";
    echo "   - Add engineering@orion-contracting.com to your contacts\n";
    echo "   - This will help future emails go to inbox\n\n";

    echo "4. IF YOU DON'T FIND THEM ANYWHERE:\n";
    echo "   - Wait 5-10 minutes (email delivery can be delayed)\n";
    echo "   - Check all email folders (Inbox, Spam, Junk, Promotions)\n";
    echo "   - Try sending to a different email address\n\n";

    echo "5. GMAIL USERS:\n";
    echo "   - Check the 'Promotions' tab\n";
    echo "   - Check the 'Updates' tab\n";
    echo "   - Check the 'Social' tab\n\n";

    echo "=== NEXT STEPS ===\n\n";
    echo "1. Check your email now\n";
    echo "2. Tell me what you found (inbox, spam, or nothing)\n";
    echo "3. If found in spam, we'll implement solutions to fix this\n";
    echo "4. If not found anywhere, we'll investigate further\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
