<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'admin@gmail.com',
            ],
            [
                'name' => 'Admin',
                'employee_code' => 'Admin-001',
                'password' => Hash::make('admin@123'),
                'phone' => "9999999999",
                'is_active' => true,
            ]
        );

        $user->assignRole('Admin');
    }
}