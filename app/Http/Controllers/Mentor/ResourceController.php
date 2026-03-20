<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Liste des ressources du mentor
     */
    public function index()
    {
        // On réinitialise le déblocage des stats quand on revient à la liste
        session()->forget('resource_stats_unlocked');

        $resources = auth()->user()->resources()->orderBy('created_at', 'desc')->paginate(12);

        return view('mentor.resources.index', compact('resources'));
    }

    /**
     * Boutique de ressources (Marketplace)
     */
    public function marketplace(Request $request)
    {
        $query = Resource::query()
            ->where(fn ($q) => $q->where('user_id', '!=', auth()->id()))
            ->where(fn ($q) => $q->where('is_published', true))
            ->where(fn ($q) => $q->where('is_validated', true));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where(fn ($q) => $q->where('type', $request->type));
        }

        if ($request->filled('author')) {
            if ($request->author === 'brillio') {
                $query->where(fn ($q) => $q->where('user_id', 1));
            } elseif ($request->author === 'mentors') {
                $query->where(fn ($q) => $q->where('user_id', '!=', 1));
            }
        }

        if ($request->filled('price')) {
            if ($request->price === 'free') {
                $query->where(fn ($q) => $q->where('price', 0));
            } elseif ($request->price === 'paid') {
                $query->where(fn ($q) => $q->where('price', '>', 0));
            }
        }

        $resources = $query->with('user')->latest()->paginate(12);
        $totalCount = $query->count();

        return view('mentor.resources.marketplace', compact('resources', 'totalCount'));
    }

    /**
     * Récupère les statistiques de la demande (Données Jeunes) - Payant 10 Crédits
     */
    public function getDemandStats()
    {
        $user = auth()->user();
        $cost = 5;

        // Si déjà débloqué pour cette session de création, on ne prélève pas
        if (session('resource_stats_unlocked')) {
            $cost = 0;
        }

        try {
            if ($cost > 0) {
                if ($user->credits_balance < $cost) {
                    return response()->json([
                        'error' => "Crédits insuffisants. Cette analyse coûte {$cost} crédits.",
                        'balance' => $user->credits_balance,
                    ], 402);
                }

                $this->walletService->deductCredits(
                    $user,
                    $cost,
                    'service_fee',
                    'Analyse des statistiques de la demande',
                    null
                );

                // On marque comme débloqué pour les rechargements de page
                session(['resource_stats_unlocked' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // Calcul des statistiques - On inclut TOUS les jeunes (pas seulement ceux qui ont fini l'onboarding)
        $jeunes = User::where('user_type', 'jeune')->get();

        $stats = [
            'total' => $jeunes->count(),
            'education' => [
                'college' => 0,
                'lycee' => 0,
                'bac' => 0,
                'licence' => 0,
                'master' => 0,
                'doctorat' => 0,
            ],
            'situation' => [
                'etudiant' => 0,
                'recherche_emploi' => 0,
                'emploi' => 0,
                'entrepreneur' => 0,
                'autre' => 0,
            ],
            'countries' => [],
            'personality_types' => [],
        ];

        foreach ($jeunes as $jeune) {
            $data = $jeune->onboarding_data ?? [];

            // Education
            if (isset($data['education_level']) && isset($stats['education'][$data['education_level']])) {
                $stats['education'][$data['education_level']]++;
            }

            // Situation
            if (isset($data['current_situation']) && isset($stats['situation'][$data['current_situation']])) {
                $stats['situation'][$data['current_situation']]++;
            }

            // Pays
            if ($jeune->country) {
                $country = ucfirst(strtolower($jeune->country));
                $stats['countries'][$country] = ($stats['countries'][$country] ?? 0) + 1;
            }
        }

        // Personality Types (MBTI) - Alignement sur TOUS les jeunes
        $mbtiStats = \App\Models\PersonalityTest::query()
            ->join('users', 'personality_tests.user_id', '=', 'users.id')
            ->where('users.user_type', 'jeune')
            ->where('personality_tests.is_current', true)
            ->select('personality_tests.personality_type', DB::raw('count(*) as total'))
            ->groupBy('personality_tests.personality_type')
            ->pluck('total', 'personality_type')
            ->toArray();

        $stats['personality_types'] = $mbtiStats;

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'balance' => $user->refresh()->credits_balance,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $mentorProfile = auth()->user()->mentorProfile;

        // Check middleware handles redirection now

        $targetingOptions = $this->getDynamicTargetingOptions();
        $targetingCost = $this->walletService->getFeatureCost('advanced_targeting', 10);

        return view('mentor.resources.create', compact('targetingOptions', 'targetingCost'));
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $messages = [
            'required' => 'Ce champ est obligatoire.',
            'string' => 'Ce champ doit être une chaîne de caractères.',
            'max' => 'La taille ne doit pas dépasser :max.',
            'in' => 'La valeur sélectionnée est invalide.',
            'integer' => 'Ce champ doit être un entier.',
            'min' => 'La valeur doit être au moins :min.',
            'file' => 'Le fichier doit être valide.',
            'image' => 'Le fichier doit être une image.',
            'file.max' => 'Le fichier est trop volumineux (Max 20 Mo).',
            'preview_image.max' => 'L\'image de couverture est trop volumineuse (Max 5 Mo).',
            'uploaded' => 'Le fichier est trop volumineux pour le serveur (limite technique atteinte).',
            'file.uploaded' => 'Le fichier dépasse la limite autorisée par le serveur.',
            'preview_image.uploaded' => 'L\'image dépasse la limite autorisée par le serveur.',
        ];

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'type' => 'required|in:article,video,tool,exercise,template,script,advertisement',

            'price' => 'nullable|integer',
            'is_premium' => 'required|in:0,1',
            'file' => 'nullable|file|max:20480', // 20MB
            'preview_image' => 'nullable|image|max:5120', // 5MB
            'metadata' => 'nullable|array',
            'mbti_types' => 'nullable|array',
            'tags' => 'nullable|string',
            'targeting' => 'nullable|array',
        ], $messages);

        // Vérification des crédits pour le ciblage
        $hasTargeting = ! empty($validated['targeting']) && (
            ! empty($validated['targeting']['education_levels']) ||
            ! empty($validated['targeting']['situations']) ||
            ! empty($validated['targeting']['countries']) ||
            ! empty($validated['targeting']['interests'])
        );

        // Validation conditionnelle pour le prix
        if ($request->is_premium == '1') {
            $request->validate([
                'price' => 'required|integer|min:200',
            ], [
                'price.required' => 'Le prix est obligatoire pour une ressource payante.',
                'price.min' => 'Le prix minimum pour une ressource payante est de 200 FCFA.',
            ]);
        }

        if ($hasTargeting) {
            $cost = $this->walletService->getFeatureCost('advanced_targeting', 10);
            try {
                // Tentaive de débit (lance une exception si solde insuffisant)
                // Note: On ne sauvegarde pas encore la transaction car si le create échoue plus bas, on a débité pour rien.
                // On utilisera une transaction DB globale ou on checke juste le solde ici.
                if (auth()->user()->credits_balance < $cost) {
                    return back()->withInput()->withErrors(['targeting' => "Crédits insuffisants. Le ciblage avancé coûte {$cost} crédits. Votre solde: ".auth()->user()->credits_balance]);
                }
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['targeting' => $e->getMessage()]);
            }
        }

        // Gestion des fichiers
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('resources/files', 'public');
        }

        $previewPath = null;
        if ($request->hasFile('preview_image')) {
            $previewPath = $request->file('preview_image')->store('resources/previews', 'public');
        }

        // Traitement des tags (string vers array)
        $tags = ! empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        // Création Transactionnelle
        try {
            DB::beginTransaction();

            $resource = Resource::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']).'-'.uniqid(),
                'description' => $validated['description'],
                'content' => $validated['content'],

                'type' => $validated['type'],
                'price' => $request->is_premium == '1' ? $request->price : 0,
                'is_premium' => $request->is_premium == '1', // Correction ici
                'file_path' => $filePath,
                'preview_image_path' => $previewPath,
                'metadata' => $validated['metadata'] ?? [],
                'mbti_types' => $validated['mbti_types'] ?? [],
                'tags' => $tags,
                'targeting' => $validated['targeting'] ?? [],
                'is_published' => true,
                'is_validated' => true,
                'validated_at' => now(),
                'admin_feedback' => null,
                'unpublished_at' => null,
            ]);

            // Débit réel si ciblage
            if ($hasTargeting && isset($cost)) {
                $this->walletService->deductCredits(
                    auth()->user(),
                    $cost,
                    'service_fee',
                    "Ciblage avancé pour: {$validated['title']}",
                    $resource
                );
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            // Nettoyage fichiers si erreur (optionnel mais propre)
            return back()->withInput()->with('error', 'Erreur lors de la création : '.$e->getMessage());
        }

        return redirect()->route('mentor.resources.index')->with('success', 'Ressource publiée avec succès. Elle est immédiatement visible.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        // $id contient le slug car getRouteKeyName() retourne 'slug' dans le modèle
        $resource = auth()->user()->resources()->where('slug', $id)->firstOrFail();

        $targetingOptions = $this->getDynamicTargetingOptions();

        return view('mentor.resources.edit', compact('resource', 'targetingOptions'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        // $id contient le slug
        $resource = auth()->user()->resources()->where('slug', $id)->firstOrFail();

        $messages = [
            'required' => 'Ce champ est obligatoire.',
            'string' => 'Ce champ doit être une chaîne de caractères.',
            'max' => 'La taille ne doit pas dépasser :max.',
            'in' => 'La valeur sélectionnée est invalide.',
            'integer' => 'Ce champ doit être un entier.',
            'min' => 'La valeur doit être au moins :min.',
            'file' => 'Le fichier doit être valide.',
            'image' => 'Le fichier doit être une image.',
            'file.max' => 'Le fichier est trop volumineux (Max 20 Mo).',
            'preview_image.max' => 'L\'image de couverture est trop volumineuse (Max 5 Mo).',
            'uploaded' => 'Le fichier est trop volumineux pour le serveur (limite technique atteinte).',
            'file.uploaded' => 'Le fichier dépasse la limite autorisée par le serveur.',
            'preview_image.uploaded' => 'L\'image dépasse la limite autorisée par le serveur.',
        ];

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'type' => 'required|in:article,video,tool,exercise,template,script,advertisement',

            'price' => 'nullable|integer',
            'is_premium' => 'required|in:0,1',
            'file' => 'nullable|file|max:20480', // 20MB
            'preview_image' => 'nullable|image|max:5120', // 5MB
            'metadata' => 'nullable|array',
            'mbti_types' => 'nullable|array',
            'tags' => 'nullable|string',
            'targeting' => 'nullable|array',
        ], $messages);

        // Vérification des crédits pour le ciblage (Loophole fix)
        $hasTargeting = ! empty($validated['targeting']) && (
            ! empty($validated['targeting']['education_levels']) ||
            ! empty($validated['targeting']['situations']) ||
            ! empty($validated['targeting']['countries']) ||
            ! empty($validated['targeting']['interests'])
        );

        $alreadyPaid = \App\Models\WalletTransaction::where('related_type', Resource::class)
            ->where('related_id', $resource->id)
            ->where('type', 'service_fee')
            ->exists();

        $needsPayment = $hasTargeting && ! $alreadyPaid;
        $cost = 0;

        if ($needsPayment) {
            $cost = $this->walletService->getFeatureCost('advanced_targeting', 10);
            if (auth()->user()->credits_balance < $cost) {
                return back()->withInput()->withErrors(['targeting' => "Crédits insuffisants. Le ciblage avancé coûte {$cost} crédits. Votre solde: ".auth()->user()->credits_balance]);
            }
        }

        // Validation conditionnelle pour le prix
        if ($request->is_premium == '1') {
            $request->validate([
                'price' => 'required|integer|min:200',
            ], [
                'price.required' => 'Le prix est obligatoire pour une ressource payante.',
                'price.min' => 'Le prix minimum pour une ressource payante est de 200 FCFA.',
            ]);
        }

        if ($request->hasFile('preview_image')) {
            if ($resource->preview_image_path) {
                Storage::disk('public')->delete($resource->preview_image_path);
            }
            $resource->preview_image_path = $request->file('preview_image')->store('resources/previews', 'public');
        }

        // Gérer le fichier principal si modifié
        if ($request->hasFile('file')) {
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $resource->file_path = $request->file('file')->store('resources/files', 'public');
        }

        $tags = ! empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        // Si la ressource était validée, elle doit être revalidée
        // On vérifie si elle est actuellement validée (is_validated = true)
        $wasValidated = $resource->is_validated;

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'price' => $request->is_premium == '1' ? $request->price : 0,
            'is_premium' => $request->is_premium == '1',
            'metadata' => $validated['metadata'] ?? [],
            'mbti_types' => $validated['mbti_types'] ?? [],
            'tags' => $tags,
            'targeting' => $validated['targeting'] ?? [],
        ];

        // Quand le mentor modifie, on remet la ressource en ligne et on efface le feedback admin
        $updateData['is_published'] = true;
        $updateData['is_validated'] = true;
        $updateData['validated_at'] = $resource->validated_at ?? now();
        $updateData['admin_feedback'] = null;
        $updateData['unpublished_at'] = null;

        try {
            DB::beginTransaction();

            $resource->update($updateData);

            // Débit réel si besoin de paiement
            if ($needsPayment && isset($cost)) {
                $this->walletService->deductCredits(
                    auth()->user(),
                    $cost,
                    'service_fee',
                    "Ciblage avancé pour: {$validated['title']}",
                    $resource
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : '.$e->getMessage());
        }

        return redirect()->route('mentor.resources.index')->with('success', 'Ressource mise à jour et republiée.');
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $resource = auth()->user()->resources()->where('slug', $id)->firstOrFail();

        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }
        if ($resource->preview_image_path) {
            Storage::disk('public')->delete($resource->preview_image_path);
        }

        $resource->delete();

        return redirect()->route('mentor.resources.index')->with('success', 'Ressource supprimée.');
    }

    /**
     * Récupère les options de ciblage dynamiques basées sur les données réelles des jeunes
     */
    private function getDynamicTargetingOptions()
    {
        // Labels
        $educationLabels = [
            'college' => 'Collège',
            'lycee' => 'Lycée',
            'bac' => 'Baccalauréat',
            'licence' => 'Licence / Bachelor',
            'master' => 'Master',
            'doctorat' => 'Doctorat',
        ];

        $situationLabels = [
            'etudiant' => 'Étudiant(e)',
            'recherche_emploi' => 'En recherche d\'emploi',
            'emploi' => 'En emploi',
            'entrepreneur' => 'Entrepreneur',
            'autre' => 'Autre',
        ];

        // Récupérer tous les jeunes ayant complété l'onboarding
        $users = User::where('user_type', 'jeune')
            ->where('onboarding_completed', true)
            ->select('country', 'onboarding_data')
            ->get();

        $countries = [];
        $educationLevels = [];
        $situations = [];
        $interests = [];

        foreach ($users as $user) {
            // Pays
            if ($user->country) {
                $countries[ucfirst(strtolower($user->country))] = $user->country;
            }

            // Données JSON
            $data = $user->onboarding_data ?? [];

            // Niveau d'études
            if (isset($data['education_level'])) {
                $level = $data['education_level'];
                if (isset($educationLabels[$level])) {
                    $educationLevels[$level] = $educationLabels[$level];
                } else {
                    $educationLevels[$level] = ucfirst($level);
                }
            }

            // Situation
            if (isset($data['current_situation'])) {
                $sit = $data['current_situation'];
                if (isset($situationLabels[$sit])) {
                    $situations[$sit] = $situationLabels[$sit];
                } else {
                    $situations[$sit] = ucfirst($sit);
                }
            }

            // Intérêts
            if (isset($data['interests']) && is_array($data['interests'])) {
                foreach ($data['interests'] as $interest) {
                    $interests[$interest] = $interest;
                }
            }
        }

        // Tri
        ksort($countries);
        $orderedEducation = [];
        foreach ($educationLabels as $key => $label) {
            if (isset($educationLevels[$key])) {
                $orderedEducation[$key] = $label;
            }
        }
        foreach ($educationLevels as $key => $label) {
            if (! isset($orderedEducation[$key])) {
                $orderedEducation[$key] = $label;
            }
        }
        sort($interests);

        return [
            'countries' => $countries, // [Label => Value]
            'education_levels' => $orderedEducation,
            'situations' => $situations,
            'interests' => array_values(array_unique($interests)),
        ];
    }
}
