<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class AiQuizFeatureCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::updateOrCreate(
            ['key' => 'feature_cost_ai_generation'],
            ['value' => '5', 'type' => 'integer']
        );
    }
}
