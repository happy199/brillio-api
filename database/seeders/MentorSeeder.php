<?php

namespace Database\Seeders;

use App\Models\MentorProfile;
use App\Models\RoadmapStep;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MentorSeeder extends Seeder
{
    /**
     * Crée 5 mentors avec leurs roadmaps complètes
     */
    public function run(): void
    {
        $mentors = [
            [
                'user' => [
                    'name' => 'Dr. Ousmane Sow',
                    'email' => 'ousmane.sow@mentor.com',
                    'country' => 'Sénégal',
                    'city' => 'Dakar',
                    'linkedin_url' => 'https://linkedin.com/in/ousmanesow',
                ],
                'profile' => [
                    'bio' => 'Ingénieur logiciel senior avec plus de 15 ans d\'expérience dans le développement d\'applications web et mobile. Passionné par le mentorat des jeunes talents africains.',
                    'current_position' => 'CTO',
                    'current_company' => 'TechAfrica Solutions',
                    'years_of_experience' => 15,
                    'specialization' => 'tech',
                    'is_published' => true,
                ],
                'roadmap' => [
                    [
                        'step_type' => 'education',
                        'title' => 'Baccalauréat Scientifique',
                        'institution_company' => 'Lycée Lamine Guèye',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2001-09-01',
                        'end_date' => '2004-07-01',
                        'description' => 'Mention Très Bien avec spécialisation en mathématiques',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Licence Informatique',
                        'institution_company' => 'Université Cheikh Anta Diop',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2004-10-01',
                        'end_date' => '2007-06-01',
                        'description' => 'Formation en génie logiciel et systèmes d\'information',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Master Informatique',
                        'institution_company' => 'Université Paris-Saclay',
                        'location' => 'Paris, France',
                        'start_date' => '2007-09-01',
                        'end_date' => '2009-06-01',
                        'description' => 'Spécialisation en architecture logicielle et systèmes distribués',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Développeur Junior',
                        'institution_company' => 'Capgemini',
                        'location' => 'Paris, France',
                        'start_date' => '2009-09-01',
                        'end_date' => '2012-08-01',
                        'description' => 'Développement d\'applications Java/J2EE pour clients grands comptes',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Tech Lead',
                        'institution_company' => 'Orange Digital Center',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2012-09-01',
                        'end_date' => '2018-12-01',
                        'description' => 'Direction technique d\'équipes de développement, mentorat de développeurs juniors',
                    ],
                    [
                        'step_type' => 'achievement',
                        'title' => 'Fondation de TechAfrica Solutions',
                        'start_date' => '2019-01-01',
                        'description' => 'Création d\'une startup spécialisée dans les solutions fintech pour l\'Afrique de l\'Ouest',
                    ],
                ],
            ],
            [
                'user' => [
                    'name' => 'Awa Niang',
                    'email' => 'awa.niang@mentor.com',
                    'country' => 'Sénégal',
                    'city' => 'Dakar',
                    'linkedin_url' => 'https://linkedin.com/in/awaniang',
                ],
                'profile' => [
                    'bio' => 'Directrice financière avec 12 ans d\'expérience dans le secteur bancaire africain. Engagée pour l\'inclusion financière et l\'autonomisation des femmes.',
                    'current_position' => 'Directrice Financière',
                    'current_company' => 'Banque Atlantique',
                    'years_of_experience' => 12,
                    'specialization' => 'finance',
                    'is_published' => true,
                ],
                'roadmap' => [
                    [
                        'step_type' => 'education',
                        'title' => 'Baccalauréat Économique',
                        'institution_company' => 'Lycée John F. Kennedy',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2003-09-01',
                        'end_date' => '2006-07-01',
                        'description' => 'Mention Bien',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Bachelor Finance',
                        'institution_company' => 'ISM Dakar',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2006-10-01',
                        'end_date' => '2009-06-01',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'MBA Finance',
                        'institution_company' => 'HEC Montréal',
                        'location' => 'Montréal, Canada',
                        'start_date' => '2009-09-01',
                        'end_date' => '2011-06-01',
                        'description' => 'Spécialisation en finance internationale',
                    ],
                    [
                        'step_type' => 'certification',
                        'title' => 'CFA Level III',
                        'institution_company' => 'CFA Institute',
                        'start_date' => '2013-06-01',
                        'end_date' => '2013-06-01',
                        'description' => 'Certification d\'analyste financier',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Analyste Financière',
                        'institution_company' => 'BCEAO',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2011-09-01',
                        'end_date' => '2016-08-01',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Directrice Financière',
                        'institution_company' => 'Banque Atlantique',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2016-09-01',
                        'description' => 'Gestion financière stratégique et développement de produits innovants',
                    ],
                ],
            ],
            [
                'user' => [
                    'name' => 'Koffi Adjoumani',
                    'email' => 'koffi.adjoumani@mentor.com',
                    'country' => 'Côte d\'Ivoire',
                    'city' => 'Abidjan',
                    'linkedin_url' => 'https://linkedin.com/in/koffiadjoumani',
                ],
                'profile' => [
                    'bio' => 'Médecin urgentiste et entrepreneur social. Fondateur d\'une ONG qui améliore l\'accès aux soins dans les zones rurales d\'Afrique de l\'Ouest.',
                    'current_position' => 'Chef du Service Urgences & Fondateur',
                    'current_company' => 'CHU Yopougon / SantéPourTous',
                    'years_of_experience' => 10,
                    'specialization' => 'health',
                    'is_published' => true,
                ],
                'roadmap' => [
                    [
                        'step_type' => 'education',
                        'title' => 'Baccalauréat Scientifique',
                        'institution_company' => 'Lycée Sainte-Marie',
                        'location' => 'Abidjan, Côte d\'Ivoire',
                        'start_date' => '2004-09-01',
                        'end_date' => '2007-07-01',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Doctorat en Médecine',
                        'institution_company' => 'Université Félix Houphouët-Boigny',
                        'location' => 'Abidjan, Côte d\'Ivoire',
                        'start_date' => '2007-10-01',
                        'end_date' => '2014-06-01',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Spécialisation Médecine d\'Urgence',
                        'institution_company' => 'Hôpitaux de Paris',
                        'location' => 'Paris, France',
                        'start_date' => '2014-09-01',
                        'end_date' => '2017-06-01',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Médecin Urgentiste',
                        'institution_company' => 'CHU Yopougon',
                        'location' => 'Abidjan, Côte d\'Ivoire',
                        'start_date' => '2017-09-01',
                        'description' => 'Prise en charge des urgences médicales et traumatologiques',
                    ],
                    [
                        'step_type' => 'achievement',
                        'title' => 'Fondation ONG SantéPourTous',
                        'start_date' => '2020-03-01',
                        'description' => 'Création d\'une ONG pour améliorer l\'accès aux soins en zone rurale. Plus de 50 000 patients traités.',
                    ],
                ],
            ],
            [
                'user' => [
                    'name' => 'Mariam Diop',
                    'email' => 'mariam.diop@mentor.com',
                    'country' => 'Sénégal',
                    'city' => 'Dakar',
                    'linkedin_url' => 'https://linkedin.com/in/mariamdiop',
                ],
                'profile' => [
                    'bio' => 'Avocate d\'affaires internationale spécialisée en droit des investissements et arbitrage. Première femme associée d\'un grand cabinet au Sénégal.',
                    'current_position' => 'Associée',
                    'current_company' => 'DLA Piper Africa',
                    'years_of_experience' => 14,
                    'specialization' => 'law',
                    'is_published' => true,
                ],
                'roadmap' => [
                    [
                        'step_type' => 'education',
                        'title' => 'Baccalauréat Littéraire',
                        'institution_company' => 'Institution Sainte Jeanne d\'Arc',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2000-09-01',
                        'end_date' => '2003-07-01',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Licence en Droit',
                        'institution_company' => 'Université Cheikh Anta Diop',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2003-10-01',
                        'end_date' => '2006-06-01',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Master Droit des Affaires',
                        'institution_company' => 'Sciences Po Paris',
                        'location' => 'Paris, France',
                        'start_date' => '2006-09-01',
                        'end_date' => '2008-06-01',
                    ],
                    [
                        'step_type' => 'certification',
                        'title' => 'Barreau de Paris',
                        'institution_company' => 'Ordre des Avocats de Paris',
                        'start_date' => '2010-01-01',
                        'end_date' => '2010-01-01',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Avocate Collaboratrice',
                        'institution_company' => 'Clifford Chance',
                        'location' => 'Paris, France',
                        'start_date' => '2010-01-01',
                        'end_date' => '2015-08-01',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Associée',
                        'institution_company' => 'DLA Piper Africa',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2015-09-01',
                        'description' => 'Direction du département arbitrage et investissements',
                    ],
                ],
            ],
            [
                'user' => [
                    'name' => 'Boubacar Touré',
                    'email' => 'boubacar.toure@mentor.com',
                    'country' => 'Mali',
                    'city' => 'Bamako',
                    'linkedin_url' => 'https://linkedin.com/in/boubacartour',
                ],
                'profile' => [
                    'bio' => 'Entrepreneur agricole et ingénieur agronome. Pionnier de l\'agriculture de précision en Afrique de l\'Ouest avec sa startup AgriTech Mali.',
                    'current_position' => 'CEO & Fondateur',
                    'current_company' => 'AgriTech Mali',
                    'years_of_experience' => 8,
                    'specialization' => 'agriculture',
                    'is_published' => false, // En attente de validation
                ],
                'roadmap' => [
                    [
                        'step_type' => 'education',
                        'title' => 'Baccalauréat Scientifique',
                        'institution_company' => 'Lycée Askia Mohamed',
                        'location' => 'Bamako, Mali',
                        'start_date' => '2006-09-01',
                        'end_date' => '2009-07-01',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Ingénieur Agronome',
                        'institution_company' => 'Institut Polytechnique Rural de Katibougou',
                        'location' => 'Koulikoro, Mali',
                        'start_date' => '2009-10-01',
                        'end_date' => '2014-06-01',
                    ],
                    [
                        'step_type' => 'education',
                        'title' => 'Master AgTech',
                        'institution_company' => 'Wageningen University',
                        'location' => 'Pays-Bas',
                        'start_date' => '2014-09-01',
                        'end_date' => '2016-06-01',
                        'description' => 'Spécialisation en technologies agricoles et agriculture de précision',
                    ],
                    [
                        'step_type' => 'work',
                        'title' => 'Consultant Agricole',
                        'institution_company' => 'FAO',
                        'location' => 'Dakar, Sénégal',
                        'start_date' => '2016-09-01',
                        'end_date' => '2019-12-01',
                    ],
                    [
                        'step_type' => 'achievement',
                        'title' => 'Création AgriTech Mali',
                        'start_date' => '2020-01-01',
                        'description' => 'Startup qui utilise les drones et l\'IA pour optimiser les rendements agricoles. Plus de 500 agriculteurs accompagnés.',
                    ],
                ],
            ],
        ];

        foreach ($mentors as $mentorData) {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $mentorData['user']['name'],
                'email' => $mentorData['user']['email'],
                'password' => Hash::make('password123'),
                'user_type' => 'mentor',
                'country' => $mentorData['user']['country'],
                'city' => $mentorData['user']['city'],
                'linkedin_url' => $mentorData['user']['linkedin_url'],
                'email_verified_at' => now(),
            ]);

            // Créer le profil mentor
            $profile = MentorProfile::create([
                'user_id' => $user->id,
                'bio' => $mentorData['profile']['bio'],
                'current_position' => $mentorData['profile']['current_position'],
                'current_company' => $mentorData['profile']['current_company'],
                'years_of_experience' => $mentorData['profile']['years_of_experience'],
                'specialization' => $mentorData['profile']['specialization'],
                'is_published' => $mentorData['profile']['is_published'],
            ]);

            // Créer les étapes du roadmap
            foreach ($mentorData['roadmap'] as $position => $step) {
                RoadmapStep::create([
                    'mentor_profile_id' => $profile->id,
                    'step_type' => $step['step_type'],
                    'title' => $step['title'],
                    'institution_company' => $step['institution_company'] ?? null,
                    'location' => $step['location'] ?? null,
                    'start_date' => $step['start_date'] ?? null,
                    'end_date' => $step['end_date'] ?? null,
                    'description' => $step['description'] ?? null,
                    'position' => $position,
                ]);
            }
        }

        $this->command->info('5 mentors créés avec leurs roadmaps');
    }
}
