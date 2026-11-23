<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ShipmentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'shipment@rbw.com',
            ],
            [
                'name' => 'Shipment User',
                'employee_code' => 'Shipment-001',
                'password' => Hash::make('RBWshipment@3422'),
                'phone' => '8888888888',
                'is_active' => true,
            ]
        );

        $user->assignRole('Shipment');
    }
}