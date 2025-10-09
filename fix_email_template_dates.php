<?php

/**
 * Fix Email Template Date Issues
 *
 * This script ensures all task date fields are properly set before sending emails
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "  FIX TASK DATE FIELDS FOR EMAILS      \n";
echo "========================================\n\n";

// Find tasks with null date fields that should have values
echo "Checking tasks with problematic date fields...\n\n";

$tasks = Task::whereIn('status', ['ready_for_email', 'on_client_consultant_review', 'completed'])
    ->get();

echo "Found {$tasks->count()} tasks in email-related statuses\n\n";

$fixed = 0;
$issues = 0;

foreach ($tasks as $task) {
    $hasIssue = false;
    $updates = [];

    // Check completed_at for completed tasks
    if ($task->status === 'completed' && !$task->completed_at) {
        $hasIssue = true;
        $updates['completed_at'] = now();
        echo "Task #{$task->id}: Missing completed_at (will set to now)\n";
    }

    // Check started_at for tasks that should have started
    if (in_array($task->status, ['ready_for_email', 'on_client_consultant_review', 'completed']) && !$task->started_at) {
        $hasIssue = true;
        $updates['started_at'] = $task->created_at;
        echo "Task #{$task->id}: Missing started_at (will set to created_at)\n";
    }

    // Check submitted_at for tasks that went through review
    if (in_array($task->status, ['ready_for_email', 'on_client_consultant_review', 'completed']) && !$task->submitted_at) {
        $hasIssue = true;
        $updates['submitted_at'] = $task->created_at;
        echo "Task #{$task->id}: Missing submitted_at (will set to created_at)\n";
    }

    if ($hasIssue) {
        $issues++;
        // Apply updates
        if (!empty($updates)) {
            $task->update($updates);
            $fixed++;
            echo "  ✓ Fixed\n";
        }
    }
}

echo "\n";
echo "Summary:\n";
echo "--------\n";
echo "Tasks checked: {$tasks->count()}\n";
echo "Issues found: {$issues}\n";
echo "Tasks fixed: {$fixed}\n";
echo "\n";

if ($fixed > 0) {
    echo "✅ Date fields have been fixed!\n";
    echo "   You can now try sending confirmation emails again.\n";
} else {
    echo "✅ No date field issues found.\n";
    echo "   The template error might be elsewhere.\n";
}

echo "\n";

