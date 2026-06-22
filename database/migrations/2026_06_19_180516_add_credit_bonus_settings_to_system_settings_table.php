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
        $plans = [
            'pro' => ['val' => '25', 'label' => 'Pro'],
            'enterprise' => ['val' => '50', 'label' => 'Enterprise'],
            'establishment' => ['val' => '50', 'label' => 'Établissement'],
        ];

        $payload = [];
        foreach ($plans as $key => $data) {
            $payload[] = [
                'key' => "credit_bonus_{$key}",
                'value' => $data['val'],
                'type' => 'integer',
                'description' => "Crédits offerts mensuellement aux organisations {$data['label']}",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('system_settings')->insert($payload);
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
