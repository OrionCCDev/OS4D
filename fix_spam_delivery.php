<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Models\User;

echo "=== Spam Delivery Fix Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = User::first();
    if (!$user) {
        echo "ERROR: No users found\n";
        exit(1);
    }

    echo "Testing improved email deliverability...\n\n";

    // 1. Test with improved email content
    echo "1. Testing with improved email content...\n";

    $improvedContent = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Professional Email</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #0056b3;'>Orion Contracting</h2>
            <p>Dear Recipient,</p>
            <p>This is a professional email from Orion Contracting. We are a legitimate business providing construction and design services.</p>
            <p>This email is being sent to test our email delivery system and ensure proper communication with our clients and partners.</p>
            <p>If you have any questions or concerns, please feel free to contact us at engineering@orion-contracting.com</p>
            <p>Thank you for your time.</p>
            <p>Best regards,<br>Orion Contracting Team</p>
        </div>
    </body>
    </html>
    ";

    Mail::html($improvedContent, function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Professional Communication - Orion Contracting')
                ->from('engineering@orion-contracting.com', 'Orion Contracting')
                ->replyTo('engineering@orion-contracting.com', 'Orion Contracting');
    });

    echo "✅ Improved email sent!\n\n";

    // 2. Test with proper email headers
    echo "2. Testing with proper email headers...\n";

    Mail::html('This email includes proper headers to improve deliverability.', function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Email with Proper Headers')
                ->from('engineering@orion-contracting.com', 'Orion Contracting')
                ->replyTo('engineering@orion-contracting.com', 'Orion Contracting')
                ->priority(3) // Normal priority
                ->getHeaders()
                ->addTextHeader('X-Mailer', 'Orion Contracting System')
                ->addTextHeader('X-Priority', '3')
                ->addTextHeader('X-MSMail-Priority', 'Normal')
                ->addTextHeader('Importance', 'Normal');
    });

    echo "✅ Email with proper headers sent!\n\n";

    // 3. Test with different subject lines
    echo "3. Testing with different subject lines...\n";

    $subjects = [
        'Important: Project Update from Orion Contracting',
        'Orion Contracting - Business Communication',
        'Professional Update - Orion Contracting',
        'Orion Contracting - Official Communication'
    ];

    foreach ($subjects as $index => $subject) {
        Mail::html("This is test email #{$index} with subject: {$subject}", function ($message) use ($user, $subject) {
            $message->to($user->email)
                    ->subject($subject)
                    ->from('engineering@orion-contracting.com', 'Orion Contracting');
        });
        echo "✅ Email with subject '{$subject}' sent!\n";
    }

    echo "\n=== Spam Prevention Strategies ===\n\n";

    echo "1. EMAIL CONTENT IMPROVEMENTS:\n";
    echo "   ✅ Use professional language\n";
    echo "   ✅ Avoid spam trigger words\n";
    echo "   ✅ Include proper HTML structure\n";
    echo "   ✅ Add company information\n";
    echo "   ✅ Use proper email formatting\n\n";

    echo "2. EMAIL HEADERS IMPROVEMENTS:\n";
    echo "   ✅ Set proper From address\n";
    echo "   ✅ Add Reply-To header\n";
    echo "   ✅ Include X-Mailer header\n";
    echo "   ✅ Set proper priority\n";
    echo "   ✅ Add company identification\n\n";

    echo "3. DNS RECORDS TO ADD:\n";
    echo "   ✅ SPF Record: v=spf1 include:_spf.google.com ~all\n";
    echo "   ✅ DKIM Record: (Contact your hosting provider)\n";
    echo "   ✅ DMARC Record: v=DMARC1; p=quarantine; rua=mailto:dmarc@orion-contracting.com\n\n";

    echo "4. EMAIL SENDING BEST PRACTICES:\n";
    echo "   ✅ Send from a consistent email address\n";
    echo "   ✅ Use proper email authentication\n";
    echo "   ✅ Avoid sending too many emails at once\n";
    echo "   ✅ Include unsubscribe option\n";
    echo "   ✅ Use professional email templates\n\n";

    echo "5. RECIPIENT EDUCATION:\n";
    echo "   ✅ Ask recipients to add engineering@orion-contracting.com to contacts\n";
    echo "   ✅ Instruct recipients to check spam folders\n";
    echo "   ✅ Provide instructions for whitelisting your domain\n";
    echo "   ✅ Send a test email first to verify delivery\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
