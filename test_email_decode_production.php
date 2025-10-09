<?php

/**
 * Test Email Decoding on Production
 *
 * This script will test the email decoding with actual email data from your database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;

echo "\n";
echo "========================================\n";
echo "  TEST EMAIL DECODING ON PRODUCTION    \n";
echo "========================================\n\n";

// Get the latest email with ID 25 (from your notification)
$email = Email::find(25);

if (!$email) {
    echo "❌ Email with ID 25 not found.\n";
    exit;
}

echo "Email Details:\n";
echo "--------------\n";
echo "ID: {$email->id}\n";
echo "Subject: {$email->subject}\n";
echo "From: {$email->from_email}\n";
echo "To: {$email->to_email}\n";
echo "Body Length: " . strlen($email->body) . " characters\n\n";

echo "BEFORE DECODING (first 300 chars):\n";
echo "-----------------------------------\n";
echo substr($email->body, 0, 300) . "...\n\n";

// Test the same decoding logic as in the view
$emailBody = $email->body;

// First, try PHP's built-in quoted-printable decode
$decodedBody = quoted_printable_decode($emailBody);

echo "AFTER PHP quoted_printable_decode() (first 300 chars):\n";
echo "-------------------------------------------------------\n";
echo substr($decodedBody, 0, 300) . "...\n\n";

// If that didn't work or we still have encoding artifacts, do manual decoding
if (strpos($decodedBody, '=3D') !== false || strpos($decodedBody, '=20') !== false || strpos($decodedBody, '=3C') !== false) {
    echo "⚠️ Still has encoding artifacts, applying manual decoding...\n\n";

    // Comprehensive quoted-printable decoding
    $replacements = [
        '=3D' => '=',     // equals sign
        '=20' => ' ',     // space
        '=0A' => "\n",    // line feed
        '=0D' => "\r",    // carriage return
        '=3C' => '<',     // less than
        '=3E' => '>',     // greater than
        '=22' => '"',     // double quote
        '=27' => "'",     // single quote
        '=2C' => ',',     // comma
        '=2E' => '.',     // period
        '=2F' => '/',     // forward slash
        '=3A' => ':',     // colon
        '=3B' => ';',     // semicolon
        '=40' => '@',     // at symbol
        '=5B' => '[',     // left bracket
        '=5D' => ']',     // right bracket
        '=5F' => '_',     // underscore
        '=60' => '`',     // backtick
        '=7B' => '{',     // left brace
        '=7D' => '}',     // right brace
        '=7E' => '~',     // tilde
        '=09' => "\t",    // tab
        '=28' => '(',     // left parenthesis
        '=29' => ')',     // right parenthesis
        '=2B' => '+',     // plus
        '=2D' => '-',     // minus
        '=3F' => '?',     // question mark
        '=21' => '!',     // exclamation
        '=23' => '#',     // hash
        '=24' => '$',     // dollar
        '=25' => '%',     // percent
        '=26' => '&',     // ampersand
        '=2A' => '*',     // asterisk
    ];

    // Apply all replacements
    $decodedBody = str_replace(array_keys($replacements), array_values($replacements), $emailBody);

    // Remove soft line breaks (= at end of lines)
    $decodedBody = preg_replace('/=\r?\n/', '', $decodedBody);
    $decodedBody = preg_replace('/=\s*$/', '', $decodedBody);

    // Clean up multiple spaces
    $decodedBody = preg_replace('/\s{2,}/', ' ', $decodedBody);

    // Fix broken words that were split across lines
    $decodedBody = preg_replace('/([a-zA-Z])\s+([a-zA-Z])/', '$1$2', $decodedBody);

    echo "AFTER MANUAL DECODING (first 300 chars):\n";
    echo "-----------------------------------------\n";
    echo substr($decodedBody, 0, 300) . "...\n\n";
} else {
    echo "✅ No encoding artifacts found after PHP decode.\n\n";
}

// Check if it's valid HTML
$isHtml = preg_match('/<[^>]+>/', $decodedBody) === 1;
echo "IS VALID HTML: " . ($isHtml ? "✅ YES" : "❌ NO") . "\n";

// Check if it has any remaining encoding artifacts
$hasArtifacts = strpos($decodedBody, '=3D') !== false || strpos($decodedBody, '=20') !== false;
echo "HAS ENCODING ARTIFACTS: " . ($hasArtifacts ? "❌ YES" : "✅ NO") . "\n";

// Check if it contains proper email structure
$hasEmailStructure = strpos($decodedBody, '<html') !== false || strpos($decodedBody, '<body') !== false;
echo "HAS EMAIL STRUCTURE: " . ($hasEmailStructure ? "✅ YES" : "❌ NO") . "\n\n";

echo "========================================\n";
echo "  DECODING TEST COMPLETED              \n";
echo "========================================\n\n";

