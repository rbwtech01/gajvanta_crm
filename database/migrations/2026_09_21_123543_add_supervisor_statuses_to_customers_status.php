<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE customers
            MODIFY status ENUM(
                'created',
                'hold',
                'dispatched',
                'delivered',
                'processing',
                'packed',
                'confirmed',
                'supervisor_hold',
                'supervisor_confirmed'
            ) NOT NULL DEFAULT 'created'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE customers
            MODIFY status ENUM(
                'created',
                'hold',
                'dispatched',
                'delivered',
                'processing',
                'packed',
                'confirmed'
            ) NOT NULL DEFAULT 'created'
        ");
    }
};