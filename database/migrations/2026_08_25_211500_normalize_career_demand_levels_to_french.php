<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $lowerDemandLevel = DB::raw('LOWER(demand_level)');

        DB::table('careers')
            ->whereIn($lowerDemandLevel, ['high', 'high_demand', 'high demand', 'haute', 'forte'])
            ->update(['demand_level' => 'Élevée']);

        DB::table('careers')
            ->whereIn($lowerDemandLevel, ['medium', 'medium_demand', 'medium demand'])
            ->update(['demand_level' => 'Moyenne']);

        DB::table('careers')
            ->whereIn($lowerDemandLevel, ['low', 'low_demand', 'low demand'])
            ->update(['demand_level' => 'Faible']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non réversible car données normalisées
    }
};
