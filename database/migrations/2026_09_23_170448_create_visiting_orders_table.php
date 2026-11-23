<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visiting_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('visitor_name');

            $table->string('primary_mobile');

            $table->string('secondary_mobile')->nullable();

            $table->string('pincode');

            $table->string('city');

            $table->string('state');

            $table->text('remark');

            $table->date('visit_date');

            // 7-day cooling period expiry
            $table->dateTime('cooling_until')->nullable();

            $table->index('cooling_until');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visiting_orders');
    }
};