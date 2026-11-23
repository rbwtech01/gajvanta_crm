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
            $table->foreignId('hold_reason_id')
                ->nullable()
                ->constrained('hold_reasons')
                ->nullOnDelete();

            $table->text('hold_remark')->nullable();
            $table->string('bill_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['hold_reason_id']);
            $table->dropColumn(['hold_reason_id', 'hold_remark']);
        });
    }
};
