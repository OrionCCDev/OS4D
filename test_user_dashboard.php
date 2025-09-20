<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Test the user dashboard controller
$user = User::find(2); // Regular user
Auth::login($user);

echo "Testing User Dashboard Controller...\n";
echo "User: " . Auth::user()->name . " (Role: " . Auth::user()->role . ")\n";
echo "Is Manager: " . (Auth::user()->isManager() ? 'Yes' : 'No') . "\n";

try {
    $controller = new App\Http\Controllers\DashboardController();
    $userData = $controller->getUserDashboardData();

    echo "User dashboard data retrieved successfully!\n";
    echo "Data keys: " . implode(', ', array_keys($userData)) . "\n";
    echo "Total tasks: " . $userData['task_stats']['total'] . "\n";
    echo "Completed tasks: " . $userData['task_stats']['completed'] . "\n";
    echo "Completion rate: " . $userData['task_stats']['completion_rate'] . "%\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
