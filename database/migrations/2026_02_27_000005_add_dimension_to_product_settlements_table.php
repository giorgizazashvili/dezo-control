<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_settlements', function (Blueprint $table) {
            $table->foreignId('dimension_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_settlements', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Dimension::class);
            $table->dropColumn('dimension_id');
        });
    }
};
