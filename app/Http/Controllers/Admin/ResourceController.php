<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Resource\ResourceRejected;
use App\Models\Resource;
use App\Models\User;
use App\Services\MentorshipNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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

        // Si c'est un coach, on ne montre que les ressources validées et publiées
        if (auth()->user()->isCoach()) {
            $query->where('is_validated', true)->where('is_published', true);
        } else {
            // Filtre par statut (Admin uniquement)
            if ($request->filled('status')) {
                if ($request->status === 'unpublished') {
                    $query->where('is_published', false);
                } elseif ($request->status === 'published') {
                    $query->where('is_published', true);
                } elseif ($request->status === 'pending') {
                    // Compatibilité : ressources validates=false et published (créées avant la migration)
                    $query->where('is_validated', false)->where('is_published', true);
                }
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
        if (auth()->user()->isCoach() && (! $resource->is_validated || ! $resource->is_published)) {
            abort(403, 'Cette ressource n\'est pas accessible.');
        }

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
     * Valider une ressource legacy (créée avant la migration auto-publish)
     */
    public function approve(Resource $resource)
    {
        $resource->update([
            'is_validated' => true,
            'is_published' => true,
            'validated_at' => now(),
            'admin_feedback' => null,
            'unpublished_at' => null,
        ]);

        $this->notificationService->sendResourceValidated($resource);

        return back()->with('success', 'Ressource publiée.');
    }

    /**
     * Dépublier une ressource avec un message de feedback pour le mentor
     */
    public function unpublish(Request $request, Resource $resource)
    {
        $request->validate([
            'feedback' => 'required|string|min:10|max:1000',
        ], [
            'feedback.required' => 'Un message explicatif est obligatoire pour informer le mentor.',
            'feedback.min' => 'Le message doit faire au moins 10 caractères.',
        ]);

        $resource->update([
            'is_published' => false,
            'admin_feedback' => $request->feedback,
            'unpublished_at' => now(),
        ]);

        // Envoyer l'email au mentor avec le feedback
        $mentor = $resource->user;
        if ($mentor && $mentor->email) {
            Mail::to($mentor->email)->send(new ResourceRejected($resource));
        }

        return back()->with('warning', 'Ressource dépubliée. Le mentor a été notifié par email.');
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
