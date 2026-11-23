<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('machinery_item_id')
                ->constrained('machinery_items')
                ->restrictOnDelete();

            // Numeric sequence generated automatically
            $table->unsignedBigInteger('serial_number');

            // Final/display serial e.g. 261041-CPW-L
            $table->string('serial_code')->unique();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Same number cannot be allocated twice
            // to the same machinery item.
            $table->unique([
                'machinery_item_id',
                'serial_number',
            ]);

            $table->index([
                'machinery_item_id',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_serials');
    }
};