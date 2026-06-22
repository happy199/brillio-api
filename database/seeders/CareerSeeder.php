<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Services\MbtiCareersService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $careersByType = MbtiCareersService::CAREERS_BY_TYPE;

        foreach ($careersByType as $mbtiType => $careers) {
            foreach ($careers as $careerData) {
                $career = Career::firstOrCreate(
                    ['title' => $careerData['title']],
                    [
                        'description' => $careerData['description'],
                    ]
                );

                // Insert into career_mbti pivot
                DB::table('career_mbti')->updateOrInsert(
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
                        DB::table('career_sector')->updateOrInsert(
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
