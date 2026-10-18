<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KidsGameController extends Controller
{
    /**
     * Get or create active child profile
     */
    public function getProfile(Request $request)
    {
        $profile = DB::table('kids_profiles')->first();

        if (! $profile) {
            $id = DB::table('kids_profiles')->insertGetId([
                'name' => 'Maya',
                'age' => 6,
                'avatar_key' => 'avatarHero',
                'favorite_color' => '#4f46e5',
                'stars_count' => 280,
                'gems_count' => 14,
                'streak_days' => 5,
                'current_world' => 1,
                'current_step' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
        $activities = [
            [
                'id' => 1,
                'title' => 'Atelier Éveil Rythmique & Percussions',
                'dimension' => 'Musicale',
                'duration' => '45 min',
                'age_range' => '4-8 ans',
                'description' => 'Découverte des percussions corporelles, maracas et djembé pour développer la pulsation naturelle et la synchronisation auditive.',
                'home_tip' => 'Utilisez deux cuillères en bois et un saladier pour marquer le tempo de sa chanson préférée.',
                'image_url' => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?w=600&auto=format&fit=crop&q=80',
                'recommended_places' => [
                    [
                        'name' => 'Conservatoire Municipal & Maison des Arts',
                        'city' => 'Paris / Île-de-France',
                        'type' => 'Atelier Pédagogique Public',
                        'distance' => '1.2 km',
                        'address' => '14 rue des Écoles',
                        'phone' => '+33 1 42 68 00 12',
                        'website' => 'https://conservatoire-musique.fr',
                    ],
                    [
                        'name' => 'Philharmonie des Enfants - Espace Rythme',
                        'city' => 'Paris 19e',
                        'type' => 'Musée Interactif Découverte',
                        'distance' => '3.5 km',
                        'address' => '221 Avenue Jean Jaurès',
                        'phone' => '+33 1 44 84 44 84',
                        'website' => 'https://philharmoniedeparis.fr/enfants',
                    ],
                    [
                        'name' => 'Médiathèque & Club des P’tits Musiciens',
                        'city' => 'Centre-Ville',
                        'type' => 'Médiathèque Municipale',
                        'distance' => '800 m',
                        'address' => 'Place de la Mairie',
                        'phone' => '+33 1 48 00 11 22',
                        'website' => 'https://mediatheque.fr',
                    ],
                ],
            ],
            [
                'id' => 2,
                'title' => 'Parcours de Motricité & Danse Sensorielle',
                'dimension' => 'Kinesthésique',
                'duration' => '1 h',
                'age_range' => '5-9 ans',
                'description' => 'Parcours d\'équilibre, sauts guidés et jeux d\'esquive pour perfectionner le réflexe moteur et la proprioception spatiale.',
                'home_tip' => 'Tracez une ligne au sol avec du ruban adhésif pour jouer au funambule et faire des bonds chrono.',
                'image_url' => 'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?w=600&auto=format&fit=crop&q=80',
                'recommended_places' => [
                    [
                        'name' => 'Gymnase Énergie & Kids Parkour',
                        'city' => 'Métropole Régionale',
                        'type' => 'Club de Gymnastique & Motricité',
                        'distance' => '1.8 km',
                        'address' => '45 Boulevard des Sports',
                        'phone' => '+33 1 43 90 20 10',
                        'website' => 'https://gymkidsparkour.fr',
                    ],
                    [
                        'name' => 'Parc Aventure & Forêt Ludique',
                        'city' => 'Environs / Plein Air',
                        'type' => 'Parc Nature & Sentier Équilibre',
                        'distance' => '4.2 km',
                        'address' => 'Allée des Grands Pins',
                        'phone' => '+33 1 45 60 70 80',
                        'website' => 'https://parcaventure-nature.fr',
                    ],
                ],
            ],
            [
                'id' => 3,
                'title' => 'Club Petits Bâtisseurs & Architecture 3D',
                'dimension' => 'Spatiale',
                'duration' => '1 h 15',
                'age_range' => '6-10 ans',
                'description' => 'Assemblage de maquettes géométriques, ponts en bois et structures d\'équilibre pour développer la visualisation 3D.',
                'home_tip' => 'Construisez une tour solide avec des boîtes de carton et testez sa résistance au vent avec un éventail.',
                'image_url' => 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=600&auto=format&fit=crop&q=80',
                'recommended_places' => [
                    [
                        'name' => 'FabLab Junior & Cité des Bâtisseurs',
                        'city' => 'Pôle Innovation',
                        'type' => 'Atelier Makers & STEAM',
                        'distance' => '2.5 km',
                        'address' => '8 rue de l\'Avenir',
                        'phone' => '+33 1 49 88 77 66',
                        'website' => 'https://fablabjunior.fr',
                    ],
                ],
            ],
        ];

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
            $id = DB::table('kids_profiles')->insertGetId([
                'name' => 'Maya',
                'age' => 6,
                'avatar_key' => 'avatarHero',
                'favorite_color' => '#4f46e5',
                'stars_count' => 280,
                'gems_count' => 14,
                'streak_days' => 5,
                'current_world' => 1,
                'current_step' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
            'avatar_key' => $validated['avatar_key'] ?? 'avatarHero',
            'favorite_color' => $validated['favorite_color'] ?? '#4f46e5',
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
}
