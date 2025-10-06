<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Models\User;

echo "=== Gmail User Email Sending Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Get the current logged-in user
    $user = User::where('email', 'a.sayed@orioncc.com')->first();
    if (!$user) {
        echo "ERROR: User a.sayed@orioncc.com not found\n";
        exit(1);
    }

    echo "Testing Gmail sending for user: {$user->name} ({$user->email})\n\n";

    // Test 1: Send from user's Gmail with Gmail SMTP
    echo "1. Testing Gmail SMTP configuration...\n";

    // Configure Gmail SMTP settings
    config([
        'mail.default' => 'smtp',
        'mail.mailers.smtp.host' => 'smtp.gmail.com',
        'mail.mailers.smtp.port' => 587,
        'mail.mailers.smtp.username' => $user->email,
        'mail.mailers.smtp.password' => 'YOUR_GMAIL_APP_PASSWORD', // User needs to provide this
        'mail.mailers.smtp.encryption' => 'tls',
        'mail.from.address' => $user->email,
        'mail.from.name' => $user->name,
    ]);

    echo "   Gmail SMTP configured for: {$user->email}\n";
    echo "   Host: smtp.gmail.com\n";
    echo "   Port: 587\n";
    echo "   Encryption: TLS\n\n";

    // Test 2: Send email from user's Gmail
    echo "2. Testing email sending from user's Gmail...\n";
    try {
        Mail::raw('This is a test email sent from your own Gmail account through the Orion Contracting system.', function ($message) use ($user) {
            $message->to('test@example.com')
                    ->subject('Test Email from ' . $user->name . ' - Gmail')
                    ->from($user->email, $user->name)
                    ->replyTo($user->email, $user->name);
        });
        echo "   ✅ Email sent from Gmail successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Gmail email failed: " . $e->getMessage() . "\n";
        echo "   This is expected - you need to set up Gmail App Password\n";
    }

    // Test 3: Send to user's own email
    echo "\n3. Testing sending to user's own email...\n";
    try {
        Mail::raw('This is a test email sent to your own Gmail account.', function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Self Test from Gmail - ' . $user->name)
                    ->from($user->email, $user->name);
        });
        echo "   ✅ Self email sent from Gmail\n";
    } catch (Exception $e) {
        echo "   ❌ Self Gmail email failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== GMAIL SETUP REQUIREMENTS ===\n\n";
    echo "To send emails from Gmail accounts, each user needs:\n\n";
    echo "1. GMAIL ACCOUNT:\n";
    echo "   - Must have a Gmail account\n";
    echo "   - Must enable 2-Factor Authentication\n";
    echo "   - Must generate an App Password\n\n";

    echo "2. GMAIL APP PASSWORD:\n";
    echo "   - Go to Google Account settings\n";
    echo "   - Security > 2-Step Verification > App passwords\n";
    echo "   - Generate password for 'Mail'\n";
    echo "   - Use this password in the system\n\n";

    echo "3. SYSTEM CONFIGURATION:\n";
    echo "   - Each user needs to provide their Gmail credentials\n";
    echo "   - System stores encrypted credentials\n";
    echo "   - Uses Gmail SMTP for sending\n\n";

    echo "=== IMPLEMENTATION OPTIONS ===\n\n";
    echo "OPTION 1: USER GMAIL CREDENTIALS\n";
    echo "   - Each user provides Gmail username/password\n";
    echo "   - System uses Gmail SMTP for each user\n";
    echo "   - Best deliverability\n";
    echo "   - Requires user setup\n\n";

    echo "OPTION 2: SYSTEM GMAIL WITH USER EMAIL\n";
    echo "   - System uses one Gmail account\n";
    echo "   - Sends emails as different users\n";
    echo "   - Easier to implement\n";
    echo "   - May have deliverability issues\n\n";

    echo "OPTION 3: HYBRID APPROACH\n";
    echo "   - Users can choose their email provider\n";
    echo "   - Support Gmail, Outlook, Yahoo, etc.\n";
    echo "   - Most flexible\n";
    echo "   - Most complex to implement\n\n";

    echo "=== QUICK TEST ===\n\n";
    echo "To test Gmail sending right now:\n";
    echo "1. Get your Gmail App Password\n";
    echo "2. Update the password in this script\n";
    echo "3. Run the test again\n";
    echo "4. Check your Gmail for the test email\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
