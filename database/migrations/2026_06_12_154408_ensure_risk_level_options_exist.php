<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $options = ['დაბალი / Low', 'საშუალო / Medium', 'მაღალი / High'];

        foreach ($options as $name) {
            DB::table('monitoring_options')->updateOrInsert(
                ['type' => 'risk_level', 'name' => $name],
                ['type' => 'risk_level', 'name' => $name, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void {}
};
