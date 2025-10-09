<?php

/**
 * Test Email Decoding Fix
 *
 * Tests the EmailHelper to ensure quoted-printable decoding works
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Helpers\EmailHelper;

echo "\n";
echo "========================================\n";
echo "  TEST EMAIL DECODING FIX              \n";
echo "========================================\n\n";

// Test content with quoted-printable encoding (like from your image)
$testContent = '<!DOCTYPE html>
<html lang=3D"en">
<head>
<meta charset=3D"UTF-8">
<title>Test Email</title>
<style>
body { font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; }
</style>
</head>
<body>
<div class=3D"email-container">
<h1>Task Completion Confirmation</h1>
<p>This email was automatically CC\'d to engineering@orion-=
contracting.com for record keeping.</p>
</div>
</body>
</html>';

echo "ORIGINAL CONTENT:\n";
echo "-----------------\n";
echo $testContent . "\n\n";

echo "DECODED CONTENT:\n";
echo "----------------\n";
$decoded = EmailHelper::decodeEmailContent($testContent);
echo $decoded . "\n\n";

echo "IS HTML CONTENT: " . (EmailHelper::isHtmlContent($testContent) ? 'YES' : 'NO') . "\n\n";

echo "PLAIN TEXT EXTRACT:\n";
echo "-------------------\n";
$plainText = EmailHelper::extractPlainText($testContent);
echo $plainText . "\n\n";

echo "✅ Email decoding test completed!\n";
echo "\n";

