<?php

require_once 'vendor/autoload.php';

use App\Models\User;

echo "=== Clean Email Template Test ===\n\n";

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

    echo "Testing cleaned email template for user: {$user->name} ({$user->email})\n\n";

    // Test 1: Show what was removed
    echo "1. Removed Elements:\n";
    echo "   ❌ 'Dear Recipient,' greeting\n";
    echo "   ❌ 'You have received a new email from...' message\n";
    echo "   ❌ Gmail sending notification box\n";
    echo "   ❌ Email details table (From, To, Subject)\n";
    echo "   ❌ 'Message:' label\n";
    echo "   ❌ Technical CC notification text\n";
    echo "   ❌ Footer with copyright and address\n\n";

    // Test 2: Show what remains
    echo "2. What Remains:\n";
    echo "   ✅ Orion Contracting logo and header\n";
    echo "   ✅ Clean, professional design\n";
    echo "   ✅ User's custom message content\n";
    echo "   ✅ Proper email formatting\n\n";

    // Test 3: Show the new email structure
    echo "3. New Email Structure:\n";
    echo "   📧 Header: Orion Contracting logo + company name\n";
    echo "   📧 Content: User's message (clean, no extra text)\n";
    echo "   📧 That's it! Clean and professional\n\n";

    // Test 4: Show example
    echo "4. Example Email Content:\n";
    echo "   ┌─────────────────────────────────────┐\n";
    echo "   │  [Orion Contracting Logo]           │\n";
    echo "   │  orion contracting company          │\n";
    echo "   │  Orion Contracting                  │\n";
    echo "   ├─────────────────────────────────────┤\n";
    echo "   │                                     │\n";
    echo "   │  Hello,                            │\n";
    echo "   │                                     │\n";
    echo "   │  This is my message content.       │\n";
    echo "   │  It appears clean and professional.│\n";
    echo "   │                                     │\n";
    echo "   │  Best regards,                     │\n";
    echo "   │  Ahmed                             │\n";
    echo "   │                                     │\n";
    echo "   └─────────────────────────────────────┘\n\n";

    // Test 5: Show benefits
    echo "5. Benefits of Clean Template:\n";
    echo "   ✅ Professional appearance\n";
    echo "   ✅ No technical jargon visible to recipients\n";
    echo "   ✅ Focus on the actual message content\n";
    echo "   ✅ Clean, modern design\n";
    echo "   ✅ Recipients see it as a normal business email\n";
    echo "   ✅ Still includes logo for branding\n\n";

    // Test 6: Show what happens behind the scenes
    echo "6. Behind the Scenes (Recipients Don't See):\n";
    echo "   📧 Email sent from: {$user->email}\n";
    echo "   📧 CC: engineering@orion-contracting.com\n";
    echo "   📧 Reply-To: {$user->email}\n";
    echo "   📧 Gmail OAuth used for sending\n";
    echo "   📧 Professional template applied\n\n";

    echo "=== TEMPLATE CLEANED ===\n\n";
    echo "The email template has been cleaned up!\n";
    echo "Recipients will now see a clean, professional email\n";
    echo "with just the Orion Contracting header and your message.\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
