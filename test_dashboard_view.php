<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

// Test the dashboard controller
$user = User::find(1); // Admin user
Auth::login($user);

echo "Testing Dashboard View...\n";
echo "User: " . Auth::user()->name . " (Role: " . Auth::user()->role . ")\n";

try {
    $controller = new App\Http\Controllers\DashboardController();
    $data = $controller->getDashboardData();

    echo "Dashboard data retrieved successfully!\n";
    echo "Data keys: " . implode(', ', array_keys($data)) . "\n";

    // Test if we can render the view
    $view = View::make('dashboard.manager', compact('data'));
    $html = $view->render();

    echo "View rendered successfully!\n";
    echo "HTML length: " . strlen($html) . " characters\n";

    // Save HTML to file for inspection
    file_put_contents('dashboard_output.html', $html);
    echo "HTML saved to dashboard_output.html\n";

    // Check if the HTML contains expected content
    if (strpos($html, 'Manager Dashboard') !== false) {
        echo "✓ Manager Dashboard title found\n";
    } else {
        echo "✗ Manager Dashboard title NOT found\n";
    }

    if (strpos($html, 'Total Users') !== false) {
        echo "✓ Total Users section found\n";
    } else {
        echo "✗ Total Users section NOT found\n";
    }

    // Show first 500 characters
    echo "First 500 characters:\n";
    echo substr($html, 0, 500) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
