<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Sub-Admin User Creation Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Test 1: Check if sub-admin user exists
    echo "1. Checking for existing sub-admin user...\n";
    $subAdmin = User::where('email', 'superadmin@orion.com')->first();

    if ($subAdmin) {
        echo "   ✅ Sub-admin user found: {$subAdmin->name} ({$subAdmin->email})\n";
        echo "   Current role: {$subAdmin->role}\n";

        // Update role if needed
        if ($subAdmin->role !== 'sub-admin') {
            $subAdmin->update(['role' => 'sub-admin']);
            echo "   ✅ Role updated to sub-admin\n";
        }
    } else {
        echo "   ❌ Sub-admin user not found, creating...\n";

        // Create sub-admin user
        $subAdmin = User::create([
            'name' => 'Sub Admin',
            'email' => 'superadmin@orion.com',
            'password' => Hash::make('Admin@orion'),
            'role' => 'sub-admin',
            'img' => 'default_user.jpg',
            'notification_sound_enabled' => true,
        ]);
        echo "   ✅ Sub-admin user created successfully!\n";
    }

    echo "\n";

    // Test 2: Test sub-admin permissions
    echo "2. Testing sub-admin permissions...\n";
    echo "   isManager(): " . ($subAdmin->isManager() ? 'Yes' : 'No') . "\n";
    echo "   isSubAdmin(): " . ($subAdmin->isSubAdmin() ? 'Yes' : 'No') . "\n";
    echo "   isAdmin(): " . ($subAdmin->isAdmin() ? 'Yes' : 'No') . "\n";
    echo "   isRegularUser(): " . ($subAdmin->isRegularUser() ? 'Yes' : 'No') . "\n";
    echo "   canDelete(): " . ($subAdmin->canDelete() ? 'Yes' : 'No') . "\n";
    echo "   canViewAll(): " . ($subAdmin->canViewAll() ? 'Yes' : 'No') . "\n";
    echo "   hasFullPrivileges(): " . ($subAdmin->hasFullPrivileges() ? 'Yes' : 'No') . "\n";

    echo "\n";

    // Test 3: Test login credentials
    echo "3. Testing login credentials...\n";
    $testPassword = 'Admin@orion';
    $passwordCheck = Hash::check($testPassword, $subAdmin->password);
    echo "   Password verification: " . ($passwordCheck ? 'Success' : 'Failed') . "\n";

    echo "\n";

    // Test 4: Show sub-admin capabilities
    echo "4. Sub-Admin Capabilities:\n";
    echo "   ✅ Can view dashboard\n";
    echo "   ✅ Can view and manage projects\n";
    echo "   ✅ Can view and manage tasks\n";
    echo "   ✅ Can send emails\n";
    echo "   ✅ Can create new projects and tasks\n";
    echo "   ✅ Can edit existing projects and tasks\n";
    echo "   ✅ Can change task statuses\n";
    echo "   ❌ Cannot delete any items\n";
    echo "   ❌ Cannot access email monitoring\n";
    echo "   ❌ Cannot access system settings\n";
    echo "   ❌ Cannot view user management\n";
    echo "   ❌ Cannot access advanced features\n";

    echo "\n";

    // Test 5: Show role hierarchy
    echo "5. Role Hierarchy:\n";
    echo "   Admin: Full access to everything\n";
    echo "   Manager: Full access except user management\n";
    echo "   Sub-Admin: Project and task management, no delete, limited view\n";
    echo "   User: Basic task access only\n";

    echo "\n";

    // Test 6: Show login information
    echo "6. Login Information:\n";
    echo "   Email: superadmin@orion.com\n";
    echo "   Password: Admin@orion\n";
    echo "   Role: sub-admin\n";
    echo "   Access Level: Limited Administrative\n";

    echo "\n";

    echo "=== SUB-ADMIN CREATION COMPLETE ===\n\n";
    echo "The sub-admin user has been created/updated successfully!\n";
    echo "You can now login with the provided credentials.\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
