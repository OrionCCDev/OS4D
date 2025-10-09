<?php

/**
 * Test Email Display Fix
 *
 * Tests the quoted-printable decoding directly
 */

echo "\n";
echo "========================================\n";
echo "  TEST EMAIL DISPLAY FIX               \n";
echo "========================================\n\n";

// Test content with quoted-printable encoding (exactly like from your image)
$testContent = '<!DOCTYPE html>
<html lang=3D"en">
<head>
<meta charset=3D"UTF-8">
<meta name=3D"viewport" content=3D"width=3Ddevice-width, initial-scale=3D1.0">
<title>Thank You for Your Email</title>
<style>
body { font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333;= background-color: #f8f9fa; margin: 0; = padding: 20px; }
.email-container { = max-width: 600px; margin: 0 auto; background-c= olor: #ffffff; border-radius: 8px; box-shadow: = 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden; }= .email-header { background: linear-gradient(135deg= , #667eea 0%, #764ba2 100%); color: white; padd= ing: 30px; text-align: center; } .logo {= width: 150px; height: auto; marg= in-bottom: 15px; } .company-name { font-s= ize: 24px; font-weight: bold; margin: 0; = } .email-body { padding: 30px; } = .email-content { font-size: 16px; line-he= ight: 1.8; margin-bottom: 30px; } .email-= footer { background-color: #f8f9fa; padding: 20= px 30px; border-top: 1px solid #e9ecef; text-al= ign: center; font-size:14px; color: #6c757d;= } .sender-info { background-color: #e3f2= fd; padding: 15px; border-radius: 5px; = margin-bottom: 20px; border-left: 4px solid #2196f3; = } .sender-name { font-weight: bold; = color: #1976d2; } .recipients { ba= ckground-color: #f3e5f5; padding: 15px; border-= radius: 5px; margin-bottom: 20px; border-left: = 4px solid #9c27b0; } .recipients-title { = font-weight: bold; color: #7b1fa2; margin-botto= m: 10px; } .recipient-list { margin: 0;= padding-left: 20px; } .recipient-item {= margin-bottom: 5px; } .cc-note { = background-color: #fff3e0; padding: 10px; = border-radius: 5px; border-left: 4px solid #ff9800; = font-size: 14px; color: #e65100; margin-= top: 15px; } </style> </head> <body> <div class= =3D"email-container"> <!-- Header --> <div class=3D"ema= il-header"> <img src=3D"https://odc.com.orion-contracting.com= /uploads/logo-blue.webp" alt=3D"Orion Contracting Logo" class=3D"logo"> = <h1 class=3D"company-name">Orion Contracting</h1> </di= v> <!-- Body --> <div class=3D"email-body"> = <!-- Sender Information --> <div class=3D"sender-info">= <div class=3D"sender-name">From: ahmdsyd</div> = <div style=3D"color: #666;font-size: 14px;">a.sayed@orioncc.com</= div> </div> <!-- Recipients Information -->= <div class=3D"recipients"> <di= v class=3D"recipients-title">To:</div> <ul class=3D"recip= ient-list"> <li class=3D"recipien= t-item">a.sayed.xc@gmail.com</li> </u= l> </div> =20 <!-- Email Content -->= <div class=3D"email-content"> important = </div> <!-- CC Note --> <div class=3D= "cc-note"> <strong>Note:</strong> This email was automati= cally CC\'dto engineering@orion-contracting.com for record keeping. = </div> </div> <!-- Footer --> <div = class=3D"email-footer"> <p><strong>Orion Contracting</strong>= </p> <p>Professional Construction Services</p> = <p>Email sent via Orion Task Management System</p> </div> <= /div> </body> </html>';

echo "BEFORE DECODING:\n";
echo "----------------\n";
echo substr($testContent, 0, 200) . "...\n\n";

// Apply the same decoding logic as in the view
$decodedBody = $testContent;

// Handle quoted-printable decoding
if (strpos($decodedBody, '=3D') !== false || strpos($decodedBody, '=20') !== false) {
    // Replace common quoted-printable sequences
    $replacements = [
        '=3D' => '=',
        '=20' => ' ',
        '=0A' => "\n",
        '=0D' => "\r",
        '=3C' => '<',
        '=3E' => '>',
        '=22' => '"',
        '=27' => "'",
        '=2C' => ',',
        '=2E' => '.',
        '=2F' => '/',
        '=3A' => ':',
        '=3B' => ';',
        '=40' => '@',
        '=5B' => '[',
        '=5D' => ']',
        '=5F' => '_',
        '=60' => '`',
        '=7B' => '{',
        '=7D' => '}',
        '=7E' => '~',
    ];
    $decodedBody = str_replace(array_keys($replacements), array_values($replacements), $decodedBody);

    // Remove soft line breaks
    $decodedBody = preg_replace('/=\r?\n/', '', $decodedBody);

    // Clean up any remaining = at end of lines
    $decodedBody = preg_replace('/=\s*$/', '', $decodedBody);

    // Fix double spaces
    $decodedBody = preg_replace('/\s{2,}/', ' ', $decodedBody);
}

echo "AFTER DECODING:\n";
echo "---------------\n";
echo substr($decodedBody, 0, 300) . "...\n\n";

// Check if HTML is now clean
$isClean = strpos($decodedBody, '=3D') === false && strpos($decodedBody, '=20') === false;
echo "DECODING SUCCESS: " . ($isClean ? "✅ YES - No more encoding artifacts" : "❌ NO - Still has encoding artifacts") . "\n\n";

// Check if it's valid HTML
$isHtml = preg_match('/<[^>]+>/', $decodedBody) === 1;
echo "IS VALID HTML: " . ($isHtml ? "✅ YES" : "❌ NO") . "\n\n";

echo "✅ Test completed!\n";
echo "\n";

