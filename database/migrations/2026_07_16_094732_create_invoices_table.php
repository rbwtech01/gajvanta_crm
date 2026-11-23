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
        Schema::create('invoices', function (Blueprint $table) {

            $table->id();

            // Invoice
            $table->string('invoice_no')->unique();

            // Relations
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();

            // Customer Snapshot
            $table->string('customer_name');
            $table->string('mobile');
            $table->text('address');
            $table->string('district');
            $table->string('state');
            $table->string('pincode');

            // Product Snapshot
            $table->string('category');
            $table->string('machine_model');

            // Attachment names
            $table->json('attachments')->nullable();

            // Financial
            $table->decimal('price', 10, 2);
            $table->decimal('advance_payment', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);

            $table->timestamp('generated_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
