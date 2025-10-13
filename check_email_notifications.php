<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== EMAIL NOTIFICATION SYSTEM DEBUG ===\n\n";

// Step 1: Check emails in engineering inbox
echo "STEP 1: Checking emails in engineering inbox (last 7 days)...\n";
$emails = \App\Models\Email::where('email_source', 'designers_inbox')
    ->where('created_at', '>=', now()->subDays(7))
    ->orderBy('created_at', 'desc')
    ->get();

echo "Found " . $emails->count() . " emails in last 7 days\n\n";

if ($emails->count() > 0) {
    echo "Recent emails:\n";
    foreach ($emails->take(5) as $email) {
        echo "  ID: {$email->id}\n";
        echo "  From: {$email->from_email}\n";
        echo "  To: {$email->to_email}\n";
        echo "  CC: {$email->cc}\n";
        echo "  Subject: " . substr($email->subject, 0, 50) . "...\n";
        echo "  Created: {$email->created_at}\n";
        echo "  ---\n";
    }
}

// Step 2: Check notifications for these emails
echo "\nSTEP 2: Checking email notifications...\n";
$emailNotifications = \App\Models\UnifiedNotification::where('category', 'email')
    ->where('created_at', '>=', now()->subDays(7))
    ->orderBy('created_at', 'desc')
    ->get();

echo "Found " . $emailNotifications->count() . " email notifications in last 7 days\n\n";

if ($emailNotifications->count() > 0) {
    echo "Recent email notifications:\n";
    foreach ($emailNotifications->take(5) as $notif) {
        $user = \App\Models\User::find($notif->user_id);
        echo "  ID: {$notif->id}\n";
        echo "  User: {$user->name} ({$user->email}) - Role: {$user->role}\n";
        echo "  Type: {$notif->type}\n";
        echo "  Title: {$notif->title}\n";
        echo "  Message: " . substr($notif->message, 0, 60) . "...\n";
        echo "  Created: {$notif->created_at}\n";
        echo "  ---\n";
    }
}

// Step 3: Check for engineering_inbox notifications specifically
echo "\nSTEP 3: Checking engineering_inbox specific notifications...\n";
$engineeringNotifs = \App\Models\UnifiedNotification::whereIn('type', [
    'engineering_inbox_received',
    'engineering_inbox_user_involved'
])->where('created_at', '>=', now()->subDays(7))
->orderBy('created_at', 'desc')
->get();

echo "Found " . $engineeringNotifs->count() . " engineering inbox notifications\n\n";

if ($engineeringNotifs->count() > 0) {
    echo "Engineering inbox notifications:\n";
    foreach ($engineeringNotifs as $notif) {
        $user = \App\Models\User::find($notif->user_id);
        echo "  ID: {$notif->id}\n";
        echo "  User: {$user->name} ({$user->email}) - Role: {$user->role}\n";
        echo "  Type: {$notif->type}\n";
        echo "  Title: {$notif->title}\n";
        echo "  Created: {$notif->created_at}\n";
        echo "  ---\n";
    }
}

// Step 4: Check managers
echo "\nSTEP 4: Checking managers in system...\n";
$managers = \App\Models\User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();
echo "Found " . $managers->count() . " managers\n";
foreach ($managers as $manager) {
    echo "  - {$manager->name} ({$manager->email}) - Role: {$manager->role}\n";
}

// Step 5: Check regular users
echo "\nSTEP 5: Checking regular users in system...\n";
$users = \App\Models\User::where('role', 'user')->get();
echo "Found " . $users->count() . " regular users\n";
foreach ($users as $user) {
    echo "  - {$user->name} ({$user->email})\n";
}

// Step 6: Test if notification service is working
echo "\n\nSTEP 6: Testing notification service...\n";
try {
    $testEmail = $emails->first();
    if ($testEmail) {
        echo "Using test email ID: {$testEmail->id}\n";
        echo "From: {$testEmail->from_email}\n";
        echo "To: {$testEmail->to_email}\n";
        echo "CC: {$testEmail->cc}\n";

        // Check if any user emails match
        $allUsers = \App\Models\User::all();
        echo "\nChecking which users would be notified:\n";
        foreach ($allUsers as $user) {
            $userEmail = strtolower(trim($user->email));
            $isInvolved = false;
            $involvementType = '';

            if (strpos(strtolower($testEmail->from_email), $userEmail) !== false) {
                $isInvolved = true;
                $involvementType = 'FROM';
            } elseif (strpos(strtolower($testEmail->to_email), $userEmail) !== false) {
                $isInvolved = true;
                $involvementType = 'TO';
            } elseif (strpos(strtolower($testEmail->cc), $userEmail) !== false) {
                $isInvolved = true;
                $involvementType = 'CC';
            }

            if ($isInvolved) {
                echo "  ✓ {$user->name} ({$user->email}) - {$involvementType}\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error testing notification service: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";

