<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('replaced_settlement_component_id')->nullable()->after('settlement_component_id');
            $table->foreign('replaced_settlement_component_id', 'ml_replaced_component_fk')
                ->references('id')->on('settlement_components')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->dropForeign('ml_replaced_component_fk');
            $table->dropColumn('replaced_settlement_component_id');
        });
    }
};
