<?php

namespace Database\Seeders;

use App\Models\PersonalityCompatibility;
use Illuminate\Database\Seeder;

class PersonalityCompatibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pairs = [
            ['INTJ', 'ENFP'],
            ['INTJ', 'ENTP'],
            ['INTP', 'ENTJ'],
            ['INTP', 'ESTJ'],
            ['ENTJ', 'INFP'],
            ['ENTJ', 'INTP'],
            ['ENTP', 'INFJ'],
            ['ENTP', 'INTJ'],
            ['INFJ', 'ENFP'],
            ['INFJ', 'ENTP'],
            ['INFP', 'ENFJ'],
            ['INFP', 'ENTJ'],
            ['ENFJ', 'INFP'],
            ['ENFJ', 'ISFP'],
            ['ENFP', 'INFJ'],
            ['ENFP', 'INTJ'],
            ['ISTJ', 'ESFP'],
            ['ISTJ', 'ESTP'],
            ['ISFJ', 'ESFJ'],
            ['ISFJ', 'ISFP'],
            ['ESTJ', 'ISTP'],
            ['ESTJ', 'ISFP'],
            ['ESFJ', 'ISFJ'],
            ['ESFJ', 'ISTP'],
            ['ISTP', 'ESTJ'],
            ['ISTP', 'ESFJ'],
            ['ISFP', 'ESFJ'],
            ['ISFP', 'ESTJ'],
            ['ESTP', 'ISTJ'],
            ['ESTP', 'ISFJ'],
            ['ESFP', 'ISTJ'],
            ['ESFP', 'ISFJ'],
        ];

        foreach ($pairs as $pair) {
            // Check if reverse exists to avoid duplicates if logical dupes are present in array
            // But DB has unique constraint on [type_a, type_b]. We insert one way.
            // Our model logic handles retrieving both ways.

            // Ensure unique consistent ordering for storage to avoid A-B and B-A dupes
            $sorted = $pair;
            sort($sorted);

            PersonalityCompatibility::firstOrCreate([
                'type_a' => $sorted[0],
                'type_b' => $sorted[1],
            ], [
                'description' => 'Compatibilité naturelle basée sur la complémentarité des fonctions cognitives.'
            ]);
        }
    }
}
