<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AiQuizFeatureCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'feature_cost_ai_generation'],
            ['value' => '5', 'type' => 'integer']
        );
    }
}
