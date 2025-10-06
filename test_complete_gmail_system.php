<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Services\UserEmailService;
use App\Models\User;

echo "=== Complete Gmail User Email System Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Get the user
    $user = User::where('email', 'a.sayed@orioncc.com')->first();
    if (!$user) {
        echo "ERROR: User a.sayed@orioncc.com not found\n";
        exit(1);
    }

    echo "Testing Gmail system for user: {$user->name} ({$user->email})\n\n";

    $userEmailService = new UserEmailService();

    // Test 1: Check current email status
    echo "1. Checking current email status...\n";
    echo "   Email configured: " . ($user->email_credentials_configured ? 'Yes' : 'No') . "\n";
    echo "   Email provider: " . ($user->email_provider ?? 'None') . "\n";
    echo "   Last updated: " . ($user->email_credentials_updated_at ?? 'Never') . "\n\n";

    // Test 2: Setup Gmail credentials (simulation)
    echo "2. Setting up Gmail credentials...\n";
    echo "   Note: You need to provide your actual Gmail App Password\n";
    echo "   For testing, we'll simulate the setup\n\n";

    // Test 3: Show Gmail setup instructions
    echo "3. Gmail Setup Instructions:\n";
    echo "   Step 1: Enable 2-Factor Authentication on your Gmail account\n";
    echo "   Step 2: Go to Google Account Settings > Security > 2-Step Verification\n";
    echo "   Step 3: Click 'App passwords' and generate one for 'Mail'\n";
    echo "   Step 4: Use the 16-character password in the system\n\n";

    // Test 4: Show email provider options
    echo "4. Available Email Providers:\n";
    $providers = $userEmailService->getEmailProviderOptions();
    foreach ($providers as $key => $provider) {
        echo "   {$key}: {$provider['name']}\n";
        echo "      Host: {$provider['host']}\n";
        echo "      Port: {$provider['port']}\n";
        echo "      Encryption: {$provider['encryption']}\n";
        echo "      Instructions: {$provider['instructions']}\n\n";
    }

    // Test 5: Show how to use the system
    echo "5. How to Use the System:\n";
    echo "   a) Go to: https://odc.com.orion-contracting.com/user-email-settings\n";
    echo "   b) Choose your email provider (Gmail, Outlook, etc.)\n";
    echo "   c) Enter your credentials\n";
    echo "   d) Test your email settings\n";
    echo "   e) Send emails from your account\n\n";

    // Test 6: Show the benefits
    echo "6. Benefits of User Email System:\n";
    echo "   ✅ Emails sent from YOUR email address\n";
    echo "   ✅ Recipients see emails from you, not the system\n";
    echo "   ✅ Much better deliverability\n";
    echo "   ✅ Less likely to go to spam\n";
    echo "   ✅ Recipients can reply directly to you\n";
    echo "   ✅ More professional appearance\n";
    echo "   ✅ Better email tracking\n\n";

    // Test 7: Show implementation steps
    echo "7. Implementation Steps:\n";
    echo "   Step 1: Run migration to add email credentials to users table\n";
    echo "   Step 2: Add routes for email settings\n";
    echo "   Step 3: Users configure their email credentials\n";
    echo "   Step 4: System uses user's email for sending\n";
    echo "   Step 5: Test with real recipients\n\n";

    // Test 8: Show database changes needed
    echo "8. Database Changes Needed:\n";
    echo "   - Add email_provider column\n";
    echo "   - Add email_smtp_host column\n";
    echo "   - Add email_smtp_port column\n";
    echo "   - Add email_smtp_username column\n";
    echo "   - Add email_smtp_password column (encrypted)\n";
    echo "   - Add email_smtp_encryption column\n";
    echo "   - Add email_credentials_configured column\n";
    echo "   - Add email_credentials_updated_at column\n\n";

    // Test 9: Show routes needed
    echo "9. Routes Needed:\n";
    echo "   GET  /user-email-settings     - Email settings page\n";
    echo "   POST /user-email/gmail        - Save Gmail credentials\n";
    echo "   POST /user-email/outlook      - Save Outlook credentials\n";
    echo "   POST /user-email/custom       - Save custom SMTP\n";
    echo "   POST /user-email/test         - Test email credentials\n";
    echo "   POST /user-email/send         - Send email from user account\n\n";

    echo "=== QUICK START GUIDE ===\n\n";
    echo "To implement this system:\n\n";
    echo "1. Run the migration:\n";
    echo "   php artisan migrate\n\n";
    echo "2. Add the routes to web.php\n";
    echo "3. Users go to /user-email-settings\n";
    echo "4. Users configure their Gmail/Outlook credentials\n";
    echo "5. Users can send emails from their own accounts\n\n";

    echo "=== TESTING ===\n\n";
    echo "To test this system:\n";
    echo "1. Set up your Gmail App Password\n";
    echo "2. Configure your email settings\n";
    echo "3. Send a test email to yourself\n";
    echo "4. Check if it arrives in your inbox\n";
    echo "5. Send emails to other recipients\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
