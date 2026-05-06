<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->string('risk_level')->nullable()->after('inspection_note');
        });

        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->string('risk_level')->nullable()->after('inspection_note');
        });
    }

    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropColumn('risk_level');
        });

        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->dropColumn('risk_level');
        });
    }
};
