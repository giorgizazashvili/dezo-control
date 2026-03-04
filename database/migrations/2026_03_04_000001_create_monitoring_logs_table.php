<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('movement_product_item_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['inspection', 'replacement']);
            $table->unsignedBigInteger('settlement_component_id')->nullable();
            $table->foreign('settlement_component_id', 'ml_settlement_component_fk')
                ->references('id')->on('settlement_components')->nullOnDelete();
            $table->decimal('quantity', 12, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_logs');
    }
};
