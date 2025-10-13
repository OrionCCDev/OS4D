<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\UnifiedNotification;
use Illuminate\Support\Facades\DB;

echo "=== NOTIFICATION SYSTEM CHECK ===\n\n";

// 1. Check database connection
echo "STEP 1: Checking database connection...\n";
try {
    $count = DB::table('unified_notifications')->count();
    echo "✅ Database connected. Total notifications: {$count}\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// 2. Check users and roles
echo "\nSTEP 2: Checking users and roles...\n";
$allUsers = User::all();
echo "Total users: " . $allUsers->count() . "\n";

$managers = User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();
echo "Managers (admin/manager/sub-admin): " . $managers->count() . "\n";

foreach ($managers as $manager) {
    echo "- {$manager->name} ({$manager->email}) - Role: {$manager->role}\n";
}

// 3. Check notification table structure
echo "\nSTEP 3: Checking notification table structure...\n";
try {
    $columns = DB::select("DESCRIBE unified_notifications");
    echo "Notification table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "\n";
}

// 4. Check recent notifications
echo "\nSTEP 4: Checking recent notifications...\n";
$recentNotifications = UnifiedNotification::orderBy('created_at', 'desc')->take(10)->get();
echo "Recent notifications (last 10):\n";

foreach ($recentNotifications as $notification) {
    $user = User::find($notification->user_id);
    echo "- ID: {$notification->id} | User: " . ($user ? $user->name : 'Unknown') . " | Category: {$notification->category} | Type: {$notification->type}\n";
    echo "  Title: {$notification->title}\n";
    echo "  Message: " . substr($notification->message, 0, 100) . "...\n";
    echo "  Created: {$notification->created_at} | Read: " . ($notification->is_read ? 'Yes' : 'No') . "\n\n";
}

// 5. Check for email-related notifications
echo "\nSTEP 5: Checking for email-related notifications...\n";
$emailNotifications = UnifiedNotification::where('type', 'like', '%email%')->get();
echo "Email-related notifications: " . $emailNotifications->count() . "\n";

foreach ($emailNotifications as $notification) {
    $user = User::find($notification->user_id);
    echo "- ID: {$notification->id} | User: " . ($user ? $user->name : 'Unknown') . " | Type: {$notification->type}\n";
    echo "  Title: {$notification->title}\n";
    echo "  Created: {$notification->created_at}\n\n";
}

echo "=== CHECK COMPLETE ===\n";
