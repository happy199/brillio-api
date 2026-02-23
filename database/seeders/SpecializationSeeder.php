<?php

namespace Database\Seeders;

use App\Models\MentorProfile;
use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Données des spécialisations existantes
        $specializations = [
            [
                'slug' => 'tech',
                'name' => 'Technologie & IT',
                'description' => 'Développement logiciel, cybersécurité, data science, intelligence artificielle',
                'mbti_types' => ['tech'],
            ],
            [
                'slug' => 'finance',
                'name' => 'Finance & Banque',
                'description' => 'Banque, investissement, comptabilité, gestion financière',
                'mbti_types' => ['finance'],
            ],
            [
                'slug' => 'health',
                'name' => 'Santé & Médecine',
                'description' => 'Médecine, soins infirmiers, pharmacie, santé publique',
                'mbti_types' => ['health'],
            ],
            [
                'slug' => 'education',
                'name' => 'Éducation',
                'description' => 'Enseignement, formation, pédagogie, recherche académique',
                'mbti_types' => ['education'],
            ],
            [
                'slug' => 'engineering',
                'name' => 'Ingénierie',
                'description' => 'Génie civil, mécanique, électrique, environnemental',
                'mbti_types' => ['engineering', 'environment'],
            ],
            [
                'slug' => 'business',
                'name' => 'Business & Entrepreneuriat',
                'description' => 'Création d\'entreprise, management, stratégie, marketing',
                'mbti_types' => ['finance', 'communication'],
            ],
            [
                'slug' => 'law',
                'name' => 'Droit',
                'description' => 'Droit des affaires, droit pénal, droit international',
                'mbti_types' => ['law'],
            ],
            [
                'slug' => 'arts',
                'name' => 'Arts & Créativité',
                'description' => 'Design, arts visuels, musique, cinéma, écriture',
                'mbti_types' => ['creative', 'communication'],
            ],
            [
                'slug' => 'science',
                'name' => 'Sciences',
                'description' => 'Biologie, chimie, physique, mathématiques, recherche scientifique',
                'mbti_types' => ['tech', 'health', 'environment'],
            ],
            [
                'slug' => 'agriculture',
                'name' => 'Agriculture',
                'description' => 'Agriculture durable, agronomie, élevage, agroalimentaire',
                'mbti_types' => ['environment'],
            ],
            [
                'slug' => 'other',
                'name' => 'Autre',
                'description' => 'Autres domaines d\'expertise',
                'mbti_types' => ['social'],
            ],
        ];

        foreach ($specializations as $data) {
            $mbtiTypes = $data['mbti_types'];
            unset($data['mbti_types']);

            $specialization = Specialization::updateOrCreate(
                ['slug' => $data['slug']], // Chercher par slug
                [
                    ...$data,
                    'status' => 'active',
                    'created_by_admin' => true,
                ]
            );

            // Lier les types MBTI
            $specialization->syncMbtiTypes($mbtiTypes);
        }

        // Migrer les données existantes des mentor_profiles
        $this->migrateMentorProfiles();
    }

    /**
     * Migrer les spécialisations existantes des mentors
     */
    private function migrateMentorProfiles()
    {
        $mentorProfiles = MentorProfile::whereNotNull('specialization')->get();

        foreach ($mentorProfiles as $profile) {
            $specialization = Specialization::where('slug', $profile->specialization)->first();

            if ($specialization) {
                $profile->specialization_id = $specialization->id;
                $profile->save();

                // Mettre à jour le compteur
                $specialization->updateMentorCount();
            }
        }
    }
}
