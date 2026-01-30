<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller pour la gestion des mentors dans le dashboard admin
 */
class MentorController extends Controller
{
    /**
     * Liste des spécialisations
     */
    protected array $specializations = [
        'tech' => 'Technologie',
        'business' => 'Business & Management',
        'health' => 'Santé',
        'education' => 'Éducation',
        'arts' => 'Arts & Culture',
        'engineering' => 'Ingénierie',
        'law' => 'Droit',
        'finance' => 'Finance',
        'marketing' => 'Marketing',
        'other' => 'Autre',
    ];

    /**
     * Liste tous les profils mentors
     */
    public function index(Request $request)
    {
        $query = MentorProfile::with(['user', 'roadmapSteps', 'specializationModel']);

        // Filtre par statut de publication
        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        // Filtre par spécialisation
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        // Recherche
        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $mentors = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistiques
        $stats = [
            'total' => MentorProfile::count(),
            'published' => MentorProfile::where('is_published', true)->count(),
            'draft' => MentorProfile::where('is_published', false)->count(),
            'total_steps' => RoadmapStep::count(),
        ];

        return view('admin.mentors.index', [
            'mentors' => $mentors,
            'stats' => $stats,
            'specializations' => $this->specializations,
        ]);
    }

    /**
     * Affiche le détail d'un profil mentor
     */
    public function show(MentorProfile $mentor)
    {
        $mentor->load(['user', 'roadmapSteps', 'specializationModel']);

        return view('admin.mentors.show', [
            'mentor' => $mentor,
            'specializations' => $this->specializations,
        ]);
    }

    /**
     * Toggle publication du profil mentor
     */
    public function togglePublish(MentorProfile $mentor)
    {
        $mentor->update([
            'is_published' => !$mentor->is_published,
        ]);

        return back()->with(
            'success',
            $mentor->is_published
            ? 'Profil mentor publié.'
            : 'Profil mentor dépublié.'
        );
    }

    /**
     * Approuve et publie un profil mentor
     */
    public function approve(MentorProfile $mentor)
    {
        $mentor->is_published = true;
        $mentor->is_validated = true;
        $mentor->validated_at = now();
        $mentor->save();

        return back()->with('success', "Le profil de {$mentor->user->name} a été validé et publié");
    }

    /**
     * Rejette (dépublie) un profil mentor
     */
    public function reject(MentorProfile $mentor)
    {
        $mentor->is_published = false;
        $mentor->save();

        return back()->with('warning', "Le profil de {$mentor->user->name} a été retiré");
    }

    /**
     * Télécharge le fichier profil LinkedIn du mentor
     */
    public function downloadLinkeInProfile(MentorProfile $mentor)
    {
        if (!$mentor->linkedin_pdf_path) {
            return back()->with('error', 'Aucun fichier profil associé.');
        }

        // Le fichier est stocké sur le disque 'local' (storage/app/linkedin-pdfs)
        if (!Storage::disk('local')->exists($mentor->linkedin_pdf_path)) {
            return back()->with('error', 'Fichier introuvable sur le serveur.');
        }

        return Storage::disk('local')->download(
            $mentor->linkedin_pdf_path,
            $mentor->linkedin_pdf_original_name ?? 'profil-linkedin.pdf'
        );
    }

