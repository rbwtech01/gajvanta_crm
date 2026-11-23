<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PackingUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'packing@rbw.com',
            ],
            [
                'name' => 'Packing User',
                'employee_code' => 'Packing-001', 
                'password' => Hash::make('RBWpacking@9519'),
                'phone' => '8888888888',
                'is_active' => true,
            ]
        );

        $user->assignRole('Packing');
    }
}