<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'full_name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'role' => 'super_admin',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('123456'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'full_name' => 'Provider',
            'email' => 'provider@gmail.com',
            'role' => 'provider',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('123456'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        User::create([
            'full_name' => 'User',
            'email' => 'user@gmail.com',
            'role' => 'user',
            'address' => 'Dhaka, Bangladesh',
            'password' => Hash::make('123456'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
