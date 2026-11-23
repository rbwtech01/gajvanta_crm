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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('mobile')->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('advance_payment', 10, 2)->default(0);
            $table->enum('status', [
                'hold',
                'delivered',
                'undelivered',
            ])->default('undelivered');
            $table->string('pincode');
            $table->string('state');
            $table->string('district');
            $table->text('address');
            $table->foreignId('agent_id')
            ->constrained('users')
            ->cascadeOnDelete();
            $table->foreignId('category_id')
            ->constrained()
            ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
