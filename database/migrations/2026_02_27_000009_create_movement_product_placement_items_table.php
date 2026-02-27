<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movement_product_placement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_settlement_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_product_placement_items');
    }
};
