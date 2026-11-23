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
            $table->foreignId('machine_model_id')
                ->nullable()
                ->after('category_id')
                ->constrained('machine_models')
                ->cascadeOnDelete();

            // Remove product_name
            $table->dropColumn('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
             $table->string('product_name')->nullable()->after('mobile');

            // Remove FK
            $table->dropForeign(['machine_model_id']);
            $table->dropColumn('machine_model_id');
        });
    }
};
