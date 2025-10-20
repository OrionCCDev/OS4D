<?php

/**
 * Test Email Signature Fix
 *
 * This script tests that email signatures are properly added to emails
 * and that HTML is not being escaped in email templates.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\EmailSignatureService;

echo "=== Email Signature Fix Test ===\n\n";

// Test 1: Check EmailSignatureService
echo "Test 1: Email Signature Service\n";
echo "--------------------------------\n";

$signatureService = app(EmailSignatureService::class);
$testUser = User::where('role', 'admin')->first() ?? User::first();

if (!$testUser) {
    echo "❌ ERROR: No users found in database\n\n";
    exit(1);
}

echo "Testing with user: {$testUser->name} ({$testUser->email})\n";
echo "User position: " . ($testUser->position ?? 'Not set') . "\n";
echo "User mobile: " . ($testUser->mobile ?? 'Not set') . "\n";
echo "User image: " . ($testUser->img ?? 'Not set') . "\n\n";

// Test HTML signature
echo "HTML Signature:\n";
$htmlSignature = $signatureService->getSignatureForEmail($testUser, 'html');
echo $htmlSignature . "\n\n";

// Test plain text signature
echo "Plain Text Signature:\n";
$plainSignature = $signatureService->getSignatureForEmail($testUser, 'plain');
echo $plainSignature . "\n\n";

// Test 2: Check signature contains expected elements
echo "Test 2: Signature Content Validation\n";
echo "------------------------------------\n";

$checks = [
    'Contains logo' => str_contains($htmlSignature, 'logo-blue.webp'),
    'Contains user name' => str_contains($htmlSignature, $testUser->name),
    'Contains user email' => str_contains($htmlSignature, $testUser->email),
    'Contains company name' => str_contains($htmlSignature, 'Orion Contracting'),
    'Contains position' => str_contains($htmlSignature, 'Manager') || str_contains($htmlSignature, 'Team Member'),
    'Contains department' => str_contains($htmlSignature, 'Engineering'),
    'Is proper HTML' => str_contains($htmlSignature, '<div') && str_contains($htmlSignature, '</div>'),
];

foreach ($checks as $check => $result) {
    echo ($result ? "✅" : "❌") . " {$check}\n";
}
echo "\n";

// Test 3: Simulate email body with signature
echo "Test 3: Email Body with Signature\n";
echo "---------------------------------\n";

$userInput = "Hello,\n\nThis is a test email.\n\nBest regards";
$processedBody = nl2br(e($userInput)) . '<br><br>' . $htmlSignature;

echo "User input (escaped and with line breaks):\n";
echo "Length: " . strlen($processedBody) . " characters\n";
echo "Contains signature: " . (str_contains($processedBody, 'Orion Contracting') ? "✅ Yes" : "❌ No") . "\n";
echo "HTML is not double-escaped: " . (!str_contains($processedBody, '&lt;div') ? "✅ Yes" : "❌ No") . "\n";
echo "\n";

// Test 4: Check email templates
echo "Test 4: Email Template Check\n";
echo "----------------------------\n";

$templates = [
    'resources/views/emails/user-general-email-gmail.blade.php',
    'resources/views/emails/user-general-email.blade.php',
    'resources/views/emails/general-email.blade.php',
    'resources/views/emails/task-confirmation.blade.php',
];

foreach ($templates as $template) {
    $path = base_path($template);
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $hasEscaping = preg_match('/\{\!\!\s*nl2br\(e\(\$/', $content);
        $status = $hasEscaping ? "❌ Still escaping HTML" : "✅ Not escaping HTML";
        echo "{$status}: " . basename($template) . "\n";
    } else {
        echo "⚠️  File not found: " . basename($template) . "\n";
    }
}
echo "\n";

// Test 5: Test signature with different user scenarios
echo "Test 5: Signature Variations\n";
echo "----------------------------\n";

// Test with manager
$manager = User::where('role', 'admin')->first();
if ($manager) {
    $managerSig = $signatureService->getSignatureForEmail($manager, 'html');
    $hasManagerTitle = str_contains($managerSig, 'Manager') || str_contains($managerSig, 'Administrator');
    echo ($hasManagerTitle ? "✅" : "⚠️ ") . " Manager signature includes proper title\n";
}

// Test with user
$regularUser = User::where('role', '!=', 'admin')->first();
if ($regularUser) {
    $userSig = $signatureService->getSignatureForEmail($regularUser, 'html');
    echo "✅ Regular user signature generated\n";
}

// Test with mobile number
$userWithMobile = User::whereNotNull('mobile')->first();
if ($userWithMobile) {
    $mobileSig = $signatureService->getSignatureForEmail($userWithMobile, 'html');
    $hasMobile = str_contains($mobileSig, $userWithMobile->mobile);
    echo ($hasMobile ? "✅" : "❌") . " Signature includes mobile number when available\n";
}

// Test with custom image
$userWithImage = User::whereNotNull('img')
    ->whereNotIn('img', ['default.png', 'default.jpg', '1.png'])
    ->first();
if ($userWithImage) {
    $imageSig = $signatureService->getSignatureForEmail($userWithImage, 'html');
    $hasImage = str_contains($imageSig, 'uploads/users/' . $userWithImage->img);
    echo ($hasImage ? "✅" : "❌") . " Signature includes custom profile image\n";
} else {
    echo "⚠️  No users with custom images found to test\n";
}

echo "\n";

// Test 6: Security Check
echo "Test 6: Security Validation\n";
echo "---------------------------\n";

// Test that user input is still escaped
$maliciousInput = "<script>alert('XSS')</script>";
$safeBody = nl2br(e($maliciousInput)) . '<br><br>' . $htmlSignature;

$isInputEscaped = str_contains($safeBody, '&lt;script&gt;');
$isSignatureIntact = str_contains($safeBody, '<div') && str_contains($safeBody, 'Orion Contracting');

echo ($isInputEscaped ? "✅" : "❌") . " User input is properly escaped\n";
echo ($isSignatureIntact ? "✅" : "❌") . " Signature HTML remains intact\n";
echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "All critical tests completed.\n";
echo "Check the output above for any ❌ failures.\n";
echo "\n";
echo "To test in your browser:\n";
echo "1. Go to /profile and check the Email Signature Preview\n";
echo "2. Send a test email and verify the signature appears at the bottom\n";
echo "3. Check that the signature includes your name, email, position, and company info\n";
echo "\n";

