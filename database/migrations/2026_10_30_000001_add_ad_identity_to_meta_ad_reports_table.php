<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_ad_reports', function (Blueprint $table) {
            $table->string('ad_id', 50)->nullable()->after('campaign_id');
            $table->string('ad_name')->nullable()->after('campaign_name');
            $table->index(['ad_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::table('meta_ad_reports', function (Blueprint $table) {
            $table->dropIndex(['ad_id', 'report_date']);
            $table->dropColumn(['ad_id', 'ad_name']);
        });
    }
};
