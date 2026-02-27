<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('source_movement_id')
                ->nullable()
                ->constrained('movements')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['source_movement_id']);
            $table->dropColumn('source_movement_id');
        });
    }
};
