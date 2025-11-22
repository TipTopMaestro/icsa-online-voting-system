<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@icsa.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create test voter user
        User::create([
            'name' => 'Test Voter',
            'email' => 'voter@icsa.com',
            'password' => Hash::make('password'),
            'role' => 'voter',
        ]);

        // Create test candidate user
        User::create([
            'name' => 'Test Candidate',
            'email' => 'candidate@icsa.com',
            'password' => Hash::make('password'),
            'role' => 'candidate',
        ]);
    }
}
