<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperationsManagerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'manager@gmail.com',
            ],
            [
                'name' => 'Operations Manager',
                'employee_code' => 'Manager-001',
                'password' => Hash::make('manager@123'),
                'phone' => '8888888888',
                'is_active' => true,
            ]
        );

        $user->assignRole('Operations Manager');
    }
}