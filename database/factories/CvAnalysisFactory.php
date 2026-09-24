<?php

namespace Database\Factories;

use App\Models\CvAnalysis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CvAnalysis>
 */
class CvAnalysisFactory extends Factory
{
    protected $model = CvAnalysis::class;

    public function definition(): array
    {
        $score = $this->faker->numberBetween(40, 95);

        return [
            'user_id' => User::factory(),
            'guest_token' => $this->faker->unique()->lexify('????????????????????????????????????????'),
            'original_filename' => $this->faker->word().'.pdf',
            'file_path' => 'cv_analyses/test/'.$this->faker->uuid.'.pdf',
            'file_size' => $this->faker->numberBetween(50000, 2000000),
            'mime_type' => 'application/pdf',
            'candidate_name' => $this->faker->name(),
            'candidate_title' => $this->faker->jobTitle(),
            'candidate_contact' => [
                'email' => $this->faker->email(),
                'phone' => $this->faker->phoneNumber(),
                'location' => $this->faker->city(),
            ],
            'parsed_content' => [
                'raw_text' => $this->faker->paragraph(5),
                'summary' => $this->faker->paragraph(2),
                'experiences' => [
                    [
                        'title' => $this->faker->jobTitle(),
                        'company' => $this->faker->company(),
                        'dates' => '2020-2023',
                        'bullets' => [
                            $this->faker->sentence(),
                            $this->faker->sentence(),
                        ],
                    ],
                ],
                'education' => [
                    [
                        'degree' => 'Master '.$this->faker->word(),
                        'school' => $this->faker->company(),
                        'dates' => '2018-2020',
                    ],
                ],
                'skills' => $this->faker->words(5),
            ],
            'global_score' => $score,
            'status_label' => CvAnalysis::determineStatusLabel($score),
            'criteria_scores' => [
                'structure' => $this->faker->numberBetween(40, 100),
                'clarite' => $this->faker->numberBetween(40, 100),
                'experiences' => $this->faker->numberBetween(40, 100),
                'competences' => $this->faker->numberBetween(40, 100),
                'impact' => $this->faker->numberBetween(40, 100),
            ],
            'strengths' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'improvements' => [
                $this->faker->sentence(),
            ],
            'recommendations' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'summary' => $this->faker->paragraph(3),
            'is_claimed' => true,
        ];
    }

    /**
     * CV d'un utilisateur invité (non connecté)
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_claimed' => false,
        ]);
    }
}
