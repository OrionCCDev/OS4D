<?php

/**
 * Test Email Display
 *
 * This script will test if the email displays correctly by simulating the view logic
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;

echo "\n";
echo "========================================\n";
echo "  TEST EMAIL DISPLAY LOGIC             \n";
echo "========================================\n\n";

// Get the email
$email = Email::find(25);

if (!$email) {
    echo "❌ Email with ID 25 not found.\n";
    exit;
}

echo "Testing email display logic...\n\n";

// Simulate the view logic
$emailBody = $email->body;
$decodedBody = quoted_printable_decode($emailBody);

// Check if we still have encoding artifacts
if (strpos($decodedBody, '=3D') !== false || strpos($decodedBody, '=20') !== false || strpos($decodedBody, '=3C') !== false) {
    echo "⚠️ Applying manual decoding...\n";

    $replacements = [
        '=3D' => '=', '=20' => ' ', '=0A' => "\n", '=0D' => "\r",
        '=3C' => '<', '=3E' => '>', '=22' => '"', '=27' => "'",
        '=2C' => ',', '=2E' => '.', '=2F' => '/', '=3A' => ':',
        '=3B' => ';', '=40' => '@', '=5B' => '[', '=5D' => ']',
        '=5F' => '_', '=60' => '`', '=7B' => '{', '=7D' => '}',
        '=7E' => '~', '=09' => "\t", '=28' => '(', '=29' => ')',
        '=2B' => '+', '=2D' => '-', '=3F' => '?', '=21' => '!',
        '=23' => '#', '=24' => '$', '=25' => '%', '=26' => '&',
        '=2A' => '*',
    ];

    $decodedBody = str_replace(array_keys($replacements), array_values($replacements), $emailBody);
    $decodedBody = preg_replace('/=\r?\n/', '', $decodedBody);
    $decodedBody = preg_replace('/=\s*$/', '', $decodedBody);
    $decodedBody = preg_replace('/\s{2,}/', ' ', $decodedBody);
    $decodedBody = preg_replace('/([a-zA-Z])\s+([a-zA-Z])/', '$1$2', $decodedBody);
}

$displayBody = $decodedBody;

// Fix character encoding
$displayBody = mb_convert_encoding($displayBody, 'UTF-8', 'UTF-8');
$displayBody = str_replace(['', '=', '=20', '=3D'], ['', '=', ' ', '='], $displayBody);

echo "Display Body Analysis:\n";
echo "----------------------\n";
echo "Length: " . strlen($displayBody) . " characters\n";
echo "Has DOCTYPE: " . (str_contains($displayBody, '<!DOCTYPE html>') ? 'Yes' : 'No') . "\n";
echo "Has HTML tag: " . (str_contains($displayBody, '<html') ? 'Yes' : 'No') . "\n";
echo "Has encoding artifacts: " . ((strpos($displayBody, '=3D') !== false || strpos($displayBody, '=20') !== false) ? 'Yes' : 'No') . "\n";
echo "Has broken characters: " . (strpos($displayBody, '') !== false ? 'Yes' : 'No') . "\n";

// Check for specific issues
$issues = [];
if (strpos($displayBody, 'widthvice-width') !== false) {
    $issues[] = "Broken 'device-width' attribute";
}
if (strpos($displayBody, '=3D') !== false) {
    $issues[] = "Still has =3D encoding";
}
if (strpos($displayBody, '=20') !== false) {
    $issues[] = "Still has =20 encoding";
}
if (strpos($displayBody, '') !== false) {
    $issues[] = "Has broken characters";
}

if (empty($issues)) {
    echo "✅ No issues found!\n";
} else {
    echo "⚠️ Issues found:\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
}

echo "\nFirst 200 characters:\n";
echo "---------------------\n";
echo htmlspecialchars(substr($displayBody, 0, 200)) . "...\n\n";

// Test if it would render as valid HTML
$isValidHtml = preg_match('/<html[^>]*>/i', $displayBody) && preg_match('/<\/html>/i', $displayBody);
echo "Would render as valid HTML: " . ($isValidHtml ? "✅ Yes" : "❌ No") . "\n";

echo "\n========================================\n";
echo "  DISPLAY TEST COMPLETED               \n";
echo "========================================\n\n";

