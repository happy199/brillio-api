<?php

namespace App\Services;

use App\Models\PersonalityTest;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour la gestion du test de personnalité MBTI via OpenMBTI API
 *
 * Ce service gère :
 * - La récupération des questions du test (32 questions OpenMBTI)
 * - L'envoi des réponses à l'API OpenMBTI pour calcul
 * - Le stockage des résultats
 *
 * @see https://openmbti.org/api-docs
 */
class PersonalityService
{
    private const API_URL = 'https://openmbti.org/api';

    /**
     * Questions du test MBTI (32 questions pour OpenMBTI)
     * Score de 1 à 5 (Likert scale)
     */
    private const QUESTIONS = [
        ['id' => 1, 'text_fr' => 'Je me sens énergisé(e) après avoir passé du temps avec un groupe de personnes.', 'text_en' => 'I feel energized after spending time with a group of people.'],
        ['id' => 2, 'text_fr' => 'Je préfère avoir quelques amis proches plutôt que beaucoup de connaissances.', 'text_en' => 'I prefer having a few close friends rather than many acquaintances.'],
        ['id' => 3, 'text_fr' => 'Je suis généralement la personne qui initie les conversations.', 'text_en' => 'I am usually the person who initiates conversations.'],
        ['id' => 4, 'text_fr' => 'J\'ai besoin de temps seul(e) pour me ressourcer.', 'text_en' => 'I need alone time to recharge.'],
        ['id' => 5, 'text_fr' => 'Je me sens à l\'aise d\'être le centre de l\'attention.', 'text_en' => 'I feel comfortable being the center of attention.'],
        ['id' => 6, 'text_fr' => 'Je préfère réfléchir avant de parler.', 'text_en' => 'I prefer to think before speaking.'],
        ['id' => 7, 'text_fr' => 'Je m\'épanouis dans les environnements sociaux animés.', 'text_en' => 'I thrive in busy social environments.'],
        ['id' => 8, 'text_fr' => 'Je préfère travailler de manière indépendante.', 'text_en' => 'I prefer to work independently.'],
        ['id' => 9, 'text_fr' => 'Je fais confiance aux faits et aux détails concrets.', 'text_en' => 'I trust facts and concrete details.'],
        ['id' => 10, 'text_fr' => 'J\'aime explorer des idées abstraites et des théories.', 'text_en' => 'I enjoy exploring abstract ideas and theories.'],
        ['id' => 11, 'text_fr' => 'Je me concentre sur ce qui est réel et actuel.', 'text_en' => 'I focus on what is real and current.'],
        ['id' => 12, 'text_fr' => 'J\'aime imaginer les possibilités futures.', 'text_en' => 'I like to imagine future possibilities.'],
        ['id' => 13, 'text_fr' => 'Je préfère les instructions étape par étape.', 'text_en' => 'I prefer step-by-step instructions.'],
        ['id' => 14, 'text_fr' => 'Je suis attiré(e) par les nouvelles idées et innovations.', 'text_en' => 'I am drawn to new ideas and innovations.'],
        ['id' => 15, 'text_fr' => 'Je fais confiance à mon expérience passée.', 'text_en' => 'I trust my past experience.'],
        ['id' => 16, 'text_fr' => 'Je vois souvent des modèles et des connexions que d\'autres ne remarquent pas.', 'text_en' => 'I often see patterns and connections that others don\'t notice.'],
        ['id' => 17, 'text_fr' => 'Je prends des décisions basées sur la logique et l\'analyse.', 'text_en' => 'I make decisions based on logic and analysis.'],
        ['id' => 18, 'text_fr' => 'Je considère comment mes décisions affecteront les sentiments des autres.', 'text_en' => 'I consider how my decisions will affect others\' feelings.'],
        ['id' => 19, 'text_fr' => 'Je valorise la vérité objective plutôt que l\'harmonie sociale.', 'text_en' => 'I value objective truth over social harmony.'],
        ['id' => 20, 'text_fr' => 'Je suis naturellement empathique envers les autres.', 'text_en' => 'I am naturally empathetic towards others.'],
        ['id' => 21, 'text_fr' => 'Je préfère être juste plutôt que compatissant(e).', 'text_en' => 'I prefer being fair over being compassionate.'],
        ['id' => 22, 'text_fr' => 'Je suis sensible aux besoins des autres.', 'text_en' => 'I am sensitive to others\' needs.'],
        ['id' => 23, 'text_fr' => 'Je reste calme et détaché(e) lors des conflits.', 'text_en' => 'I stay calm and detached during conflicts.'],
        ['id' => 24, 'text_fr' => 'Je priorise le maintien des relations harmonieuses.', 'text_en' => 'I prioritize maintaining harmonious relationships.'],
        ['id' => 25, 'text_fr' => 'Je préfère avoir un plan clair et m\'y tenir.', 'text_en' => 'I prefer having a clear plan and sticking to it.'],
        ['id' => 26, 'text_fr' => 'J\'aime garder mes options ouvertes.', 'text_en' => 'I like to keep my options open.'],
        ['id' => 27, 'text_fr' => 'Je me sens mal à l\'aise avec l\'ambiguïté.', 'text_en' => 'I feel uncomfortable with ambiguity.'],
        ['id' => 28, 'text_fr' => 'Je suis adaptable et flexible dans mon approche.', 'text_en' => 'I am adaptable and flexible in my approach.'],
        ['id' => 29, 'text_fr' => 'Je préfère terminer les tâches avant les délais.', 'text_en' => 'I prefer to complete tasks before deadlines.'],
        ['id' => 30, 'text_fr' => 'Je travaille mieux sous pression de dernière minute.', 'text_en' => 'I work best under last-minute pressure.'],
        ['id' => 31, 'text_fr' => 'J\'aime que les choses soient décidées et réglées.', 'text_en' => 'I like things to be decided and settled.'],
        ['id' => 32, 'text_fr' => 'Je préfère la spontanéité à la planification.', 'text_en' => 'I prefer spontaneity over planning.'],
    ];

