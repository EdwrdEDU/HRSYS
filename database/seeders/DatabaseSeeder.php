<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create regular user
        User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }
}
