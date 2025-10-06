<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Mail\UserGeneralEmail;
use App\Models\User;

echo "=== User Email Implementation Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = User::first();
    if (!$user) {
        echo "ERROR: No users found\n";
        exit(1);
    }

    echo "Testing user email sending implementation...\n";
    echo "User: {$user->name} ({$user->email})\n\n";

    // Test 1: Send email using UserGeneralEmail class
    echo "1. Testing UserGeneralEmail class...\n";
    try {
        $userEmail = new UserGeneralEmail(
            'Test Email from User - ' . $user->name,
            'This is a test email sent from the user\'s own email address. This should have much better deliverability and be less likely to go to spam folders.',
            $user,
            ['test@example.com']
        );

        Mail::to('test@example.com')
            ->cc('engineering@orion-contracting.com')
            ->send($userEmail);

        echo "   ✅ UserGeneralEmail sent successfully\n";
        echo "   📧 Email sent from: {$user->email}\n";
        echo "   📧 Email sent to: test@example.com\n";
        echo "   📧 CC: engineering@orion-contracting.com\n";

    } catch (Exception $e) {
        echo "   ❌ UserGeneralEmail failed: " . $e->getMessage() . "\n";
    }

    // Test 2: Send to user's own email
    echo "\n2. Testing sending to user's own email...\n";
    try {
        $selfEmail = new UserGeneralEmail(
            'Self Test Email - ' . $user->name,
            'This is a test email sent to your own email address. This should definitely be delivered to your inbox.',
            $user,
            [$user->email]
        );

        Mail::to($user->email)
            ->send($selfEmail);

        echo "   ✅ Self email sent successfully\n";
        echo "   📧 Check your inbox for: 'Self Test Email - {$user->name}'\n";
        echo "   📧 From: {$user->email}\n";

    } catch (Exception $e) {
        echo "   ❌ Self email failed: " . $e->getMessage() . "\n";
    }

    // Test 3: Test with different users
    echo "\n3. Testing with different users...\n";
    $users = User::limit(3)->get();
    foreach ($users as $testUser) {
        echo "   Testing with: {$testUser->name} ({$testUser->email})\n";
        try {
            $testEmail = new UserGeneralEmail(
                'Test from ' . $testUser->name,
                'This is a test email from ' . $testUser->name . ' via the Orion Contracting system.',
                $testUser,
                ['test@example.com']
            );

            Mail::to('test@example.com')->send($testEmail);
            echo "   ✅ Email sent from {$testUser->email}\n";

        } catch (Exception $e) {
            echo "   ❌ Email from {$testUser->email} failed: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== IMPLEMENTATION BENEFITS ===\n\n";
    echo "✅ BETTER DELIVERABILITY:\n";
    echo "   - Emails sent from user's own email address\n";
    echo "   - Recipients recognize the sender\n";
    echo "   - Less likely to be marked as spam\n";
    echo "   - More personal and professional\n\n";

    echo "✅ IMPROVED USER EXPERIENCE:\n";
    echo "   - Users send emails as themselves\n";
    echo "   - Recipients can reply directly\n";
    echo "   - More natural email flow\n";
    echo "   - Better email tracking\n\n";

    echo "✅ TECHNICAL ADVANTAGES:\n";
    echo "   - Uses existing SMTP configuration\n";
    echo "   - No need for user SMTP setup\n";
    echo "   - Easy to implement\n";
    echo "   - Maintains system control\n\n";

    echo "=== NEXT STEPS ===\n\n";
    echo "1. CHECK YOUR EMAIL:\n";
    echo "   - Look for: 'Self Test Email - {$user->name}'\n";
    echo "   - From: {$user->email}\n";
    echo "   - Check inbox AND spam folder\n\n";

    echo "2. IF YOU RECEIVE THE EMAIL:\n";
    echo "   ✅ This approach works!\n";
    echo "   ✅ We can implement this for all users\n";
    echo "   ✅ Much better deliverability\n\n";

    echo "3. IMPLEMENTATION:\n";
    echo "   - Update the general email form to use UserGeneralEmail\n";
    echo "   - Modify the controller to use user's email\n";
    echo "   - Update the email template\n";
    echo "   - Test with real recipients\n\n";

    echo "4. TESTING:\n";
    echo "   - Send test emails to different providers\n";
    echo "   - Check deliverability rates\n";
    echo "   - Monitor spam folder placement\n";
    echo "   - Get feedback from recipients\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
