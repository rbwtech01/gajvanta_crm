<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Agent']);
        Role::firstOrCreate(['name' => 'Operations Manager']);
        Role::firstOrCreate(['name' => 'Call Center Admin']);
        Role::firstOrCreate(['name' => 'Packing']);
        Role::firstOrCreate(['name' => 'Shipment']);
        Role::firstOrCreate(['name' => 'Supervisor']);
    }
}
