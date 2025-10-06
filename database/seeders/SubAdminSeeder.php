<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SubAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if sub-admin already exists
        $existingSubAdmin = User::where('email', 'superadmin@orion.com')->first();

        if ($existingSubAdmin) {
            $this->command->info('Sub-admin user already exists. Updating role...');
            $existingSubAdmin->update([
                'role' => 'sub-admin',
                'password' => Hash::make('Admin@orion'),
            ]);
            $this->command->info('Sub-admin user updated successfully!');
        } else {
            // Create new sub-admin user
            User::create([
                'name' => 'Sub Admin',
                'email' => 'superadmin@orion.com',
                'password' => Hash::make('Admin@orion'),
                'role' => 'sub-admin',
                'img' => 'default_user.jpg',
                'notification_sound_enabled' => true,
            ]);
            $this->command->info('Sub-admin user created successfully!');
        }
    }
}
