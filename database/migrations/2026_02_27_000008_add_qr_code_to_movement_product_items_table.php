<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movement_product_items', function (Blueprint $table) {
            $table->text('qr_code')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('movement_product_items', function (Blueprint $table) {
            $table->dropColumn('qr_code');
        });
    }
};
