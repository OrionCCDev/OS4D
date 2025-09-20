<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Test the dashboard controller
$user = User::find(1); // Admin user
Auth::login($user);

echo "Testing Dashboard Controller...\n";
echo "User: " . Auth::user()->name . " (Role: " . Auth::user()->role . ")\n";
echo "Is Manager: " . (Auth::user()->isManager() ? 'Yes' : 'No') . "\n";

try {
    $controller = new App\Http\Controllers\DashboardController();
    $data = $controller->getDashboardData();

    echo "Dashboard data retrieved successfully!\n";
    echo "Total users: " . $data['overview']['total_users'] . "\n";
    echo "Total tasks: " . $data['overview']['total_tasks'] . "\n";
    echo "Total projects: " . $data['overview']['total_projects'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
