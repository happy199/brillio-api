<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\User;
use App\Services\MentorshipNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    protected $notificationService;

    public function __construct(MentorshipNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Liste des ressources
     */
    public function index(Request $request)
    {
        $query = Resource::with('user');

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_validated', false);
            } elseif ($request->status === 'published') {
                $query->where('is_published', true)->where('is_validated', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Recherche
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.resources.index', compact('resources'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $targetingOptions = $this->getDynamicTargetingOptions();

        return view('admin.resources.create', compact('targetingOptions'));
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
            'tags' => 'nullable|string', // Reçu comme string séparée par virgules
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
            'is_published' => true, // Admin publie directement
            'is_validated' => true, // Admin valide directement
            'validated_at' => now(),
        ]);

        return redirect()->route('admin.resources.index')->with('success', 'Ressource créée avec succès.');
    }

    /**
     * Affichage détail
     */
    public function show(Resource $resource)
    {
        return view('admin.resources.show', compact('resource'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Resource $resource)
    {
        $targetingOptions = $this->getDynamicTargetingOptions();

        return view('admin.resources.edit', compact('resource', 'targetingOptions'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Resource $resource)
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

        // Validation conditionnelle pour le prix
        if ($request->is_premium == '1') {
            $request->validate([
                'price' => 'required|integer|min:200',
            ], [
                'price.required' => 'Le prix est obligatoire pour une ressource payante.',
                'price.min' => 'Le prix minimum pour une ressource payante est de 200 FCFA.',
            ]);
        }

        if ($request->hasFile('file')) {
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $resource->file_path = $request->file('file')->store('resources/files', 'public');
        }

        if ($request->hasFile('preview_image')) {
            if ($resource->preview_image_path) {
                Storage::disk('public')->delete($resource->preview_image_path);
            }
            $resource->preview_image_path = $request->file('preview_image')->store('resources/previews', 'public');
        }

        $tags = ! empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        $resource->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'price' => $request->is_premium == '1' ? $request->price : 0,
            'is_premium' => $request->is_premium == '1', // Correction ici
            'metadata' => $validated['metadata'] ?? [],
            'mbti_types' => $validated['mbti_types'] ?? [],
            'tags' => $tags,
            'targeting' => $validated['targeting'] ?? [],
        ]);

        return redirect()->route('admin.resources.index')->with('success', 'Ressource mise à jour.');
    }

    /**
     * Suppression
     */
    public function destroy(Resource $resource)
    {
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }
        if ($resource->preview_image_path) {
            Storage::disk('public')->delete($resource->preview_image_path);
        }

        $resource->delete();

        return redirect()->route('admin.resources.index')->with('success', 'Ressource supprimée.');
    }

    /**
     * Valider une ressource
     */
    public function approve(Resource $resource)
    {
        $resource->update([
            'is_validated' => true,
            'is_published' => true,
            'validated_at' => now(),
        ]);

        $this->notificationService->sendResourceValidated($resource);

        return back()->with('success', 'Ressource validée et publiée.');
    }

    /**
     * Valider toutes les ressources en attente
     */
    public function approveAll()
    {
        $resources = Resource::where('is_validated', false)->get();

        Resource::where('is_validated', false)->update([
            'is_validated' => true,
            'is_published' => true,
            'validated_at' => now(),
        ]);

        foreach ($resources as $resource) {
            $this->notificationService->sendResourceValidated($resource);
        }

        return back()->with('success', 'Toutes les ressources en attente ont été validées.');
    }

    /**
     * Rejeter une ressource
     */
    public function reject(Resource $resource)
    {
        $resource->update([
            'is_published' => false,
            // On garde is_validated a false ou on pourrait ajouter un champ 'rejected_at'
        ]);

        $this->notificationService->sendResourceRejected($resource);

        return back()->with('warning', 'Ressource retirée de la publication.');
    }

    /**
     * Récupère les options de ciblage dynamiques basées sur les données réelles des jeunes
     */
    private function getDynamicTargetingOptions()
    {
        // Labels (Mappage statique pour l'affichage propre)
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
            'countries' => $countries,
            'education_levels' => $orderedEducation,
            'situations' => $situations,
            'interests' => array_values(array_unique($interests)),
        ];
    }
}
