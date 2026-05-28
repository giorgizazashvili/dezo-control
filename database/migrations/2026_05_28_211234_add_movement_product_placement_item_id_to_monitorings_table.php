<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->foreignId('movement_product_placement_item_id')
                ->nullable()
                ->after('movement_product_item_id')
                ->constrained('movement_product_placement_items')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\MovementProductPlacementItem::class);
            $table->dropColumn('movement_product_placement_item_id');
        });
    }
};
