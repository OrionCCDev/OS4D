<?php
/**
 * Debug CC Field Issue
 * 
 * This script debugs why the CC field is not being stored properly
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Debug CC Field Issue ===\n";
echo "Debugging why CC field is not being stored\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/vendor/autoload.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Step 1: Check the Email model
    echo "\n--- Checking Email Model ---\n";
    $email = new \App\Models\Email();
    $fillable = $email->getFillable();
    echo "Fillable fields: " . implode(', ', $fillable) . "\n";
    
    if (in_array('cc', $fillable)) {
        echo "✅ 'cc' is in fillable fields\n";
    } else {
        echo "❌ 'cc' is NOT in fillable fields\n";
    }

    // Step 2: Check database schema
    echo "\n--- Checking Database Schema ---\n";
    $hasCcColumn = \Illuminate\Support\Facades\Schema::hasColumn('emails', 'cc');
    echo "CC column exists: " . ($hasCcColumn ? 'YES' : 'NO') . "\n";
    
    if ($hasCcColumn) {
        $columnType = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM emails LIKE 'cc'")[0];
        echo "CC column type: " . $columnType->Type . "\n";
        echo "CC column null: " . $columnType->Null . "\n";
    }

    // Step 3: Test direct database insertion
    echo "\n--- Testing Direct Database Insertion ---\n";
    $testData = [
        'user_id' => 1,
        'from_email' => 'debug@example.com',
        'to_email' => 'engineering@orion-contracting.com',
        'cc' => 'test@example.com, debug@example.com',
        'cc_emails' => ['test@example.com', 'debug@example.com'],
        'subject' => 'Debug CC Field Test - ' . now()->format('Y-m-d H:i:s'),
        'body' => 'This is a debug test for CC field storage.',
        'email_type' => 'received',
        'status' => 'received',
        'is_tracked' => false,
        'received_at' => now(),
        'message_id' => 'debug-cc-test-' . time(),
        'created_at' => now(),
        'updated_at' => now(),
    ];
    
    echo "Test data CC field: '{$testData['cc']}'\n";
    
    try {
        $emailId = \Illuminate\Support\Facades\DB::table('emails')->insertGetId($testData);
        echo "✅ Direct database insertion successful, ID: {$emailId}\n";
        
        // Check what was actually stored
        $storedEmail = \App\Models\Email::find($emailId);
        echo "Stored CC field: '{$storedEmail->cc}'\n";
        echo "Stored CC_emails: " . json_encode($storedEmail->cc_emails) . "\n";
        
    } catch (Exception $e) {
        echo "❌ Direct database insertion failed: " . $e->getMessage() . "\n";
    }

    // Step 4: Test Eloquent model creation
    echo "\n--- Testing Eloquent Model Creation ---\n";
    try {
        $eloquentEmail = \App\Models\Email::create([
            'user_id' => 1,
            'from_email' => 'eloquent@example.com',
            'to_email' => 'engineering@orion-contracting.com',
            'cc' => 'eloquent@example.com, test@example.com',
            'cc_emails' => ['eloquent@example.com', 'test@example.com'],
            'subject' => 'Eloquent CC Field Test - ' . now()->format('Y-m-d H:i:s'),
            'body' => 'This is an Eloquent test for CC field storage.',
            'email_type' => 'received',
            'status' => 'received',
            'is_tracked' => false,
            'received_at' => now(),
            'message_id' => 'eloquent-cc-test-' . time(),
        ]);
        
        echo "✅ Eloquent model creation successful, ID: {$eloquentEmail->id}\n";
        echo "Stored CC field: '{$eloquentEmail->cc}'\n";
        echo "Stored CC_emails: " . json_encode($eloquentEmail->cc_emails) . "\n";
        
    } catch (Exception $e) {
        echo "❌ Eloquent model creation failed: " . $e->getMessage() . "\n";
    }

    // Step 5: Check recent emails
    echo "\n--- Recent Emails with CC Data ---\n";
    $recentEmails = \App\Models\Email::orderBy('created_at', 'desc')->limit(3)->get();
    foreach ($recentEmails as $email) {
        echo "ID: {$email->id}, From: {$email->from_email}, To: {$email->to_email}\n";
        echo "   CC: '{$email->cc}'\n";
        echo "   CC_emails: " . json_encode($email->cc_emails) . "\n";
        echo "   Subject: {$email->subject}\n\n";
    }

    echo "\n=== Debug Complete ===\n";
    echo "Check the results above to identify the CC field issue.\n";

} catch (Exception $e) {
    echo "❌ Debug failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
