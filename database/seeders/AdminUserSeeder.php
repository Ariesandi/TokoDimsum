<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@dimsummamahhaura.com'],
            [
                'name' => 'Admin Dimsum Mamah Haura',
                'email' => 'admin@dimsummamahhaura.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '081234567890',
                'address' => 'Jl. Dimsum No. 1, Jakarta',
                'email_verified_at' => now(),
            ]
        );

        // Create sample customer
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer Test',
                'email' => 'customer@example.com',
                'password' => Hash::make('customer123'),
                'role' => 'customer',
                'phone' => '081234567891',
                'address' => 'Jl. Customer No. 2, Jakarta',
                'email_verified_at' => now(),
            ]
        );
    }
}
