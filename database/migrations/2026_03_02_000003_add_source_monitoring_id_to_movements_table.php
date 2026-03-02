<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('source_monitoring_id')
                ->nullable()
                ->after('source_movement_id')
                ->constrained('monitorings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Monitoring::class, 'source_monitoring_id');
            $table->dropColumn('source_monitoring_id');
        });
    }
};
