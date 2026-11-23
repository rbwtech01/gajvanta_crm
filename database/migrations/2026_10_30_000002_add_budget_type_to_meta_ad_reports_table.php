<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_ad_reports', function (Blueprint $table) {
            $table->string('budget_type', 20)->nullable()->after('budget');
        });
    }

    public function down(): void
    {
        Schema::table('meta_ad_reports', function (Blueprint $table) {
            $table->dropColumn('budget_type');
        });
    }
};
