<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->string('operation_type'); // component_receipt | product_receipt
            $table->timestamps();
        });

        Schema::create('movement_component_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('settlement_component_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->timestamps();
        });

        Schema::create('movement_product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_settlement_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_product_items');
        Schema::dropIfExists('movement_component_items');
        Schema::dropIfExists('movements');
    }
};
