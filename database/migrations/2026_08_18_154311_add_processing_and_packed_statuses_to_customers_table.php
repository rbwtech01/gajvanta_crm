<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE customers
            MODIFY COLUMN status
            ENUM(
                'created',
                'hold',
                'dispatched',
                'delivered',
                'processing',
                'packed'
            )
            NOT NULL
            DEFAULT 'created'
        ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE customers
            MODIFY COLUMN status
            ENUM(
                'created',
                'hold',
                'dispatched',
                'delivered'
            )
            NOT NULL
            DEFAULT 'created'
        ");
        });
    }
};
