<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecializationController extends Controller
{
    /**
     * Liste des spécialisations
     */
    public function index(Request $request)
    {
        $query = Specialization::query()->withCount('mentorProfiles');

        // Filtres
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Recherche
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $specializations = $query->orderBy('name')->paginate(20);

        return view('admin.specializations.index', compact('specializations'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $mbtiSectors = $this->getMbtiSectors();

        return view('admin.specializations.create', compact('mbtiSectors'));
    }

    /**
     * Enregistrer une nouvelle spécialisation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,pending,archived',
            'mbti_types' => 'nullable|array',
            'mbti_types.*' => 'string|max:50',
        ]);

        $specialization = Specialization::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'created_by_admin' => true,
        ]);

        // Lier les types MBTI
        if (! empty($validated['mbti_types'])) {
            $specialization->syncMbtiTypes($validated['mbti_types']);
        }

        return redirect()
            ->route('admin.specializations.index')
            ->with('success', 'Spécialisation créée avec succès');
    }

    /**
     * Afficher une spécialisation
     */
    public function show(Specialization $specialization)
    {
        $specialization->load(['mentorProfiles.user', 'mbtiTypes']);

        return view('admin.specializations.show', compact('specialization'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Specialization $specialization)
    {
        $specialization->load('mbtiTypes');
        $mbtiSectors = $this->getMbtiSectors();
        $selectedMbtiTypes = $specialization->mbtiTypes->pluck('mbti_type_code')->toArray();

        return view('admin.specializations.edit', compact('specialization', 'mbtiSectors', 'selectedMbtiTypes'));
    }

    /**
     * Mettre à jour une spécialisation
     */
    public function update(Request $request, Specialization $specialization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specializations,name,'.$specialization->id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,pending,archived',
            'mbti_types' => 'nullable|array',
            'mbti_types.*' => 'string|max:50',
        ]);

        $specialization->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        // Mettre à jour les types MBTI
        if (isset($validated['mbti_types'])) {
            $specialization->syncMbtiTypes($validated['mbti_types']);
        } else {
            $specialization->syncMbtiTypes([]);
        }

        return redirect()
            ->route('admin.specializations.index')
            ->with('success', 'Spécialisation mise à jour avec succès');
    }

    /**
     * Supprimer ou archiver une spécialisation
     */
    public function destroy(Specialization $specialization)
    {
        $mentorCount = $specialization->mentorProfiles()->count();

        if ($mentorCount > 0) {
            // Archiver au lieu de supprimer
            $specialization->update(['status' => 'archived']);

            return redirect()
                ->route('admin.specializations.index')
                ->with('warning', "Spécialisation archivée car {$mentorCount} mentor(s) y sont liés");
        }

        // Supprimer si aucun mentor
        $specialization->delete();

        return redirect()
            ->route('admin.specializations.index')
            ->with('success', 'Spécialisation supprimée avec succès');
    }

    /**
     * Liste des suggestions en attente de modération
     */
    public function moderate()
    {
        $pendingSpecializations = Specialization::pending()
            ->withCount('mentorProfiles')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.specializations.moderate', compact('pendingSpecializations'));
    }

    /**
     * Approuver une suggestion
     */
    public function approve(Specialization $specialization)
    {
        $specialization->update(['status' => 'active']);

        return redirect()
            ->route('admin.specializations.moderate')
            ->with('success', 'Spécialisation approuvée');
    }

    /**
     * Rejeter une suggestion
     */
    public function reject(Specialization $specialization)
    {
        // Détacher les mentors liés
        MentorProfile::where('specialization_id', $specialization->id)
            ->update(['specialization_id' => null]);

        $specialization->delete();

        return redirect()
            ->route('admin.specializations.moderate')
            ->with('success', 'Suggestion rejetée et supprimée');
    }

    /**
     * Obtenir les secteurs MBTI disponibles
     */
    private function getMbtiSectors()
    {
        return [
            'tech' => 'Technologie',
            'finance' => 'Finance',
            'health' => 'Santé',
            'education' => 'Éducation',
            'engineering' => 'Ingénierie',
            'environment' => 'Environnement',
            'law' => 'Droit',
            'creative' => 'Créatif',
            'communication' => 'Communication',
            'social' => 'Social',
        ];
    }
}
