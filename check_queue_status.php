<?php

/**
 * Queue System Diagnostic Script
 * This script checks the status of your queue system in production
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== QUEUE SYSTEM DIAGNOSTIC ===\n\n";

try {
    // 1. Check Queue Configuration
    echo "1. QUEUE CONFIGURATION\n";
    echo "   Connection: " . config('queue.default') . "\n";
    echo "   Driver: " . config('queue.connections.' . config('queue.default') . '.driver') . "\n";
    echo "   Table: " . config('queue.connections.' . config('queue.default') . '.table', 'jobs') . "\n\n";

    // 2. Check Jobs Table
    echo "2. PENDING JOBS IN QUEUE\n";
    $pendingJobs = DB::table('jobs')->get();
    echo "   Total Pending Jobs: " . $pendingJobs->count() . "\n";

    if ($pendingJobs->count() > 0) {
        echo "   ⚠️  WARNING: You have " . $pendingJobs->count() . " jobs waiting to be processed!\n";
        echo "   This means the queue worker is NOT running!\n\n";

        echo "   First 5 pending jobs:\n";
        foreach ($pendingJobs->take(5) as $job) {
            $payload = json_decode($job->payload, true);
            $displayName = $payload['displayName'] ?? 'Unknown';
            $attempts = $job->attempts;
            $created = date('Y-m-d H:i:s', $job->created_at);
            $waiting = round((time() - $job->created_at) / 60, 1);

            echo "   - Job: {$displayName}\n";
            echo "     Created: {$created} ({$waiting} minutes ago)\n";
            echo "     Attempts: {$attempts}\n\n";
        }
    } else {
        echo "   ✓ No pending jobs (Queue is empty)\n\n";
    }

    // 3. Check Failed Jobs
    echo "3. FAILED JOBS\n";
    $failedJobs = DB::table('failed_jobs')->get();
    echo "   Total Failed Jobs: " . $failedJobs->count() . "\n";

    if ($failedJobs->count() > 0) {
        echo "   ⚠️  WARNING: You have " . $failedJobs->count() . " failed jobs!\n\n";

        echo "   Recent failed jobs:\n";
        foreach ($failedJobs->sortByDesc('failed_at')->take(5) as $failedJob) {
            $payload = json_decode($failedJob->payload, true);
            $displayName = $payload['displayName'] ?? 'Unknown';
            $failed = $failedJob->failed_at;

            echo "   - Job: {$displayName}\n";
            echo "     Failed: {$failed}\n";
            echo "     Exception: " . substr($failedJob->exception, 0, 150) . "...\n\n";
        }
    } else {
        echo "   ✓ No failed jobs\n\n";
    }

    // 4. Check Email Preparations
    echo "4. EMAIL PREPARATIONS STATUS\n";
    $emailPreparations = DB::table('task_email_preparations')
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

    foreach ($emailPreparations as $prep) {
        echo "   {$prep->status}: {$prep->count}\n";
    }

    $stuckInProcessing = DB::table('task_email_preparations')
        ->where('status', 'processing')
        ->where('created_at', '<', now()->subMinutes(10))
        ->count();

    if ($stuckInProcessing > 0) {
        echo "\n   ⚠️  WARNING: {$stuckInProcessing} emails stuck in 'processing' status for >10 minutes!\n";
        echo "   These are likely jobs that failed without being caught.\n";
    }
    echo "\n";

    // 5. Check if Queue Worker Process is Running
    echo "5. QUEUE WORKER PROCESS\n";
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe" 2>NUL | findstr "queue:work"');
    } else {
        // Linux/Unix
        $output = shell_exec('ps aux | grep "queue:work" | grep -v grep');
    }

    if (empty($output)) {
        echo "   ❌ CRITICAL: Queue worker is NOT running!\n";
        echo "   This is the root cause of your email issues.\n\n";
    } else {
        echo "   ✓ Queue worker appears to be running\n";
        echo "   Process: " . trim($output) . "\n\n";
    }

    // 6. Summary and Recommendations
    echo "=== SUMMARY ===\n";

    $issues = [];

    if ($pendingJobs->count() > 0) {
        $issues[] = "Pending jobs in queue need processing";
    }

    if ($failedJobs->count() > 0) {
        $issues[] = "Failed jobs need investigation";
    }

    if ($stuckInProcessing > 0) {
        $issues[] = "Email preparations stuck in processing";
    }

    if (empty($output)) {
        $issues[] = "Queue worker is not running (CRITICAL)";
    }

    if (empty($issues)) {
        echo "✓ Everything looks good!\n";
    } else {
        echo "⚠️  Issues Found:\n";
        foreach ($issues as $issue) {
            echo "   - {$issue}\n";
        }

        echo "\n=== IMMEDIATE ACTIONS REQUIRED ===\n";
        if (empty($output)) {
            echo "1. START THE QUEUE WORKER IMMEDIATELY:\n";
            echo "   Command: php artisan queue:work --daemon --tries=3 --timeout=300\n\n";
            echo "2. Set up a cron job or supervisor to keep it running:\n";
            echo "   See CRON_JOBS_SETUP.md or setup supervisor configuration\n\n";
        }

        if ($stuckInProcessing > 0) {
            echo "3. Reset stuck email preparations:\n";
            echo "   php artisan tinker\n";
            echo "   >>> \\App\\Models\\TaskEmailPreparation::where('status', 'processing')->where('created_at', '<', now()->subMinutes(10))->update(['status' => 'failed']);\n\n";
        }
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