    /**
     * Descriptions détaillées des types de personnalité en français
     */
    public const TYPE_DESCRIPTIONS = [
        'INTJ' => [
            'label' => 'L\'Architecte',
            'description' => 'Les INTJ sont des penseurs stratégiques naturels. Ils excellent dans la planification à long terme et l\'analyse complexe. Indépendants et déterminés, ils cherchent constamment à améliorer les systèmes et processus. Ils sont souvent attirés par les carrières en technologie, sciences, ingénierie, droit ou gestion stratégique.',
        ],
        'INTP' => [
            'label' => 'Le Logicien',
            'description' => 'Les INTP sont des penseurs analytiques et logiques qui aiment résoudre des problèmes complexes. Curieux et inventifs, ils excellent dans la recherche et l\'innovation. Ils sont souvent attirés par les carrières en recherche scientifique, programmation, philosophie ou architecture.',
        ],
        'ENTJ' => [
            'label' => 'Le Commandant',
            'description' => 'Les ENTJ sont des leaders nés, stratégiques et décisifs. Ils excellent dans l\'organisation et la direction d\'équipes vers des objectifs ambitieux. Confiants et charismatiques, ils sont souvent attirés par les carrières en management, entrepreneuriat, conseil ou politique.',
        ],
        'ENTP' => [
            'label' => 'L\'Innovateur',
            'description' => 'Les ENTP sont des innovateurs créatifs qui adorent les défis intellectuels. Ils excellent dans le brainstorming et la résolution créative de problèmes. Enthousiastes et adaptables, ils sont souvent attirés par l\'entrepreneuriat, le marketing, le droit ou le consulting.',
        ],
        'INFJ' => [
            'label' => 'L\'Avocat',
            'description' => 'Les INFJ sont des idéalistes empathiques avec une vision claire du futur. Ils excellent dans la compréhension des autres et la résolution de problèmes humains. Ils sont souvent attirés par les carrières en psychologie, conseil, écriture, ONG ou développement personnel.',
        ],
        'INFP' => [
            'label' => 'Le Médiateur',
            'description' => 'Les INFP sont des idéalistes créatifs guidés par leurs valeurs. Ils excellent dans l\'expression artistique et l\'aide aux autres. Empathiques et authentiques, ils sont souvent attirés par l\'écriture, les arts, la psychologie, l\'éducation ou le travail social.',
        ],
        'ENFJ' => [
            'label' => 'Le Protagoniste',
            'description' => 'Les ENFJ sont des leaders charismatiques et inspirants. Ils excellent dans la motivation des autres et la création de communautés. Empathiques et organisés, ils sont souvent attirés par l\'enseignement, les RH, le coaching, la politique ou le marketing.',
        ],
        'ENFP' => [
            'label' => 'Le Campaigner',
            'description' => 'Les ENFP sont des esprits libres créatifs et sociables. Ils excellent dans l\'innovation et la connexion avec les autres. Enthousiastes et flexibles, ils sont souvent attirés par le journalisme, les arts, la publicité, l\'entrepreneuriat social ou l\'événementiel.',
        ],
        'ISTJ' => [
            'label' => 'Le Logisticien',
            'description' => 'Les ISTJ sont des individus fiables et méthodiques. Ils excellent dans l\'organisation et le respect des processus. Responsables et pratiques, ils sont souvent attirés par la comptabilité, l\'administration, le droit, l\'armée ou la gestion de projet.',
        ],
        'ISFJ' => [
            'label' => 'Le Défenseur',
            'description' => 'Les ISFJ sont des protecteurs dévoués et attentionnés. Ils excellent dans le service aux autres et le maintien des traditions. Loyaux et patients, ils sont souvent attirés par la santé, l\'éducation, l\'administration, le social ou les RH.',
        ],
        'ESTJ' => [
            'label' => 'Le Directeur',
            'description' => 'Les ESTJ sont des organisateurs efficaces et pratiques. Ils excellent dans la gestion et l\'application des règles. Directs et déterminés, ils sont souvent attirés par le management, l\'administration publique, le droit, la finance ou l\'armée.',
        ],
        'ESFJ' => [
            'label' => 'Le Consul',
            'description' => 'Les ESFJ sont des personnes sociables et attentionnées. Ils excellent dans la création d\'harmonie et le soin des autres. Loyaux et coopératifs, ils sont souvent attirés par la santé, l\'éducation, le social, les RH ou l\'événementiel.',
        ],
        'ISTP' => [
            'label' => 'Le Virtuose',
            'description' => 'Les ISTP sont des praticiens habiles et analytiques. Ils excellent dans la résolution pratique de problèmes. Curieux et efficaces, ils sont souvent attirés par l\'ingénierie, la mécanique, l\'informatique, l\'artisanat ou les métiers techniques.',
        ],
        'ISFP' => [
            'label' => 'L\'Aventurier',
            'description' => 'Les ISFP sont des artistes sensibles et flexibles. Ils excellent dans l\'expression artistique et l\'appréciation de la beauté. Doux et adaptables, ils sont souvent attirés par les arts, le design, la mode, la musique ou les soins personnels.',
        ],
        'ESTP' => [
            'label' => 'L\'Entrepreneur',
            'description' => 'Les ESTP sont des pragmatiques énergiques qui aiment l\'action. Ils excellent dans la négociation et la gestion de crise. Audacieux et sociables, ils sont souvent attirés par la vente, l\'entrepreneuriat, le sport, l\'urgence ou le marketing.',
        ],
        'ESFP' => [
            'label' => 'L\'Amuseur',
            'description' => 'Les ESFP sont des entertainers spontanés et énergiques. Ils excellent dans la performance et la création d\'expériences. Optimistes et pratiques, ils sont souvent attirés par le divertissement, l\'événementiel, la vente, le tourisme ou les arts.',
        ],
    ];

