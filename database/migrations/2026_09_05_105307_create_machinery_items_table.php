<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machinery_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('machinery_category_id')
                ->constrained('machinery_categories')
                ->restrictOnDelete();

            $table->string('name');

            // Example: CPW-L, CPW-H, R405
            $table->string('model_code')->unique();

            // Example: Chhota Light, Chhota Heavy, R405 Proking
            $table->string('model_tag')->nullable();

            $table->decimal('dealer_price', 12, 2);

            // Optional commercial fields
            $table->decimal('mrp', 12, 2)->nullable();
            $table->decimal('mop', 12, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machinery_items');
    }
};