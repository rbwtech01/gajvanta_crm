<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_ad_reports', function (Blueprint $table) {
            $table->string('campaign_id', 50)
                ->nullable()
                ->after('agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('meta_ad_reports', function (Blueprint $table) {
            $table->dropColumn('campaign_id');
        });
    }
};