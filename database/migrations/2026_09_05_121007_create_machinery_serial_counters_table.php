<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_serial_counters', function (Blueprint $table) {
            $table->id();

            // Global next serial number to allocate
            $table->unsignedBigInteger('next_number')
                ->default(261041);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_serial_counters');
    }
};