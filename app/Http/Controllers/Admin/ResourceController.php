<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
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
        return view('admin.resources.create');
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'type' => 'required|in:article,video,tool,exercise,template,script,advertisement',
            'price' => 'nullable|integer|min:0',
            'is_premium' => 'nullable|boolean',
            'file' => 'nullable|file|max:10240', // 10MB
            'preview_image' => 'nullable|image|max:2048', // 2MB
            'metadata' => 'nullable|array',
            'mbti_types' => 'nullable|array',
            'tags' => 'nullable|string', // Reçu comme string séparée par virgules
        ]);

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

        $resource = Resource::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'description' => $validated['description'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'price' => $validated['price'] ?? 0,
            'is_premium' => $request->has('is_premium'),
            'file_path' => $filePath,
            'preview_image_path' => $previewPath,
            'metadata' => $validated['metadata'] ?? [],
            'mbti_types' => $validated['mbti_types'] ?? [],
            'tags' => $tags,
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
        return view('admin.resources.edit', compact('resource'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'type' => 'required|in:article,video,tool,exercise,template,script,advertisement',
            'price' => 'nullable|integer|min:0',
            'is_premium' => 'nullable|boolean',
            'file' => 'nullable|file|max:10240',
            'preview_image' => 'nullable|image|max:2048',
            'metadata' => 'nullable|array',
            'mbti_types' => 'nullable|array',
            'tags' => 'nullable|string',
        ]);

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

        $tags = !empty($request->tags) ? array_map('trim', explode(',', $request->tags)) : [];

        $resource->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'price' => $validated['price'] ?? 0,
            'is_premium' => $request->has('is_premium'),
            'metadata' => $validated['metadata'] ?? [],
            'mbti_types' => $validated['mbti_types'] ?? [],
            'tags' => $tags,
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

        return back()->with('success', 'Ressource validée et publiée.');
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

        return back()->with('warning', 'Ressource retirée de la publication.');
    }
}
