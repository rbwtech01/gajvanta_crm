<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['machine_model_id']);

            // Recreate with RESTRICT
            $table->foreign('machine_model_id')
                ->references('id')
                ->on('machine_models')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop RESTRICT foreign key
            $table->dropForeign(['machine_model_id']);

            // Recreate with CASCADE
            $table->foreign('machine_model_id')
                ->references('id')
                ->on('machine_models')
                ->cascadeOnDelete();
        });
    }
};