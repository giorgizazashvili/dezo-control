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
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('monitoring_id')->constrained()->nullOnDelete();
            $table->string('unique_code')->nullable()->after('notes');
            $table->string('zone')->nullable()->after('unique_code');
            $table->string('location')->nullable()->after('zone');
            $table->string('inspection_status')->nullable()->after('location');
            $table->string('pest_type')->nullable()->after('inspection_status');
            $table->decimal('pest_quantity', 12, 4)->nullable()->after('pest_type');
            $table->string('bait_status')->nullable()->after('pest_quantity');
            $table->text('action_taken')->nullable()->after('bait_status');
            $table->text('inspection_note')->nullable()->after('action_taken');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'unique_code',
                'zone',
                'location',
                'inspection_status',
                'pest_type',
                'pest_quantity',
                'bait_status',
                'action_taken',
                'inspection_note',
            ]);
        });
    }
};
