<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. დავამატოთ monitoring_session_id nullable-ად მიგრაციისთვის
        Schema::table('monitorings', function (Blueprint $table) {
            $table->foreignId('monitoring_session_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // 2. თითო monitoring-ისთვის შევქმნათ სესია
        DB::table('monitorings')->orderBy('id')->each(function ($monitoring) {
            $sessionId = DB::table('monitoring_sessions')->insertGetId([
                'organization_id' => $monitoring->organization_id,
                'technician' => $monitoring->technician,
                'started_at' => $monitoring->started_at,
                'finished_at' => $monitoring->finished_at,
                'notes' => null,
                'created_at' => $monitoring->created_at,
                'updated_at' => $monitoring->updated_at,
            ]);

            DB::table('monitorings')->where('id', $monitoring->id)->update([
                'monitoring_session_id' => $sessionId,
            ]);
        });

        // 3. NOT NULL-ად გადავიყვანოთ
        Schema::table('monitorings', function (Blueprint $table) {
            $table->foreignId('monitoring_session_id')->nullable(false)->change();
        });

        // 4. ძველი სესიური კოლუმნები წაიშალოს
        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['organization_id', 'technician', 'started_at', 'finished_at']);
        });
    }

    public function down(): void
    {
        Schema::table('monitorings', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('technician')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
        });

        // session-იდან ძველი მონაცემები დავაბრუნოთ
        DB::table('monitorings')->each(function ($monitoring) {
            if (! $monitoring->monitoring_session_id) {
                return;
            }

            $session = DB::table('monitoring_sessions')->find($monitoring->monitoring_session_id);
            if (! $session) {
                return;
            }

            DB::table('monitorings')->where('id', $monitoring->id)->update([
                'organization_id' => $session->organization_id,
                'technician' => $session->technician,
                'started_at' => $session->started_at,
                'finished_at' => $session->finished_at,
            ]);
        });

        Schema::table('monitorings', function (Blueprint $table) {
            $table->dropForeign(['monitoring_session_id']);
            $table->dropColumn('monitoring_session_id');
        });
    }
};
