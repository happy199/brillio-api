<?php

namespace Database\Seeders;

use App\Models\PersonalityTest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonalityTestSeeder extends Seeder
{
    /**
     * Crée des tests de personnalité pour certains utilisateurs
     */
    public function run(): void
    {
        // Types de personnalité à distribuer
        $types = [
            'INTJ' => ['label' => 'L\'Architecte', 'description' => 'Les INTJ sont des penseurs stratégiques naturels...'],
            'ENFP' => ['label' => 'Le Campaigner', 'description' => 'Les ENFP sont des esprits libres créatifs et sociables...'],
            'ISTJ' => ['label' => 'Le Logisticien', 'description' => 'Les ISTJ sont des individus fiables et méthodiques...'],
            'INFJ' => ['label' => 'L\'Avocat', 'description' => 'Les INFJ sont des idéalistes empathiques...'],
            'ENTP' => ['label' => 'L\'Innovateur', 'description' => 'Les ENTP sont des innovateurs créatifs...'],
        ];

        // Récupérer les jeunes
        $jeunes = User::where('user_type', 'jeune')->get();

        foreach ($jeunes->take(8) as $index => $jeune) {
            $typeKeys = array_keys($types);
            $selectedType = $typeKeys[$index % count($typeKeys)];
            $typeInfo = $types[$selectedType];

            // Générer des réponses aléatoires simulées
            $responses = [];
            for ($i = 1; $i <= 20; $i++) {
                $responses[$i] = rand(-3, 3);
            }

            // Générer des scores de traits cohérents avec le type
            $traitsScores = $this->generateTraitsForType($selectedType);

            PersonalityTest::create([
                'user_id' => $jeune->id,
                'test_type' => 'openmbti',
                'raw_responses' => $responses,
                'personality_type' => $selectedType,
                'personality_label' => $typeInfo['label'],
                'personality_description' => $typeInfo['description'],
                'traits_scores' => $traitsScores,
                'completed_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('Tests de personnalité créés pour 8 utilisateurs');
    }

    /**
     * Génère des scores de traits cohérents avec le type MBTI
     */
    private function generateTraitsForType(string $type): array
    {
        $traits = [
            'extraversion' => 50,
            'introversion' => 50,
            'sensing' => 50,
            'intuition' => 50,
            'thinking' => 50,
            'feeling' => 50,
            'judging' => 50,
            'perceiving' => 50,
        ];

        // E/I
        if (str_starts_with($type, 'E')) {
            $traits['extraversion'] = rand(60, 85);
            $traits['introversion'] = 100 - $traits['extraversion'];
        } else {
            $traits['introversion'] = rand(60, 85);
            $traits['extraversion'] = 100 - $traits['introversion'];
        }

        // S/N
        if ($type[1] === 'S') {
            $traits['sensing'] = rand(60, 85);
            $traits['intuition'] = 100 - $traits['sensing'];
        } else {
            $traits['intuition'] = rand(60, 85);
            $traits['sensing'] = 100 - $traits['intuition'];
        }

        // T/F
        if ($type[2] === 'T') {
            $traits['thinking'] = rand(60, 85);
            $traits['feeling'] = 100 - $traits['thinking'];
        } else {
            $traits['feeling'] = rand(60, 85);
            $traits['thinking'] = 100 - $traits['feeling'];
        }

        // J/P
        if ($type[3] === 'J') {
            $traits['judging'] = rand(60, 85);
            $traits['perceiving'] = 100 - $traits['judging'];
        } else {
            $traits['perceiving'] = rand(60, 85);
            $traits['judging'] = 100 - $traits['perceiving'];
        }

        return $traits;
    }
}
