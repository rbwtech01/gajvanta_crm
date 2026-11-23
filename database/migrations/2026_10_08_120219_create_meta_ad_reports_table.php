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
        Schema::create('meta_ad_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->date('report_date');

            $table->string('campaign_name');

            $table->decimal('budget', 12, 2)
                ->default(0);

            $table->decimal('amount_spent', 12, 2)
                ->default(0);

            $table->timestamps();

            $table->index([
                'report_date',
                'agent_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_ad_reports');
    }
};