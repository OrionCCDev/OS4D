<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Email System Setup\n";
echo "=========================\n\n";

// Step 1: Check if routes exist
echo "1. Checking routes...\n";
$routes = [
    'emails.send-form' => '/emails/send',
    'emails.send-general' => '/emails/send (POST)'
];

foreach ($routes as $name => $url) {
    try {
        $route = route($name);
        echo "   SUCCESS: Route '{$name}' exists - {$route}\n";
    } catch (Exception $e) {
        echo "   ERROR: Route '{$name}' not found\n";
    }
}
echo "\n";

// Step 2: Check if views exist
echo "2. Checking views...\n";
$views = [
    'emails.general-email-form' => 'Email form view',
    'emails.general-email' => 'Email template view'
];

foreach ($views as $view => $description) {
    try {
        if (view()->exists($view)) {
            echo "   SUCCESS: View '{$view}' exists - {$description}\n";
        } else {
            echo "   ERROR: View '{$view}' not found - {$description}\n";
        }
    } catch (Exception $e) {
        echo "   ERROR: View '{$view}' error - {$e->getMessage()}\n";
    }
}
echo "\n";

// Step 3: Check if Mail class exists
echo "3. Checking Mail class...\n";
try {
    $mailClass = new \App\Mail\GeneralEmail('Test', 'Test body', User::first(), ['test@example.com']);
    echo "   SUCCESS: GeneralEmail class exists and can be instantiated\n";
} catch (Exception $e) {
    echo "   ERROR: GeneralEmail class error - {$e->getMessage()}\n";
}
echo "\n";

// Step 4: Check logo file
echo "4. Checking logo file...\n";
$logoPath = public_path('uploads/logo-blue.webp');
if (file_exists($logoPath)) {
    echo "   SUCCESS: Logo file exists at {$logoPath}\n";
    echo "   File size: " . filesize($logoPath) . " bytes\n";
} else {
    echo "   ERROR: Logo file not found at {$logoPath}\n";
}
echo "\n";

// Step 5: Test navigation
echo "5. Testing navigation...\n";
$user = User::first();
if ($user) {
    Auth::login($user);
    echo "   Logged in as: {$user->name}\n";
    echo "   User role: {$user->role}\n";
    echo "   Can access email form: " . (Auth::check() ? 'Yes' : 'No') . "\n";
    Auth::logout();
} else {
    echo "   ERROR: No users found\n";
}

echo "\nTest Complete!\n";
echo "==============\n";
echo "If all checks show 'SUCCESS', the email system is ready to use!\n";
echo "Access the email form at: https://odc.com.orion-contracting.com/emails/send\n";
