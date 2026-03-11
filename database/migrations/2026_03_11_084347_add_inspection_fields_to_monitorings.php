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
            $table->string('pest_type')->nullable()->after('notes');
            $table->decimal('pest_quantity', 12, 4)->nullable()->after('pest_type');
            $table->string('bait_status')->nullable()->after('pest_quantity');
            $table->text('action_taken')->nullable()->after('bait_status');
            $table->text('inspection_note')->nullable()->after('action_taken');
        });
    }

    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropColumn(['pest_type', 'pest_quantity', 'bait_status', 'action_taken', 'inspection_note']);
        });
    }
};
