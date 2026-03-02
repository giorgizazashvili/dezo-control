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
        Schema::table('monitoring_component_replacements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_component_replacements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 4)->nullable(false)->change();
        });
    }
};
