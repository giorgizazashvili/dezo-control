<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('monitoring_options')
            ->where('type', 'risk_level')
            ->where('name', 'დაბალი')
            ->update(['name' => 'დაბალი / Low']);

        DB::table('monitorings')
            ->where('risk_level', 'დაბალი')
            ->update(['risk_level' => 'დაბალი / Low']);

        DB::table('monitoring_logs')
            ->where('risk_level', 'დაბალი')
            ->update(['risk_level' => 'დაბალი / Low']);
    }

    public function down(): void
    {
        DB::table('monitoring_options')
            ->where('type', 'risk_level')
            ->where('name', 'დაბალი / Low')
            ->update(['name' => 'დაბალი']);

        DB::table('monitorings')
            ->where('risk_level', 'დაბალი / Low')
            ->update(['risk_level' => 'დაბალი']);

        DB::table('monitoring_logs')
            ->where('risk_level', 'დაბალი / Low')
            ->update(['risk_level' => 'დაბალი']);
    }
};
