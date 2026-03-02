<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_component_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('settlement_component_id');
            $table->foreign('settlement_component_id', 'mcr_component_fk')
                ->references('id')
                ->on('settlement_components')
                ->restrictOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_component_replacements');
    }
};
