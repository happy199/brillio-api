<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $user = auth()->user();
        $isCoach = $user->isCoach();
        $query = MentorProfile::with(['user', 'roadmapSteps', 'specializationModel']);

        // Si c'est un coach, on ne montre que les profils publiés
        if ($isCoach) {
            $query->where('is_published', true);
        }
        else {
            // Filtre par statut de publication (Admin uniquement)
            if ($request->filled('status')) {
                $query->where('is_published', $request->status === 'published');
            }
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
        if ($isCoach) {
            $stats = [
                'total' => MentorProfile::where('is_published', true)->count(),
                'published' => MentorProfile::where('is_published', true)->count(),
                'draft' => 0,
                'total_steps' => RoadmapStep::whereHas('mentorProfile', function ($q) {
                $q->where('is_published', true);
            })->count(),
            ];
        }
        else {
            $stats = [
                'total' => MentorProfile::count(),
                'published' => MentorProfile::where('is_published', true)->count(),
                'draft' => MentorProfile::where('is_published', false)->count(),
                'total_steps' => RoadmapStep::count(),
            ];
        }

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
        if (auth()->user()->isCoach() && !$mentor->is_published) {
            abort(403, 'Ce profil mentor n\'est pas encore publié.');
        }

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

        // Notifier le mentor
        try {
            \Illuminate\Support\Facades\Mail::to($mentor->user->email)->send(new \App\Mail\MentorVerifiedMail($mentor));
        }
        catch (\Exception $e) {
            \Log::error('Erreur envoi email validation mentor (approve): ' . $e->getMessage());
        }

        return back()->with('success', "Le profil de {$mentor->user->name} a été validé et publié");
    }

    /**
     * Toggle validation du profil mentor
     */
    public function toggleValidation(MentorProfile $mentor)
    {
        $oldValue = $mentor->is_validated;
        $mentor->is_validated = !$mentor->is_validated;

        if ($mentor->is_validated) {
            $mentor->validated_at = now();
        }

        $mentor->save();

        // Si on passe de non-validé à validé, on envoie l'email
        if (!$oldValue && $mentor->is_validated) {
            try {
                \Illuminate\Support\Facades\Mail::to($mentor->user->email)->send(new \App\Mail\MentorVerifiedMail($mentor));
            }
            catch (\Exception $e) {
                \Log::error('Erreur envoi email validation mentor (toggle): ' . $e->getMessage());
            }
        }

        return back()->with(
            'success',
            $mentor->is_validated
            ? "Profil de {$mentor->user->name} marqué comme vérifié."
            : "Profil de {$mentor->user->name} n'est plus marqué comme vérifié."
        );
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
            \Log::warning('Missing LinkedIn PDF file requested for download', [
                'mentor_id' => $mentor->id,
                'mentor_name' => $mentor->user->name,
                'file_path' => $mentor->linkedin_pdf_path,
            ]);

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
    public function __construct(
        private \App\Services\UserAvatarService $avatarService,
        private \App\Services\LinkedInPdfParserService $parserService
        )
    {
    }

    /**
     * Recharge les données LinkedIn à partir du PDF existant
     */
    public function reloadLinkedInProfile(MentorProfile $mentor)
    {
        // 1. Vérification de l'existence du chemin en base
        if (!$mentor->linkedin_pdf_path) {
            return response()->json([
                'success' => false,
                'needs_upload' => true,
                'error' => 'Aucun PDF LinkedIn n’est associé à ce profil.',
            ], 404);
        }

        // 2. Vérification physique du fichier sur le disque
        if (!Storage::disk('local')->exists($mentor->linkedin_pdf_path)) {
            Log::warning('LinkedIn PDF missing during admin reload', [
                'mentor_id' => $mentor->id,
                'path' => $mentor->linkedin_pdf_path,
            ]);

            return response()->json([
                'success' => false,
                'needs_upload' => true,
                'error' => 'Le fichier PDF est introuvable sur le serveur. Un nouvel upload est nécessaire.',
            ], 404);
        }

        try {
            $fullPath = storage_path('app/' . $mentor->linkedin_pdf_path);
            $profileData = $this->parserService->parsePdf($fullPath);

            $this->processLinkedInImport($mentor, $profileData);

            return response()->json([
                'success' => true,
                'message' => 'Profil rechargé avec succès depuis le PDF existant.',
            ]);
        }
        catch (\Exception $e) {
            Log::error('Admin LinkedIn reload error', [
                'mentor_id' => $mentor->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l’analyse du PDF : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload un nouveau PDF LinkedIn et met à jour le profil
     */
    public function uploadLinkedInProfile(Request $request, MentorProfile $mentor)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // 10MB
        ]);

        try {
            // Stocker le PDF
            $finalPdfPath = $request->file('pdf')->store('linkedin-pdfs', 'local');
            $originalName = $request->file('pdf')->getClientOriginalName();

            // Parser le PDF
            $fullPath = storage_path('app/' . $finalPdfPath);
            $profileData = $this->parserService->parsePdf($fullPath);

            // Mettre à jour les métadonnées de fichier
            $mentor->update([
                'linkedin_pdf_path' => $finalPdfPath,
                'linkedin_pdf_original_name' => $originalName,
            ]);

            $this->processLinkedInImport($mentor, $profileData);

            return response()->json([
                'success' => true,
                'message' => 'Nouveau PDF uploadé et profil mis à jour avec succès.',
            ]);
        }
        catch (\Exception $e) {
            Log::error('Admin LinkedIn upload error', [
                'mentor_id' => $mentor->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'upload ou de l\'analyse : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logique partagée pour traiter les données importées
     */
    private function processLinkedInImport(MentorProfile $mentor, array $profileData)
    {
        // Supprimer les anciennes étapes
        $mentor->roadmapSteps()->delete();

        // Calculer l'expérience
        $totalMonths = 0;
        if (!empty($profileData['experience'])) {
            foreach ($profileData['experience'] as $exp) {
                if (isset($exp['duration_years'])) {
                    $totalMonths += ($exp['duration_years'] * 12);
                }
                if (isset($exp['duration_months'])) {
                    $totalMonths += $exp['duration_months'];
                }
            }
        }
        $yearsOfExperience = round($totalMonths / 12);

        // Récupérer la dernière expérience
        $latestExperience = !empty($profileData['experience']) ? $profileData['experience'][0] : null;

        // Mise à jour du profil (on préserve bio/advice si déjà remplis)
        $mentor->update([
            'linkedin_raw_data' => $profileData,
            'linkedin_imported_at' => now(),
            'linkedin_import_count' => $mentor->linkedin_import_count + 1,
            'current_position' => $mentor->current_position ?: ($latestExperience['title'] ?? null),
            'current_company' => $mentor->current_company ?: ($latestExperience['company'] ?? null),
            'bio' => $mentor->bio ?: ($profileData['headline'] ?? null),
            'skills' => !empty($profileData['skills']) ? $profileData['skills'] : $mentor->skills,
            'years_of_experience' => $yearsOfExperience > 0 ? $yearsOfExperience : $mentor->years_of_experience,
            // On ne touche pas aux URLs de contact ici pour éviter d'écraser des modifs manuelles de l'admin
            // sauf si elles étaient vides
            'linkedin_url' => $mentor->linkedin_url ?: $this->formatUrl($profileData['contact']['linkedin'] ?? null),
            'website_url' => $mentor->website_url ?: $this->formatUrl($profileData['contact']['website'] ?? null),
        ]);

        // Importer les expériences
        $stepPosition = 0;
        if (!empty($profileData['experience'])) {
            foreach ($profileData['experience'] as $exp) {
                $startDate = !empty($exp['start_date'])
                    ? (strlen($exp['start_date']) === 4 ? $exp['start_date'] . '-01-01' : $exp['start_date'])
                    : null;

                $endDate = null;
                if (array_key_exists('end_date', $exp)) {
                    $endDate = !empty($exp['end_date'])
                        ? (strlen($exp['end_date']) === 4 ? $exp['end_date'] . '-12-31' : $exp['end_date'])
                        : null;
                }

                $mentor->roadmapSteps()->create([
                    'step_type' => 'work',
                    'title' => $exp['title'] ?? 'Sans titre',
                    'institution_company' => $exp['company'] ?? null,
                    'description' => trim($exp['description'] ?? ''),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'position' => $stepPosition++,
                ]);
            }
        }

        // Importer les formations
        if (!empty($profileData['education'])) {
            foreach ($profileData['education'] as $edu) {
                $mentor->roadmapSteps()->create([
                    'step_type' => 'education',
                    'title' => $edu['degree'] ?? 'Formation',
                    'institution_company' => $edu['school'] ?? null,
                    'description' => 'Formation académique',
                    'start_date' => !empty($edu['year_start']) ? $edu['year_start'] . '-01-01' : null,
                    'end_date' => !empty($edu['year_end']) ? $edu['year_end'] . '-12-31' : null,
                    'position' => $stepPosition++,
                ]);
            }
        }
    }

    /**
     * Helper pour formater les URLs
     */
    private function formatUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }
        $url = trim($url);
        if (!preg_match('/^https?:\/\//i', $url)) {
            return 'https://' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * Met à jour le profil du mentor
     */
    public function update(Request $request, MentorProfile $mentor)
    {
        // Pour l'admin, on rend les champs optionnels.
        // Si un champ obligatoire (comme name/email) est vide, on garde l'ancienne valeur.
        $validated = $request->validate([
            // Informations utilisateur
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $mentor->user_id,
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'profile_photo_url' => 'nullable|url|max:500',

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

        // Mise à jour des informations utilisateur UNIQUEMENT si fournies
        $userUpdateData = [];
        if (!empty($validated['name'])) {
            $userUpdateData['name'] = $validated['name'];
        }
        if (!empty($validated['email'])) {
            $userUpdateData['email'] = $validated['email'];
        }
        if (array_key_exists('phone', $validated)) {
            $userUpdateData['phone'] = $validated['phone'];
        }
        if (array_key_exists('city', $validated)) {
            $userUpdateData['city'] = $validated['city'];
        }
        if (array_key_exists('country', $validated)) {
            $userUpdateData['country'] = $validated['country'];
        }

        if (!empty($userUpdateData)) {
            $mentor->user->update($userUpdateData);
        }

        // Si une URL de photo est fournie, on essaie de la télécharger
        // On retire la vérification !== pour permettre de forcer le re-téléchargement si nécessaire
        if (!empty($validated['profile_photo_url'])) {
            // Si l'URL est différente OU si on veut juste s'assurer qu'elle est bien là
            if ($validated['profile_photo_url'] !== $mentor->user->profile_photo_url || !$mentor->user->profile_photo_path) {
                $path = $this->avatarService->downloadFromUrl($mentor->user, $validated['profile_photo_url']);

                if (!$path) {
                    return back()->withInput()->with('error', "Impossible de télécharger l'image depuis l'URL LinkedIn fournie. L'URL est peut-être expirée ou inaccessible.");
                }
            }
        }

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

        // Si validé pour la première fois, enregistrer la date et notifier
        $previouslyValidated = $mentor->is_validated;
        $newValidated = $request->has('is_validated');

        if ($newValidated && !$previouslyValidated) {
            $profileData['validated_at'] = now();

            // On envoie l'email après l'update pour être sûr que tout est en base
            $mentor->update($profileData);

            try {
                \Illuminate\Support\Facades\Mail::to($mentor->user->email)->send(new \App\Mail\MentorVerifiedMail($mentor));
            }
            catch (\Exception $e) {
                \Log::error('Erreur envoi email validation mentor (update): ' . $e->getMessage());
            }
        }
        else {
            $mentor->update($profileData);
        }

        return redirect()
            ->route('admin.mentors.index')
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
            $this->avatarService->upload($mentor->user, $request->file('profile_photo'));

            return back()->with('success', 'Photo de profil mise à jour avec succès.');
        }

        return back()->with('error', 'Aucune photo sélectionnée.');
    }

    /**
     * Supprime la photo de profil du mentor
     */
    public function deleteProfilePhoto(MentorProfile $mentor)
    {
        $this->avatarService->delete($mentor->user);

        return back()->with('success', 'Photo de profil supprimée avec succès.');
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

    /**
     * Rétrograde un mentor en jeune
     */
    public function demote(MentorProfile $mentor)
    {
        $user = $mentor->user;

        // 1. Archiver le compte mentor
        $user->update([
            'user_type' => 'jeune',
            'is_archived' => true,
            'archived_at' => now(),
            'archived_reason' => 'Rétrogradation administrative de Mentor à Jeune.',
        ]);

        // 2. Notifier l'utilisateur
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\Admin\DemotionNotificationMail($user));
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur envoi notification rétrogradation: ' . $e->getMessage());
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Le mentor {$user->name} a été rétrogradé en jeune et son compte a été archivé pour la transition.");
    }
}