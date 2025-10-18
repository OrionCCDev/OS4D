<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user directly without factory
        User::create([
            'name' => 'Mohab',
            'email' => 'Mohab@orioncc.com',
            'password' => bcrypt('Mohab@orionManager'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'img' => 'default.png',
        ]);

        // Call the UserSeeder to create additional users
        $this->call(UserSeeder::class);
    }
}