    /**
     * Affiche le formulaire d'édition du profil mentor
     */
    public function edit(MentorProfile $mentor)
    {
        $mentor->load(['user', 'roadmapSteps', 'specializationModel']);

        $specializations = \App\Models\Specialization::where('status', 'approved')
            ->orderBy('name')
            ->get();

        return view('admin.mentors.edit', [
            'mentor' => $mentor,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Met à jour le profil du mentor
     */
    public function update(Request $request, MentorProfile $mentor)
    {
        $validated = $request->validate([
            // Informations utilisateur
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $mentor->user_id,
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',

            // Informations profil mentor
            'bio' => 'nullable|string|max:2000',
            'advice' => 'nullable|string|max:2000',
            'current_position' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0|max:60',
            'specialization_id' => 'nullable|exists:specializations,id',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',

            // Liens
            'linkedin_url' => 'nullable|url|max:500',
            'website_url' => 'nullable|url|max:500',

            // Statut
            'is_published' => 'boolean',
            'is_validated' => 'boolean',
        ]);

        // Mise à jour des informations utilisateur
        $mentor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);

        // Mise à jour du profil mentor
        $profileData = [
            'bio' => $validated['bio'] ?? null,
            'advice' => $validated['advice'] ?? null,
            'current_position' => $validated['current_position'] ?? null,
            'current_company' => $validated['current_company'] ?? null,
            'years_of_experience' => $validated['years_of_experience'] ?? null,
            'specialization_id' => $validated['specialization_id'] ?? null,
            'skills' => $validated['skills'] ?? [],
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'is_published' => $request->has('is_published'),
            'is_validated' => $request->has('is_validated'),
        ];

        // Si validé pour la première fois, enregistrer la date
        if ($request->has('is_validated') && !$mentor->is_validated) {
            $profileData['validated_at'] = now();
        }

        $mentor->update($profileData);

        return redirect()
            ->route('admin.mentors.edit', $mentor)
            ->with('success', "Le profil de {$mentor->user->name} a été mis à jour avec succès.");
    }

    /**
     * Met à jour la photo de profil du mentor
     */
    public function updateProfilePhoto(Request $request, MentorProfile $mentor)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($mentor->user->profile_photo && Storage::disk('public')->exists($mentor->user->profile_photo)) {
                Storage::disk('public')->delete($mentor->user->profile_photo);
            }

            // Stocker la nouvelle photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');

            $mentor->user->update([
                'profile_photo' => $path,
            ]);

            return back()->with('success', 'Photo de profil mise à jour avec succès.');
        }

        return back()->with('error', 'Aucune photo sélectionnée.');
    }

    /**
     * Ajoute une étape de roadmap au profil mentor
     */
    public function storeRoadmapStep(Request $request, MentorProfile $mentor)
    {
        $validated = $request->validate([
            'step_type' => 'required|in:' . implode(',', array_keys(\App\Models\RoadmapStep::STEP_TYPES)),
            'title' => 'required|string|max:255',
            'institution_company' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
        ]);

        // Déterminer la position (dernière position + 1)
        $maxPosition = $mentor->roadmapSteps()->max('position') ?? 0;
        $validated['position'] = $maxPosition + 1;
        $validated['mentor_profile_id'] = $mentor->id;

        \App\Models\RoadmapStep::create($validated);

        return back()->with('success', 'Étape ajoutée avec succès.');
    }

    /**
     * Met à jour une étape de roadmap
     */
    public function updateRoadmapStep(Request $request, MentorProfile $mentor, \App\Models\RoadmapStep $step)
    {
        // Vérifier que l'étape appartient bien à ce mentor
        if ($step->mentor_profile_id !== $mentor->id) {
            return back()->with('error', 'Étape non trouvée.');
        }

        $validated = $request->validate([
            'step_type' => 'required|in:' . implode(',', array_keys(\App\Models\RoadmapStep::STEP_TYPES)),
            'title' => 'required|string|max:255',
            'institution_company' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
        ]);

        $step->update($validated);

        return back()->with('success', 'Étape mise à jour avec succès.');
    }

    /**
     * Supprime une étape de roadmap
     */
    public function deleteRoadmapStep(MentorProfile $mentor, \App\Models\RoadmapStep $step)
    {
        // Vérifier que l'étape appartient bien à ce mentor
        if ($step->mentor_profile_id !== $mentor->id) {
            return back()->with('error', 'Étape non trouvée.');
        }

        $step->delete();

        return back()->with('success', 'Étape supprimée avec succès.');
    }
}
