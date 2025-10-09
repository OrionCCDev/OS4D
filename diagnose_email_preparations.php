<?php

/**
 * Email Preparation Diagnostic Script
 *
 * This script checks the status of task email preparations and attachments
 * Run this from the command line: php diagnose_email_preparations.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaskEmailPreparation;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "  EMAIL PREPARATION DIAGNOSTIC REPORT  \n";
echo "========================================\n\n";

// 1. Check for database schema
echo "1. CHECKING DATABASE SCHEMA\n";
echo "----------------------------\n";
try {
    $hasCC = Schema::hasColumn('emails', 'cc');
    echo "✓ 'cc' column in 'emails' table: " . ($hasCC ? "EXISTS" : "MISSING") . "\n";

    if (!$hasCC) {
        echo "  ⚠ WARNING: Missing 'cc' column will cause email fetch failures!\n";
        echo "  ⚡ FIX: Run 'php artisan migrate' to add the column\n";
    }
} catch (\Exception $e) {
    echo "✗ Error checking schema: " . $e->getMessage() . "\n";
}
echo "\n";

// 2. Check recent email preparations
echo "2. RECENT EMAIL PREPARATIONS (Last 10)\n";
echo "---------------------------------------\n";
try {
    $preparations = TaskEmailPreparation::with('task')
        ->latest()
        ->take(10)
        ->get();

    if ($preparations->isEmpty()) {
        echo "⚠ No email preparations found in database.\n";
    } else {
        foreach ($preparations as $prep) {
            echo "ID: {$prep->id} | Task: {$prep->task_id} | Status: {$prep->status}\n";
            echo "  To: {$prep->to_emails}\n";
            echo "  Subject: {$prep->subject}\n";
            echo "  Created: {$prep->created_at}\n";
            echo "  Sent At: " . ($prep->sent_at ?? 'Not sent') . "\n";

            if ($prep->attachments && is_array($prep->attachments)) {
                echo "  Attachments: " . count($prep->attachments) . " file(s)\n";
                foreach ($prep->attachments as $path) {
                    $fullPath = storage_path('app/' . $path);
                    $exists = file_exists($fullPath);
                    $size = $exists ? filesize($fullPath) : 0;
                    echo "    - " . basename($path) . " | ";
                    echo ($exists ? "EXISTS" : "MISSING") . " | ";
                    echo ($exists ? number_format($size / 1024, 2) . " KB" : "N/A") . "\n";
                }
            } else {
                echo "  Attachments: None\n";
            }
            echo "\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ Error fetching email preparations: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Check email preparation statistics
echo "3. EMAIL PREPARATION STATISTICS\n";
echo "--------------------------------\n";
try {
    $stats = DB::table('task_email_preparations')
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

    foreach ($stats as $stat) {
        echo "  {$stat->status}: {$stat->count}\n";
    }

    $withAttachments = TaskEmailPreparation::whereNotNull('attachments')
        ->where('attachments', '!=', '[]')
        ->count();
    echo "  With Attachments: {$withAttachments}\n";
} catch (\Exception $e) {
    echo "✗ Error getting statistics: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Check storage directory
echo "4. STORAGE DIRECTORY CHECK\n";
echo "---------------------------\n";
try {
    $storagePath = storage_path('app/email-attachments');
    if (is_dir($storagePath)) {
        echo "✓ Directory exists: {$storagePath}\n";
        echo "  Readable: " . (is_readable($storagePath) ? "YES" : "NO") . "\n";
        echo "  Writable: " . (is_writable($storagePath) ? "YES" : "NO") . "\n";

        $files = glob($storagePath . '/*');
        echo "  Total files: " . count($files) . "\n";

        if (count($files) > 0) {
            $totalSize = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                }
            }
            echo "  Total size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";
        }
    } else {
        echo "✗ Directory does not exist: {$storagePath}\n";
    }
} catch (\Exception $e) {
    echo "✗ Error checking storage: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Check for failed jobs
echo "5. FAILED QUEUE JOBS CHECK\n";
echo "---------------------------\n";
try {
    $failedJobs = DB::table('failed_jobs')
        ->where('payload', 'like', '%SendTaskConfirmationEmailJob%')
        ->latest()
        ->take(5)
        ->get();

    if ($failedJobs->isEmpty()) {
        echo "✓ No failed email jobs found.\n";
    } else {
        echo "⚠ Found {$failedJobs->count()} failed email jobs:\n";
        foreach ($failedJobs as $job) {
            echo "  ID: {$job->id}\n";
            echo "  Failed at: {$job->failed_at}\n";
            echo "  Exception: " . substr($job->exception, 0, 200) . "...\n\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ Error checking failed jobs: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Check recent Laravel logs for email errors
echo "6. RECENT EMAIL ERRORS IN LOGS\n";
echo "-------------------------------\n";
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -500); // Last 500 lines

        $emailErrors = array_filter($recentLines, function($line) {
            return stripos($line, 'email') !== false &&
                   (stripos($line, 'error') !== false ||
                    stripos($line, 'failed') !== false ||
                    stripos($line, 'attachment') !== false);
        });

        if (empty($emailErrors)) {
            echo "✓ No recent email errors found in logs.\n";
        } else {
            echo "⚠ Found " . count($emailErrors) . " email-related errors (showing last 10):\n";
            $lastErrors = array_slice($emailErrors, -10);
            foreach ($lastErrors as $error) {
                echo "  " . substr($error, 0, 150) . "...\n";
            }
        }
    } else {
        echo "⚠ Log file not found: {$logFile}\n";
    }
} catch (\Exception $e) {
    echo "✗ Error reading logs: " . $e->getMessage() . "\n";
}
echo "\n";

echo "========================================\n";
echo "  END OF DIAGNOSTIC REPORT\n";
echo "========================================\n\n";

echo "💡 RECOMMENDATIONS:\n";
echo "-------------------\n";

// Check if cc column is missing
try {
    $hasCC = Schema::hasColumn('emails', 'cc');
    if (!$hasCC) {
        echo "1. ⚡ URGENT: Run 'php artisan migrate' to add missing 'cc' column\n";
    }
} catch (\Exception $e) {
    // Ignore
}

// Check for draft preparations
$draftCount = TaskEmailPreparation::where('status', 'draft')->count();
if ($draftCount > 0) {
    echo "2. 📧 You have {$draftCount} draft email(s) waiting to be sent\n";
}

// Check for processing preparations
$processingCount = TaskEmailPreparation::where('status', 'processing')->count();
if ($processingCount > 0) {
    echo "3. ⏳ You have {$processingCount} email(s) currently processing\n";
}

// Check queue worker
exec('ps aux | grep "queue:work" | grep -v grep', $output);
if (empty($output)) {
    echo "4. ⚠ Queue worker is NOT running. Start it with: php artisan queue:work --daemon\n";
} else {
    echo "4. ✓ Queue worker is running\n";
}

echo "\n";

