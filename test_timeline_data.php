<?php
/**
 * Test script to debug TimelineJS data issues
 * Run this script to check database connection and task data
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Task;
use Carbon\Carbon;

echo "=== TimelineJS Data Debug Script ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "   Please check your .env file and database server\n\n";
    exit(1);
}

// Test 2: Check if tasks table exists
echo "\n2. Checking Tasks Table...\n";
try {
    $taskCount = Task::count();
    echo "✅ Tasks table accessible. Total tasks: $taskCount\n";
} catch (Exception $e) {
    echo "❌ Tasks table error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check task data structure
echo "\n3. Analyzing Task Data Structure...\n";
$sampleTask = Task::first();
if ($sampleTask) {
    echo "✅ Sample task found:\n";
    echo "   - ID: " . $sampleTask->id . "\n";
    echo "   - Title: " . $sampleTask->title . "\n";
    echo "   - Status: " . $sampleTask->status . "\n";
    echo "   - Priority: " . $sampleTask->priority . "\n";
    echo "   - Start Date: " . ($sampleTask->start_date ?? 'NULL') . "\n";
    echo "   - Due Date: " . ($sampleTask->due_date ?? 'NULL') . "\n";
    echo "   - Assigned To: " . ($sampleTask->assigned_to ?? 'NULL') . "\n";
    echo "   - Project ID: " . ($sampleTask->project_id ?? 'NULL') . "\n";
} else {
    echo "⚠️  No tasks found in database\n";
}

// Test 4: Check tasks with dates
echo "\n4. Checking Tasks with Dates...\n";
$now = now();
$endDate = $now->copy()->addDays(20);

$tasksWithStartDate = Task::whereNotNull('start_date')->count();
$tasksWithDueDate = Task::whereNotNull('due_date')->count();
$tasksInRange = Task::where(function($query) use ($now, $endDate) {
    $query->where(function($q) use ($now, $endDate) {
        $q->whereNotNull('start_date')
          ->whereBetween('start_date', [$now->format('Y-m-d'), $endDate->format('Y-m-d')]);
    })->orWhere(function($q) use ($now, $endDate) {
        $q->whereNotNull('due_date')
          ->whereBetween('due_date', [$now->format('Y-m-d'), $endDate->format('Y-m-d')]);
    });
})->count();

echo "   - Tasks with start_date: $tasksWithStartDate\n";
echo "   - Tasks with due_date: $tasksWithDueDate\n";
echo "   - Tasks in next 20 days: $tasksInRange\n";

// Test 5: Create sample data if none exists
if ($taskCount === 0) {
    echo "\n5. Creating Sample Tasks...\n";
    try {
        // Create sample tasks for testing
        $sampleTasks = [
            [
                'title' => 'Sample Task 1 - Website Design',
                'description' => 'Create responsive website design for client project',
                'start_date' => $now->addDays(1)->format('Y-m-d'),
                'due_date' => $now->addDays(5)->format('Y-m-d'),
                'status' => 'pending',
                'priority' => 2,
                'project_id' => 1,
                'created_by' => 1,
            ],
            [
                'title' => 'Sample Task 2 - Content Review',
                'description' => 'Review and approve content for marketing campaign',
                'start_date' => $now->addDays(3)->format('Y-m-d'),
                'due_date' => $now->addDays(7)->format('Y-m-d'),
                'status' => 'assigned',
                'priority' => 3,
                'project_id' => 1,
                'created_by' => 1,
                'assigned_to' => 1,
            ],
            [
                'title' => 'Sample Task 3 - Database Migration',
                'description' => 'Migrate user data to new database structure',
                'start_date' => $now->addDays(10)->format('Y-m-d'),
                'due_date' => $now->addDays(15)->format('Y-m-d'),
                'status' => 'in_progress',
                'priority' => 1,
                'project_id' => 1,
                'created_by' => 1,
                'assigned_to' => 1,
            ]
        ];

        foreach ($sampleTasks as $taskData) {
            Task::create($taskData);
        }

        echo "✅ Created 3 sample tasks for testing\n";
        echo "   - Task 1: Website Design (Due in 5 days)\n";
        echo "   - Task 2: Content Review (Due in 7 days)\n";
        echo "   - Task 3: Database Migration (Due in 15 days)\n";

    } catch (Exception $e) {
        echo "❌ Failed to create sample tasks: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Debug Complete ===\n";
echo "If you're still seeing no data in TimelineJS, check:\n";
echo "1. Database server is running\n";
echo "2. .env file has correct database credentials\n";
echo "3. Tasks have start_date or due_date set\n";
echo "4. Tasks are within the next 20 days\n";
echo "5. Browser console for JavaScript errors\n";
