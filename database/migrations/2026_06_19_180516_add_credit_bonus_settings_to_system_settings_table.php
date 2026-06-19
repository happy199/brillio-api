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
        // Add credit bonus settings for each plan
        DB::table('system_settings')->insert([
            [
                'key' => 'credit_bonus_pro',
                'value' => '25',
                'type' => 'integer',
                'description' => 'Crédits offerts mensuellement aux organisations Pro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'credit_bonus_enterprise',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Crédits offerts mensuellement aux organisations Enterprise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'credit_bonus_establishment',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Crédits offerts mensuellement aux organisations Établissement',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'credit_bonus_pro',
            'credit_bonus_enterprise',
            'credit_bonus_establishment',
        ])->delete();
    }
};
