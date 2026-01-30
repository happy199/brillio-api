<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        $mentorProfile = auth()->user()->mentorProfile;

        // Vérifier si le profil est publié
        if (!$mentorProfile || $mentorProfile->status !== 'published') {
            return view('mentor.resources.index', [
                'resources' => collect([]),
                'profileNotPublished' => true
            ]);
        }

        $resources = auth()->user()->resources()->orderBy('created_at', 'desc')->paginate(12);
        return view('mentor.resources.index', compact('resources'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $mentorProfile = auth()->user()->mentorProfile;

        // Bloquer si le profil n'est pas publié
        if (!$mentorProfile || $mentorProfile->status !== 'published') {
            return redirect()->route('mentor.resources.index')
                ->withErrors(['profile' => 'Vous devez publier votre profil mentorpour créer des ressources.']);
        }

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
        $hasTargeting = !empty($validated['targeting']) && (
            !empty($validated['targeting']['education_levels']) ||
            !empty($validated['targeting']['situations']) ||
            !empty($validated['targeting']['countries']) ||
            !empty($validated['targeting']['interests'])
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
                    return back()->withInput()->withErrors(['targeting' => "Crédits insuffisants. Le ciblage avancé coûte {$cost} crédits. Votre solde: " . auth()->user()->credits_balance]);
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
        $tags = !empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        // Création Transactionnelle
        try {
            DB::beginTransaction();

            $resource = Resource::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']) . '-' . uniqid(),
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
                'is_validated' => false,
                'validated_at' => null,
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
            return back()->withInput()->with('error', "Erreur lors de la création : " . $e->getMessage());
        }

        return redirect()->route('mentor.resources.index')->with('success', 'Ressource créée ! Elle sera visible après validation par un administrateur.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $resource = auth()->user()->resources()->findOrFail($id);
        $targetingOptions = $this->getDynamicTargetingOptions();
        return view('mentor.resources.edit', compact('resource', 'targetingOptions'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $resource = auth()->user()->resources()->findOrFail($id);

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

        $tags = !empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        // Si la ressource était validée (approved), la repasser en pending pour nouvelle validation
        $needsRevalidation = $resource->status === 'approved';

        $resource->update([
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
            'status' => $needsRevalidation ? 'pending' : $resource->status, // Repasser en pending si était validée
        ]);

        $message = $needsRevalidation
            ? 'Ressource mise à jour. Elle sera à nouveau soumise à validation admin.'
            : 'Ressource mise à jour.';

        return redirect()->route('mentor.resources.index')->with('success', $message);
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $resource = auth()->user()->resources()->findOrFail($id);

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
            'doctorat' => 'Doctorat'
        ];

        $situationLabels = [
            'etudiant' => 'Étudiant(e)',
            'recherche_emploi' => 'En recherche d\'emploi',
            'emploi' => 'En emploi',
            'entrepreneur' => 'Entrepreneur',
            'autre' => 'Autre'
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
            if (!isset($orderedEducation[$key])) {
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
