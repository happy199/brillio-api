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
        DB::table('careers')
            ->whereIn(DB::raw('LOWER(demand_level)'), ['high', 'high_demand', 'high demand', 'haute', 'forte'])
            ->update(['demand_level' => 'Élevée']);

        DB::table('careers')
            ->whereIn(DB::raw('LOWER(demand_level)'), ['medium', 'medium_demand', 'medium demand'])
            ->update(['demand_level' => 'Moyenne']);

        DB::table('careers')
            ->whereIn(DB::raw('LOWER(demand_level)'), ['low', 'low_demand', 'low demand'])
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