    /**
     * Retourne les questions du test formatées
     */
    public function getQuestions(string $locale = 'fr'): array
    {
        return array_map(function ($question) use ($locale) {
            $textKey = $locale === 'fr' ? 'text_fr' : 'text_en';
            return [
                'id' => $question['id'],
                'text' => $question[$textKey],
                'options' => [
                    ['value' => 1, 'label' => $locale === 'fr' ? 'Pas du tout d\'accord' : 'Strongly disagree'],
                    ['value' => 2, 'label' => $locale === 'fr' ? 'Plutôt pas d\'accord' : 'Disagree'],
                    ['value' => 3, 'label' => $locale === 'fr' ? 'Neutre' : 'Neutral'],
                    ['value' => 4, 'label' => $locale === 'fr' ? 'Plutôt d\'accord' : 'Agree'],
                    ['value' => 5, 'label' => $locale === 'fr' ? 'Tout à fait d\'accord' : 'Strongly agree'],
                ],
            ];
        }, self::QUESTIONS);
    }

    /**
     * Calcule le type de personnalité via l'API OpenMBTI
     *
     * @param array $responses Format: [question_id => score (1-5)]
     */
    public function calculatePersonalityType(array $responses): array
    {
        try {
            // Formater les réponses pour l'API OpenMBTI (clés string)
            $formattedAnswers = [];
            foreach ($responses as $questionId => $score) {
                $formattedAnswers[(string) $questionId] = (int) $score;
            }

            // Appel à l'API OpenMBTI
            $response = Http::timeout(30)->post(self::API_URL . '/calculate', [
                'answers' => $formattedAnswers,
                'locale' => 'fr', // Résultats en français
                'save' => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $type = $data['type'] ?? 'INTJ';
                $typeInfo = self::TYPE_DESCRIPTIONS[$type] ?? [
                    'label' => $data['name'] ?? 'Type inconnu',
                    'description' => $data['description'] ?? 'Description non disponible.',
                ];

                // Extraire les scores des traits depuis la réponse API
                $traitsScores = [
                    'E' => $data['scores']['E'] ?? 50,
                    'I' => $data['scores']['I'] ?? 50,
                    'S' => $data['scores']['S'] ?? 50,
                    'N' => $data['scores']['N'] ?? 50,
                    'T' => $data['scores']['T'] ?? 50,
                    'F' => $data['scores']['F'] ?? 50,
                    'J' => $data['scores']['J'] ?? 50,
                    'P' => $data['scores']['P'] ?? 50,
                ];

                return [
                    'type' => $type,
                    'label' => $typeInfo['label'],
                    'description' => $typeInfo['description'],
                    'traits_scores' => $traitsScores,
                ];
            }

            Log::error('OpenMBTI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Fallback: calcul local si l'API échoue
            return $this->calculateLocalFallback($responses);

        } catch (\Exception $e) {
            Log::error('OpenMBTI API exception', ['message' => $e->getMessage()]);
            return $this->calculateLocalFallback($responses);
        }
    }

    /**
     * Calcul local de secours si l'API OpenMBTI est indisponible
     */
    private function calculateLocalFallback(array $responses): array
    {
        // Mapping simplifié des questions aux dimensions
        // Questions 1-8: E/I, 9-16: S/N, 17-24: T/F, 25-32: J/P
        $dimensions = ['EI' => 0, 'SN' => 0, 'TF' => 0, 'JP' => 0];

        foreach ($responses as $questionId => $score) {
            $id = (int) $questionId;
            $normalizedScore = $score - 3; // Convertir 1-5 en -2 à +2

            if ($id >= 1 && $id <= 8) {
                // Questions impaires favorisent E, paires favorisent I
                $dimensions['EI'] += ($id % 2 === 1) ? $normalizedScore : -$normalizedScore;
            } elseif ($id >= 9 && $id <= 16) {
                $dimensions['SN'] += ($id % 2 === 1) ? -$normalizedScore : $normalizedScore;
            } elseif ($id >= 17 && $id <= 24) {
                $dimensions['TF'] += ($id % 2 === 1) ? $normalizedScore : -$normalizedScore;
            } elseif ($id >= 25 && $id <= 32) {
                $dimensions['JP'] += ($id % 2 === 1) ? $normalizedScore : -$normalizedScore;
            }
        }

        // Détermination du type
        $type = '';
        $type .= $dimensions['EI'] >= 0 ? 'E' : 'I';
        $type .= $dimensions['SN'] >= 0 ? 'S' : 'N';
        $type .= $dimensions['TF'] >= 0 ? 'T' : 'F';
        $type .= $dimensions['JP'] >= 0 ? 'J' : 'P';

        // Calcul des pourcentages
        $maxScore = 16; // 8 questions * 2 max
        $traitsScores = [
            'E' => 50 + ($dimensions['EI'] / $maxScore * 50),
            'I' => 50 - ($dimensions['EI'] / $maxScore * 50),
            'S' => 50 - ($dimensions['SN'] / $maxScore * 50),
            'N' => 50 + ($dimensions['SN'] / $maxScore * 50),
            'T' => 50 + ($dimensions['TF'] / $maxScore * 50),
            'F' => 50 - ($dimensions['TF'] / $maxScore * 50),
            'J' => 50 + ($dimensions['JP'] / $maxScore * 50),
            'P' => 50 - ($dimensions['JP'] / $maxScore * 50),
        ];

        $typeInfo = self::TYPE_DESCRIPTIONS[$type] ?? [
            'label' => 'Type inconnu',
            'description' => 'Description non disponible.',
        ];

        return [
            'type' => $type,
            'label' => $typeInfo['label'],
            'description' => $typeInfo['description'],
            'traits_scores' => $traitsScores,
        ];
    }

    /**
     * Soumet les réponses et enregistre le résultat pour un utilisateur
     */
    public function submitTest(User $user, array $responses): PersonalityTest
    {
        $result = $this->calculatePersonalityType($responses);

        // Création ou mise à jour du test
        $personalityTest = PersonalityTest::updateOrCreate(
            ['user_id' => $user->id],
            [
                'test_type' => 'openmbti',
                'raw_responses' => $responses,
                'personality_type' => $result['type'],
                'personality_label' => $result['label'],
                'personality_description' => $result['description'],
                'traits_scores' => $result['traits_scores'],
                'completed_at' => now(),
            ]
        );

        Log::info('Test de personnalité complété', [
            'user_id' => $user->id,
            'type' => $result['type'],
        ]);

        return $personalityTest;
    }

    /**
     * Enregistre un résultat de test pré-calculé (depuis l'API OpenMBTI côté frontend)
     */
    public function savePreCalculatedResult(
        User $user,
        string $personalityType,
        string $personalityLabel,
        string $personalityDescription,
        array $traitsScores,
        array $responses
    ): PersonalityTest {
        // Marquer tous les tests précédents comme historique
        PersonalityTest::where('user_id', $user->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // Créer un nouveau test
        $personalityTest = PersonalityTest::create([
            'user_id' => $user->id,
            'test_type' => 'openmbti',
            'raw_responses' => $responses,
            'personality_type' => $personalityType,
            'personality_label' => $personalityLabel,
            'personality_description' => $personalityDescription,
            'traits_scores' => $traitsScores,
            'completed_at' => now(),
            'is_current' => true,
        ]);

        Log::info('Test de personnalité complété (pré-calculé)', [
            'user_id' => $user->id,
            'type' => $personalityType,
        ]);

        return $personalityTest;
    }

    /**
     * Récupère le résultat du test d'un utilisateur
     */
    public function getResult(User $user): ?PersonalityTest
    {
        return $user->personalityTest;
    }

    /**
     * Vérifie si l'utilisateur a complété le test
     */
    public function hasCompletedTest(User $user): bool
    {
        $test = $user->personalityTest;
        return $test && $test->isCompleted();
    }

    /**
     * Retourne les statistiques globales des types de personnalité
     */
    public function getStatistics(): array
    {
        $stats = PersonalityTest::whereNotNull('personality_type')
            ->selectRaw('personality_type, COUNT(*) as count')
            ->groupBy('personality_type')
            ->pluck('count', 'personality_type')
            ->toArray();

        $total = array_sum($stats);

        return [
            'total_tests' => $total,
            'distribution' => array_map(function ($count) use ($total) {
                return [
                    'count' => $count,
                    'percentage' => $total > 0 ? round($count / $total * 100, 1) : 0,
                ];
            }, $stats),
        ];
    }
}
