<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperVisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            [
                'email' => 'supervisor@rbw.com',
            ],
            [
                'name' => 'supervisor',
                'employee_code' => 'Supervisor-001',
                'password' => Hash::make('supervisor_rbw@8624'),
                'phone' => '8888888888',
                'is_active' => true,
            ]
        );

        $user->assignRole('Supervisor');
    }
}
