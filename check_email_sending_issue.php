<?php

/**
 * Check Email Sending Issue
 *
 * This script checks if emails are actually being sent
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaskEmailPreparation;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "  EMAIL SENDING ISSUE DIAGNOSTIC       \n";
echo "========================================\n\n";

// Check the latest email preparation
echo "1. LATEST EMAIL PREPARATION DETAILS\n";
echo "------------------------------------\n";
$prep = TaskEmailPreparation::latest()->first();

if ($prep) {
    echo "ID: {$prep->id}\n";
    echo "Task ID: {$prep->task_id}\n";
    echo "Status: {$prep->status}\n";
    echo "To: {$prep->to_emails}\n";
    echo "Subject: {$prep->subject}\n";
    echo "Created: {$prep->created_at}\n";
    echo "Sent At: " . ($prep->sent_at ?? 'NULL') . "\n";

    // Check the associated task
    $task = Task::find($prep->task_id);
    if ($task) {
        echo "\nTask Details:\n";
        echo "  Title: {$task->title}\n";
        echo "  Status: {$task->status}\n";
        echo "  Submitted At: " . ($task->submitted_at ?? 'NULL') . "\n";
        echo "  Completed At: " . ($task->completed_at ?? 'NULL') . "\n";
        echo "  Started At: " . ($task->started_at ?? 'NULL') . "\n";
        echo "  Approved At: " . ($task->approved_at ?? 'NULL') . "\n";
    }
} else {
    echo "No email preparations found.\n";
}
echo "\n";

// Check if there are tracked emails
echo "2. TRACKED EMAILS (from email tracking)\n";
echo "----------------------------------------\n";
try {
    $trackedEmails = DB::table('tracked_emails')
        ->where('task_id', $prep->task_id ?? 0)
        ->latest()
        ->take(3)
        ->get();

    if ($trackedEmails->isEmpty()) {
        echo "⚠ No tracked emails found for this task.\n";
        echo "This means the email was NOT actually sent!\n";
    } else {
        foreach ($trackedEmails as $email) {
            echo "Message ID: {$email->message_id}\n";
            echo "  Status: {$email->status}\n";
            echo "  Sent At: {$email->sent_at}\n";
            echo "  To: {$email->to_email}\n\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Check for job failures in logs
echo "3. CHECKING FOR EMAIL JOB ERRORS\n";
echo "---------------------------------\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -1000);

    // Look for SendTaskConfirmationEmailJob errors
    $jobErrors = array_filter($recentLines, function($line) {
        return (stripos($line, 'SendTaskConfirmationEmailJob') !== false ||
                stripos($line, 'Background email sending job') !== false) &&
               (stripos($line, 'ERROR') !== false || stripos($line, 'failed') !== false);
    });

    if (empty($jobErrors)) {
        echo "✓ No job errors found.\n";
    } else {
        echo "⚠ Found errors:\n";
        foreach (array_slice($jobErrors, -5) as $error) {
            echo substr($error, 0, 200) . "...\n\n";
        }
    }

    // Look for template errors
    $templateErrors = array_filter($recentLines, function($line) {
        return stripos($line, 'format()') !== false && stripos($line, 'null') !== false;
    });

    if (!empty($templateErrors)) {
        echo "\n⚠ TEMPLATE ERRORS FOUND:\n";
        foreach (array_slice($templateErrors, -3) as $error) {
            echo substr($error, 0, 300) . "...\n\n";
        }
    }
}
echo "\n";

// Check queue jobs
echo "4. CHECKING QUEUE JOBS\n";
echo "-----------------------\n";
try {
    $jobs = DB::table('jobs')
        ->where('queue', 'default')
        ->count();
    echo "Pending jobs in queue: {$jobs}\n";

    if ($jobs > 0) {
        echo "⚠ There are jobs waiting to be processed.\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "========================================\n";
echo "  DIAGNOSIS\n";
echo "========================================\n\n";

if ($prep && $prep->status === 'sent' && $prep->sent_at) {
    // Check if actually sent
    try {
        $tracked = DB::table('tracked_emails')
            ->where('task_id', $prep->task_id)
            ->exists();

        if (!$tracked) {
            echo "❌ PROBLEM IDENTIFIED:\n";
            echo "   The email preparation is marked as 'sent' but\n";
            echo "   no tracked email exists in the database.\n\n";
            echo "   This means the email job FAILED after marking\n";
            echo "   it as sent, likely due to a template error.\n\n";
            echo "🔧 FIX: Check the task dates - the template is trying\n";
            echo "   to format a NULL date field.\n";
        } else {
            echo "✅ Email was tracked - it should have been sent.\n";
            echo "   Check the recipient's inbox (including spam).\n";
        }
    } catch (\Exception $e) {
        echo "Unable to verify: " . $e->getMessage() . "\n";
    }
}

echo "\n";

