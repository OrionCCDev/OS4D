<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'ahmdsyd',
            'email' => 'a.sayed@orioncc.com',
            'password' => Hash::make('THEgh0$t'),
            'role' => 'admin'
        ]);

        // Create regular user
        User::create([
            'name' => 'malekahmd',
            'email' => 'a.sayed.xc@gmail.com',
            'password' => Hash::make('THEgh0$t'),
            'role' => 'user'
        ]);
        
        User::create([
            'name' => 'hahmed',
            'email' => 'h.ahmed.moursy@gmail.com',
            'password' => Hash::make('THEgh0$t'),
            'role' => 'user'
        ]);

        $this->command->info('Users created successfully!');
    }
}
