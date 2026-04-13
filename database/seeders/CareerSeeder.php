<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $careersByType = \App\Services\MbtiCareersService::CAREERS_BY_TYPE;

        foreach ($careersByType as $mbtiType => $careers) {
            foreach ($careers as $careerData) {
                $career = \App\Models\Career::firstOrCreate(
                    ['title' => $careerData['title']],
                    [
                        'description' => $careerData['description'],
                    ]
                );

                // Insert into career_mbti pivot
                \Illuminate\Support\Facades\DB::table('career_mbti')->updateOrInsert(
                    [
                        'career_id' => $career->id,
                        'mbti_type' => $mbtiType,
                    ],
                    [
                        'match_reason' => $careerData['match_reason'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Insert sectors
                if (isset($careerData['sectors'])) {
                    foreach ($careerData['sectors'] as $sectorCode) {
                        \Illuminate\Support\Facades\DB::table('career_sector')->updateOrInsert(
                            [
                                'career_id' => $career->id,
                                'sector_code' => $sectorCode,
                            ],
                            [
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        }
    }
}
