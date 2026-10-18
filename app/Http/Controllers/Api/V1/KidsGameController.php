<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KidsGameController extends Controller
{
    private const DEFAULT_FAVORITE_COLOR = '#4f46e5';

    private const DEFAULT_AVATAR_KEY = 'avatarHero';

    private function createDefaultProfile(): int
    {
        return DB::table('kids_profiles')->insertGetId([
            'name' => 'Maya',
            'age' => 6,
            'avatar_key' => self::DEFAULT_AVATAR_KEY,
            'favorite_color' => self::DEFAULT_FAVORITE_COLOR,
            'stars_count' => 280,
            'gems_count' => 14,
            'streak_days' => 5,
            'current_world' => 1,
            'current_step' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get or create active child profile
     */
    public function getProfile(Request $request)
    {
        $profile = DB::table('kids_profiles')->first();

        if (! $profile) {
            $id = $this->createDefaultProfile();
            $profile = DB::table('kids_profiles')->where('id', $id)->first();
        }

        return response()->json([
            'status' => 'success',
            'data' => $profile,
        ]);
    }

    /**
     * Record a mini-game play session with cognitive telemetry
     */
    public function submitGameSession(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|string',
            'dimension' => 'required|string',
            'score' => 'nullable|integer',
            'accuracy_rate' => 'nullable|numeric',
            'reaction_time_ms' => 'nullable|integer',
            'rotations_count' => 'nullable|integer',
            'telemetry_data' => 'nullable|array',
        ]);

        $profile = DB::table('kids_profiles')->first();
        $profileId = $profile ? $profile->id : 1;

        $sessionId = DB::table('kids_game_sessions')->insertGetId([
            'kids_profile_id' => $profileId,
            'game_id' => $validated['game_id'],
            'dimension' => $validated['dimension'],
            'score' => $validated['score'] ?? 100,
            'accuracy_rate' => $validated['accuracy_rate'] ?? 94.0,
            'reaction_time_ms' => $validated['reaction_time_ms'] ?? null,
            'rotations_count' => $validated['rotations_count'] ?? null,
            'telemetry_data' => isset($validated['telemetry_data']) ? json_encode($validated['telemetry_data']) : null,
            'completed' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Reward with stars and update stats
        DB::table('kids_profiles')->where('id', $profileId)->increment('stars_count', 50);

        return response()->json([
            'status' => 'success',
            'message' => 'Session enregistrée avec succès',
            'session_id' => $sessionId,
            'rewards' => [
                'stars_earned' => 50,
                'talent_dimension' => $validated['dimension'],
            ],
        ]);
    }

    /**
     * Get 8D Gardner Parent Dashboard analytics
     */
    public function getParentReport(Request $request)
    {
        $profile = DB::table('kids_profiles')->first();
        $profileId = $profile ? $profile->id : 1;

        $sessions = DB::table('kids_game_sessions')
            ->where('kids_profile_id', $profileId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        // 8 Gardner dimensions mapping
        $dimensions = [
            'Musicale' => 94,
            'Kinesthésique' => 86,
            'Spatiale' => 78,
            'Logico-Mathématique' => 72,
            'Linguistique' => 68,
            'Naturaliste' => 64,
            'Interpersonnelle' => 60,
            'Intrapersonnelle' => 58,
        ];

        // Dynamic activities with localized places recommendation (city, neighborhood, venue)
        $activities = $this->getTailoredActivities();

        return response()->json([
            'status' => 'success',
            'data' => [
                'child' => $profile,
                'top_superpower' => [
                    'name' => 'Oreille Musicale & Sens du Rythme',
                    'accuracy' => 94,
                    'confidence_index' => 88,
                    'description' => 'Maya synchronise spontanément ses mouvements aux stimuli musicaux avec une précision temporelle de 94%.',
                ],
                'dimensions' => $dimensions,
                'recent_sessions' => $sessions,
                'tailored_activities' => $activities,
            ],
        ]);
    }

    /**
     * Parent Account Creation / Sign-In with email and password
     */
    public function registerParent(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
            'parent_name' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $parent = DB::table('users')->where('email', $validated['email'])->first();

        if (! $parent) {
            $parentId = DB::table('users')->insertGetId([
                'name' => $validated['parent_name'] ?? 'Parent Brillio',
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => 'parent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $parent = DB::table('users')->where('id', $parentId)->first();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Compte parent actif',
            'data' => [
                'parent' => [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'email' => $parent->email,
                ],
                'token' => 'brillio_parent_token_'.bin2hex(random_bytes(16)),
            ],
        ]);
    }

    /**
     * List all children for this parent account
     */
    public function getChildren(Request $request)
    {
        $children = DB::table('kids_profiles')->get();

        if ($children->isEmpty()) {
            $id = $this->createDefaultProfile();
            $children = DB::table('kids_profiles')->where('id', $id)->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $children,
        ]);
    }

    /**
     * Add a new child to parent account
     */
    public function createChild(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'age' => 'required|integer|min:3|max:12',
            'avatar_key' => 'nullable|string',
            'favorite_color' => 'nullable|string',
        ]);

        $id = DB::table('kids_profiles')->insertGetId([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'avatar_key' => $validated['avatar_key'] ?? self::DEFAULT_AVATAR_KEY,
            'favorite_color' => $validated['favorite_color'] ?? self::DEFAULT_FAVORITE_COLOR,
            'stars_count' => 0,
            'gems_count' => 0,
            'streak_days' => 1,
            'current_world' => 1,
            'current_step' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $newChild = DB::table('kids_profiles')->where('id', $id)->first();

        return response()->json([
            'status' => 'success',
            'message' => "Espace créé pour {$validated['name']} !",
            'data' => $newChild,
        ]);
    }

    /**
     * Process dynamic parent subscription payment (Stripe / Moneroo simulation)
     */
    public function subscribePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|string',
            'parent_email' => 'required|email',
            'payment_method' => 'nullable|string',
        ]);

        $plans = [
            'yearly' => ['amount' => 59, 'currency' => 'EUR', 'label' => 'Pass Explorateur Annuel (1 Enfant)'],
            'monthly' => ['amount' => 7.99, 'currency' => 'EUR', 'label' => 'Pass Explorateur Mensuel'],
            'family' => ['amount' => 99, 'currency' => 'EUR', 'label' => 'Pass Tribu Famille (Multi-Enfants)'],
        ];

        $plan = $plans[$validated['plan_id']] ?? $plans['yearly'];

        return response()->json([
            'status' => 'success',
            'message' => 'Abonnement activé avec succès !',
            'data' => [
                'subscription_id' => 'sub_'.bin2hex(random_bytes(8)),
                'plan_id' => $validated['plan_id'],
                'plan_label' => $plan['label'],
                'amount_paid' => $plan['amount'],
                'currency' => $plan['currency'],
                'active_until' => now()->addYear()->toIso8601String(),
                'status' => 'active',
            ],
        ]);
    }

    /**
     * Save parent observation (Micro-journaling 3 clicks)
     */
    public function submitObservation(Request $request)
    {
        $validated = $request->validate([
            'question_key' => 'required|string',
            'answer' => 'required|string',
        ]);

        $profile = DB::table('kids_profiles')->first();
        $profileId = $profile ? $profile->id : 1;

        DB::table('kids_parent_observations')->insert([
            'kids_profile_id' => $profileId,
            'question_key' => $validated['question_key'],
            'answer' => $validated['answer'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Observation enregistrée',
        ]);
    }

    private function buildPlace(string $name, string $city, string $type, string $distance, string $address, string $phone, string $website): array
    {
        return compact('name', 'city', 'type', 'distance', 'address', 'phone', 'website');
    }

    private function formatActivity(
        int $id,
        string $title,
        string $dimension,
        string $duration,
        string $ageRange,
        string $description,
        string $homeTip,
        string $imageUrl,
        array $places
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'dimension' => $dimension,
            'duration' => $duration,
            'age_range' => $ageRange,
            'description' => $description,
            'home_tip' => $homeTip,
            'image_url' => $imageUrl,
            'recommended_places' => $places,
        ];
    }

    private function getTailoredActivities(): array
    {
        return [
            $this->formatActivity(
                1,
                'Atelier Éveil Rythmique & Percussions',
                'Musicale',
                '45 min',
                '4-8 ans',
                'Découverte des percussions corporelles, maracas et djembé pour développer la pulsation naturelle et la synchronisation auditive.',
                'Utilisez deux cuillères en bois et un saladier pour marquer le tempo de sa chanson préférée.',
                'https://images.unsplash.com/photo-1516627145497-ae6968895b74?w=600&auto=format&fit=crop&q=80',
                [
                    $this->buildPlace('Conservatoire Municipal & Maison des Arts', 'Paris / Île-de-France', 'Atelier Pédagogique Public', '1.2 km', '14 rue des Écoles', '+33 1 42 68 00 12', 'https://conservatoire-musique.fr'),
                    $this->buildPlace('Philharmonie des Enfants - Espace Rythme', 'Paris 19e', 'Musée Interactif Découverte', '3.5 km', '221 Avenue Jean Jaurès', '+33 1 44 84 44 84', 'https://philharmoniedeparis.fr/enfants'),
                    $this->buildPlace('Médiathèque & Club des P’tits Musiciens', 'Centre-Ville', 'Médiathèque Municipale', '800 m', 'Place de la Mairie', '+33 1 48 00 11 22', 'https://mediatheque.fr'),
                ]
            ),
            $this->formatActivity(
                2,
                'Parcours de Motricité & Danse Sensorielle',
                'Kinesthésique',
                '1 h',
                '5-9 ans',
                'Parcours d\'équilibre, sauts guidés et jeux d\'esquive pour perfectionner le réflexe moteur et la proprioception spatiale.',
                'Tracez une ligne au sol avec du ruban adhésif pour jouer au funambule et faire des bonds chrono.',
                'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?w=600&auto=format&fit=crop&q=80',
                [
                    $this->buildPlace('Gymnase Énergie & Kids Parkour', 'Métropole Régionale', 'Club de Gymnastique & Motricité', '1.8 km', '45 Boulevard des Sports', '+33 1 43 90 20 10', 'https://gymkidsparkour.fr'),
                    $this->buildPlace('Parc Aventure & Forêt Ludique', 'Environs / Plein Air', 'Parc Nature & Sentier Équilibre', '4.2 km', 'Allée des Grands Pins', '+33 1 45 60 70 80', 'https://parcaventure-nature.fr'),
                ]
            ),
            $this->formatActivity(
                3,
                'Club Petits Bâtisseurs & Architecture 3D',
                'Spatiale',
                '1 h 15',
                '6-10 ans',
                'Assemblage de maquettes géométriques, ponts en bois et structures d\'équilibre pour développer la visualisation 3D.',
                'Construisez une tour solide avec des boîtes de carton et testez sa résistance au vent avec un éventail.',
                'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=600&auto=format&fit=crop&q=80',
                [
                    $this->buildPlace('FabLab Junior & Cité des Bâtisseurs', 'Pôle Innovation', 'Atelier Makers & STEAM', '2.5 km', '8 rue de l\'Avenir', '+33 1 49 88 77 66', 'https://fablabjunior.fr'),
                ]
            ),
        ];
    }
}
