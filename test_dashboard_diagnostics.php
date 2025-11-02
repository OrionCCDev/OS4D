<?php
/**
 * Dashboard Diagnostics Test Script
 *
 * Run this script to test dashboard functionality and identify issues
 * Usage: php test_dashboard_diagnostics.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Dashboard Diagnostics Test ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ Database connection successful\n";
    echo "   Database: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Cache System
echo "2. Testing Cache System...\n";
try {
    cache()->put('test_key', 'test_value', 60);
    $value = cache()->get('test_key');
    if ($value === 'test_value') {
        echo "   ✓ Cache system working\n";
        cache()->forget('test_key');
    } else {
        echo "   ✗ Cache system not working properly\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Cache system failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: User Model
echo "3. Testing User Model...\n";
try {
    $userCount = App\Models\User::count();
    echo "   ✓ User model accessible\n";
    echo "   Total users: " . $userCount . "\n";
} catch (\Exception $e) {
    echo "   ✗ User model failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Task Model
echo "4. Testing Task Model...\n";
try {
    $taskCount = App\Models\Task::count();
    echo "   ✓ Task model accessible\n";
    echo "   Total tasks: " . $taskCount . "\n";
} catch (\Exception $e) {
    echo "   ✗ Task model failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Project Model
echo "5. Testing Project Model...\n";
try {
    $projectCount = App\Models\Project::count();
    echo "   ✓ Project model accessible\n";
    echo "   Total projects: " . $projectCount . "\n";
} catch (\Exception $e) {
    echo "   ✗ Project model failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Quarter Calculation
echo "6. Testing Quarter Calculation (for top performers)...\n";
try {
    $now = now();
    $currentQuarter = ceil($now->month / 3);
    $startMonth = (($currentQuarter - 1) * 3) + 1;
    $startDate = $now->copy()->month($startMonth)->startOfMonth();
    $endDate = $startDate->copy()->addMonths(2)->endOfMonth();

    echo "   ✓ Quarter calculation successful\n";
    echo "   Current quarter: Q" . $currentQuarter . "\n";
    echo "   Quarter start: " . $startDate->format('Y-m-d') . "\n";
    echo "   Quarter end: " . $endDate->format('Y-m-d') . "\n";
} catch (\Exception $e) {
    echo "   ✗ Quarter calculation failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 7: Top Performers Query
echo "7. Testing Top Performers Query...\n";
try {
    $now = now();
    $startDate = $now->copy()->startOfMonth();
    $endDate = $now->copy()->endOfMonth();

    $performers = App\Models\User::withCount(['assignedTasks as completed_tasks_count' => function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->where(function($q) use ($startDate, $endDate) {
                      $q->where(function($subQ) use ($startDate, $endDate) {
                          $subQ->whereBetween('completed_at', [$startDate, $endDate]);
                      })
                      ->orWhere(function($subQ) use ($startDate, $endDate) {
                          $subQ->whereNull('completed_at')
                               ->whereBetween('updated_at', [$startDate, $endDate]);
                      });
                  });
        }])
        ->withCount(['assignedTasks as total_tasks_count' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->whereHas('assignedTasks')
        ->orderBy('completed_tasks_count', 'desc')
        ->limit(3)
        ->get();

    echo "   ✓ Top performers query successful\n";
    echo "   Found " . $performers->count() . " performers\n";

    if ($performers->count() > 0) {
        echo "\n   Top performers this month:\n";
        foreach ($performers as $index => $performer) {
            echo "   " . ($index + 1) . ". " . $performer->name . " (" . $performer->completed_tasks_count . " completed)\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Top performers query failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 8: Log File Permissions
echo "8. Testing Log File Permissions...\n";
$logPath = storage_path('logs');
if (is_writable($logPath)) {
    echo "   ✓ Log directory is writable\n";
    echo "   Log path: " . $logPath . "\n";

    // List recent log files
    $logFiles = glob($logPath . '/*.log');
    if (count($logFiles) > 0) {
        echo "   Recent log files:\n";
        $recentFiles = array_slice($logFiles, -3);
        foreach ($recentFiles as $file) {
            echo "   - " . basename($file) . " (" . number_format(filesize($file)) . " bytes)\n";
        }
    }
} else {
    echo "   ✗ Log directory is not writable\n";
}
echo "\n";

// Test 9: Environment Configuration
echo "9. Environment Configuration:\n";
echo "   APP_ENV: " . config('app.env') . "\n";
echo "   APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
echo "   DB_CONNECTION: " . config('database.default') . "\n";
echo "   CACHE_DRIVER: " . config('cache.default') . "\n";
echo "   LOG_CHANNEL: " . config('logging.default') . "\n";
echo "\n";

echo "=== Diagnostics Complete ===\n";
echo "\nIf any tests failed, please check:\n";
echo "1. Database credentials in .env file\n";
echo "2. Cache driver configuration\n";
echo "3. File permissions for storage/logs\n";
echo "4. PHP extensions (PDO, MySQL, etc.)\n";
