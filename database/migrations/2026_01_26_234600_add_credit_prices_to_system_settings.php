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
        // Insert new settings for dual pricing
        DB::table('system_settings')->insertOrIgnore([
            [
                'key' => 'credit_price_jeune',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Prix d\'un crédit pour les JEUNES (FCFA)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'credit_price_mentor',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Prix d\'un crédit pour les MENTORS (FCFA)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // We can optionally remove the old 'credit_price' if we want to force usage of specific ones,
        // but keeping it as a fallback might be safer for now, or we just ignore it.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', ['credit_price_jeune', 'credit_price_mentor'])->delete();
    }
};
