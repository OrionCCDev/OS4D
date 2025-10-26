<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Checking Time Extension Setup...\n\n";

// Check if table exists
$tableExists = Schema::hasTable('task_time_extension_requests');

if ($tableExists) {
    echo "✅ Table 'task_time_extension_requests' exists\n";

    // Check columns
    $columns = Schema::getColumnListing('task_time_extension_requests');
    echo "Columns: " . implode(', ', $columns) . "\n\n";

    // Test model
    try {
        $count = \App\Models\TaskTimeExtensionRequest::count();
        echo "✅ Model works. Total requests: {$count}\n";
    } catch (\Exception $e) {
        echo "❌ Model error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Table 'task_time_extension_requests' does NOT exist\n";
    echo "Please run: php artisan migrate\n";
}

echo "\nDone.\n";
