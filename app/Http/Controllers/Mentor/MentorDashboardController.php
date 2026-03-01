<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MentorDashboardController extends Controller
{
    /**
     * Dashboard principal du mentor
     */
    public function index()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        // Stats
        $stats = [
            'profile_views' => $profile ? $profile->profile_views : 0,
            'roadmap_steps' => $profile ? $profile->roadmapSteps()->count() : 0,
            'is_published' => $profile ? $profile->is_published : false,
            'profile_complete' => $profile ? $profile->isComplete() : false,
        ];

        return view('mentor.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    /**
     * Page du profil mentor
     */
    public function profile()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        // Charger les spécialisations actives depuis la base de données
        $specializations = \App\Models\Specialization::active()
            ->orderBy('name')
            ->get();

        return view('mentor.profile', [
            'user' => $user,
            'profile' => $profile,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Mise a jour du profil mentor
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'bio' => 'required|string|max:2000',
            'current_position' => 'required|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'years_of_experience' => 'required|integer|min:0|max:50',
            'specialization_id' => 'required|string', // Peut être un ID ou 'new'
            'new_specialization_name' => 'nullable|required_if:specialization_id,new|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'advice' => 'nullable|string|max:1000',
            'is_published' => 'nullable|boolean',
            'profile_photo' => 'nullable|image|max:5120', // 5MB max
        ]);

        // Nettoyer les URLs avant de continuer (si non vides et sans protocole)
        $request->merge([
            'linkedin_url' => $this->formatUrl($request->linkedin_url),
            'website_url' => $this->formatUrl($request->website_url),
        ]);

        // Re-valider uniquement les URLs après formatage pour être sur qu'elles passent
        $request->validate([
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
        ]);

        // Mettre à jour les données validées avec les URLs formatées
        $validated['linkedin_url'] = $request->linkedin_url;
        $validated['website_url'] = $request->website_url;

        $validated['is_published'] = $request->has('is_published');

        $user = auth()->user();
        $profile = $user->mentorProfile;

        // 📸 LOG DE SÉCURITÉ : On trace l'état des photos avant la mise à jour
        \Log::info('🛡️ Photo safety check - Before updateProfile', [
            'user_id' => $user->id,
            'photo_path' => $user->profile_photo_path,
            'photo_url' => $user->profile_photo_url,
        ]);

        // Validation pour la publication
        if ($validated['is_published']) {
            // Vérifier si l'utilisateur a une photo (locale ou URL) et si l'upload en cours n'est pas vide
            $hasPhoto = $user->profile_photo_path || $user->profile_photo_url;
            $isUploading = $request->hasFile('profile_photo');

            if (! $hasPhoto && ! $isUploading) {
                return back()->withErrors(['is_published' => 'Vous devez ajouter une photo de profil pour rendre votre profil visible aux jeunes.'])->withInput();
            }
        }

        // Gérer l'upload de la photo
        if ($request->hasFile('profile_photo')) {
            // Supprimer l'ancienne photo si elle est locale
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Stocker la nouvelle photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
            $user->save();
        }

        // Gérer la spécialisation
        if ($validated['specialization_id'] === 'new' && ! empty($validated['new_specialization_name'])) {
            // Vérifier si une spécialisation avec ce nom existe déjà
            $existingSpec = \App\Models\Specialization::where('name', $validated['new_specialization_name'])
                ->orWhere('slug', Str::slug($validated['new_specialization_name']))
                ->first();

            if ($existingSpec) {
                // Si elle existe déjà, utiliser celle-ci
                $validated['specialization_id'] = $existingSpec->id;

                if ($existingSpec->status === 'pending') {
                    $message = 'Votre profil a été mis à jour. Ce domaine est déjà en attente de validation par un administrateur.';
                } elseif ($existingSpec->status === 'active') {
                    $message = 'Votre profil a été mis à jour. Le domaine "'.$existingSpec->name.'" a été sélectionné.';
                } else {
                    $message = 'Votre profil a été mis à jour.';
                }
            } else {
                // Créer une nouvelle spécialisation en attente de modération
                $newSpec = \App\Models\Specialization::create([
                    'name' => $validated['new_specialization_name'],
                    'status' => 'pending',
                    'created_by_admin' => false,
                ]);
                $validated['specialization_id'] = $newSpec->id;

                $message = 'Votre profil a été mis à jour. Votre suggestion de domaine d\'expertise sera examinée par un administrateur.';
            }
        } else {
            $validated['specialization_id'] = (int) $validated['specialization_id'];
            $message = 'Votre profil a été mis à jour.';
        }

        // Supprimer les champs non nécessaires
        unset($validated['new_specialization_name']);
        unset($validated['profile_photo']);

        if ($profile) {
            $profile->update($validated);

            // Mettre à jour le compteur de mentors pour l'ancienne et nouvelle spécialisation
            if ($profile->wasChanged('specialization_id')) {
                if ($profile->getOriginal('specialization_id')) {
                    \App\Models\Specialization::find($profile->getOriginal('specialization_id'))?->updateMentorCount();
                }
                $profile->specializationModel?->updateMentorCount();
            }
        } else {
            $profile = MentorProfile::create([
                'user_id' => $user->id,
                ...$validated,
            ]);
            $profile->specializationModel?->updateMentorCount();
        }

        // 📸 LOG DE SÉCURITÉ : On trace l'état des photos après la mise à jour
        \Log::info('🛡️ Photo safety check - After updateProfile', [
            'user_id' => $user->id,
            'photo_path' => $user->profile_photo_path,
            'photo_url' => $user->profile_photo_url,
        ]);

        return back()->with('success', $message);
    }

    /**
     * Publier le profil mentor
     */
    public function publishProfile()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            $profile = MentorProfile::create(['user_id' => $user->id]);
        }

        $profile->update(['is_published' => true]);

        return back()->with('success', 'Votre profil est maintenant visible par les jeunes !');
    }

    /**
     * Page du parcours (roadmap)
     */
    public function roadmap()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;
        $steps = $profile ? $profile->roadmapSteps()->orderBy('position')->get() : collect();

        return view('mentor.roadmap', [
            'user' => $user,
            'profile' => $profile,
            'steps' => $steps,
        ]);
    }

    /**
     * Page des statistiques
     */
    public function stats()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        $stats = [
            'profile_views' => $profile ? $profile->profile_views : 0,
        ];

        return view('mentor.stats', [
            'user' => $user,
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    /**
     * Recuperer une etape du roadmap
     */
    public function getStep($step)
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $roadmapStep = $profile->roadmapSteps()->findOrFail($step);

        return response()->json($roadmapStep);
    }

    /**
     * Ajouter une etape au roadmap
     */
    public function storeStep(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'year_start' => 'nullable|integer|min:1950|max:2030',
            'year_end' => 'nullable|integer|min:1950|max:2030',
            'description' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
        ]);

        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            $profile = MentorProfile::create(['user_id' => $user->id]);
        }

        // Determiner la position
        $maxPosition = $profile->roadmapSteps()->max('position') ?? 0;

        $step = $profile->roadmapSteps()->create([
            'step_type' => 'work',
            'title' => $validated['title'],
            'institution_company' => $validated['organization'] ?? null,
            'start_date' => ! empty($validated['year_start']) ? $validated['year_start'].'-01-01' : null,
            'end_date' => ! empty($validated['year_end']) ? $validated['year_end'].'-12-31' : null,
            'description' => $validated['description'] ?? null,
            'position' => $maxPosition + 1,
        ]);

        return response()->json($step, 201);
    }

    /**
     * Mettre a jour une etape
     */
    public function updateStep(Request $request, $step)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'year_start' => 'nullable|integer|min:1950|max:2030',
            'year_end' => 'nullable|integer|min:1950|max:2030',
            'description' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
        ]);

        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $roadmapStep = $profile->roadmapSteps()->findOrFail($step);
        $roadmapStep->update([
            'title' => $validated['title'],
            'institution_company' => $validated['organization'] ?? null,
            'start_date' => ! empty($validated['year_start']) ? $validated['year_start'].'-01-01' : null,
            'end_date' => ! empty($validated['year_end']) ? $validated['year_end'].'-12-31' : null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($roadmapStep);
    }

    /**
     * Supprimer une etape
     */
    public function deleteStep($stepId)
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $step = $profile->roadmapSteps()->findOrFail($stepId);
        $step->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Importer les données LinkedIn depuis un PDF
     */
    public function importLinkedInData(Request $request, \App\Services\LinkedInPdfParserService $parserService)
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        // 📸 LOG DE SÉCURITÉ : On trace l'état des photos avant l'import
        \Log::info('🛡️ Photo safety check - Before LinkedIn Import', [
            'user_id' => $user->id,
            'photo_path' => $user->profile_photo_path,
            'photo_url' => $user->profile_photo_url,
        ]);

        if (! $profile) {
            $profile = MentorProfile::create(['user_id' => $user->id]);
        }

        try {
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:5120', // 5MB max
            ]);

            // Stocker temporairement le PDF
            $pdfPath = $request->file('pdf')->store('temp-linkedin-pdfs', 'local');
            $fullPath = storage_path('app/'.$pdfPath);

            // Parser le PDF
            $profileData = $parserService->parsePdf($fullPath);

            \Log::info('LinkedIn PDF parsed', ['data' => $profileData]);

            // 🔒 SÉCURITÉ : Vérifier que l'email ou le nom correspond
            $isOwner = false;
            $mismatchContext = [];

            // 1. Vérification par Email
            if (! empty($profileData['contact']['email'])) {
                $pdfEmail = strtolower(trim($profileData['contact']['email']));
                $userEmail = strtolower(trim($user->email));

                if ($pdfEmail === $userEmail) {
                    $isOwner = true;
                    \Log::info('✅ Email validation passed', ['email' => $userEmail]);
                } else {
                    $mismatchContext['email'] = ['pdf' => $pdfEmail, 'user' => $userEmail];
                }
            }

            // 2. Vérification par Nom (si email ne correspond pas ou est absent)
            if (! $isOwner && ! empty($profileData['name'])) {
                $pdfName = strtolower(trim($profileData['name']));
                $userName = strtolower(trim($user->name));

                // On vérifie si le nom complet correspond ou si l'un contient l'autre (match partiel pour plus de souplesse)
                if ($pdfName === $userName || str_contains($pdfName, $userName) || str_contains($userName, $pdfName)) {
                    $isOwner = true;
                    \Log::info('✅ Name validation passed', ['name' => $userName, 'pdf_name' => $pdfName]);
                } else {
                    $mismatchContext['name'] = ['pdf' => $pdfName, 'user' => $userName];
                }
            }

            // Si aucune vérification n'a fonctionné
            if (! $isOwner) {
                // Supprimer le fichier temporaire
                Storage::disk('local')->delete($pdfPath);

                \Log::warning('LinkedIn import ownership mismatch', [
                    'user_id' => $user->id,
                    'context' => $mismatchContext,
                ]);

                $errorMessage = 'Ce profil LinkedIn ne semble pas vous appartenir.';
                if (isset($mismatchContext['name'])) {
                    $errorMessage .= ' Le nom dans le PDF ('.$profileData['name'].') ne correspond pas à votre nom ('.$user->name.').';
                }

                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 422);
            }

            // Stocker le PDF définitivement
            $finalPdfPath = $request->file('pdf')->store('linkedin-pdfs', 'local');
            $originalName = $request->file('pdf')->getClientOriginalName();

            // Supprimer le fichier temporaire
            \Storage::disk('local')->delete($pdfPath);

            // Supprimer anciennes expériences si réimport
            if ($profile->linkedin_import_count > 0) {
                $profile->roadmapSteps()->delete();
            }

            // Calculer les années d'expérience
            $yearsOfExperience = $this->calculateYearsOfExperience($profileData['experience']);

            // Récupérer la dernière expérience (la plus récente)
            $latestExperience = ! empty($profileData['experience']) ? $profileData['experience'][0] : null;

            // Sauvegarder les données
            // NOTE: On ne touche JAMAIS aux champs de photo de l'utilisateur ici (profile_photo_path, profile_photo_url)
            // car le PDF LinkedIn ne contient pas de photo. On préserve les données existantes si le PDF est incomplet.
            $profile->update([
                'linkedin_raw_data' => $profileData,
                'linkedin_imported_at' => now(),
                'linkedin_pdf_path' => $finalPdfPath,
                'linkedin_pdf_original_name' => $originalName,
                'linkedin_import_count' => $profile->linkedin_import_count + 1,

                // Poste actuel = titre de la dernière expérience (on préserve si vide)
                'current_position' => ($latestExperience['title'] ?? null) ?: $profile->current_position,

                // Entreprise actuelle = company de la dernière expérience (on préserve si vide)
                'current_company' => ($latestExperience['company'] ?? null) ?: $profile->current_company,

                // Bio = headline (on préserve si vide)
                'bio' => ($profileData['headline'] ?? null) ?: $profile->bio,

                'skills' => (! empty($profileData['skills'])) ? $profileData['skills'] : $profile->skills,

                // Nouveaux mappings - formatage robuste des URLs
                'linkedin_url' => $this->formatUrl(($profileData['contact']['linkedin'] ?? null) ?: $profile->linkedin_url),
                'website_url' => $this->formatUrl(($profileData['contact']['website'] ?? null) ?: $profile->website_url),
                'years_of_experience' => $yearsOfExperience > 0 ? $yearsOfExperience : $profile->years_of_experience,
            ]);

            // Importer les expériences comme étapes
            $stepPosition = 0;

            if (! empty($profileData['experience'])) {
                foreach ($profileData['experience'] as $exp) {
                    $startDate = null;
                    $endDate = null;

                    // 1. Utiliser les dates fournies par le parser si valides
                    if (! empty($exp['start_date'])) {
                        // S'assurer que le format est YYYY-MM-DD
                        $startDate = strlen($exp['start_date']) === 4 ? $exp['start_date'].'-01-01' : $exp['start_date'];
                    }

                    if (array_key_exists('end_date', $exp)) {
                        if (! empty($exp['end_date'])) {
                            // S'assurer que le format est YYYY-MM-DD
                            $endDate = strlen($exp['end_date']) === 4 ? $exp['end_date'].'-12-31' : $exp['end_date'];
                        } else {
                            // end_date est explictement vide ou null (Present)
                            $endDate = null;
                        }
                    } else {
                        // Pas de end_date dans le JSON, on essaie de calculer avec la durée comme fallback de dernier recours
                        $currentYear = date('Y');
                        $durationYears = $exp['duration_years'] ?? 0;

                        if ($durationYears > 0) {
                            $endDate = $currentYear.'-12-31';
                            $startDate = ($currentYear - $durationYears).'-01-01';
                        }
                    }

                    $profile->roadmapSteps()->create([
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

            // Importer les formations comme étapes
            if (! empty($profileData['education'])) {
                foreach ($profileData['education'] as $edu) {
                    $profile->roadmapSteps()->create([
                        'step_type' => 'education',
                        'title' => $edu['degree'] ?? 'Formation',
                        'institution_company' => $edu['school'] ?? null,
                        'description' => 'Formation académique',
                        'start_date' => ! empty($edu['year_start']) ? $edu['year_start'].'-01-01' : null,
                        'end_date' => ! empty($edu['year_end']) ? $edu['year_end'].'-12-31' : null,
                        'position' => $stepPosition++,
                    ]);
                }
            }

            // Détecter les données manquantes et générer des suggestions
            $warnings = [];
            $missingFields = [];
            $suggestions = [];

            if (empty($profileData['summary'])) {
                $missingFields[] = 'bio';
                $warnings[] = 'Aucun résumé trouvé dans le PDF';
                $suggestions[] = 'Ajoutez une bio sur la page "Mon profil"';
            }

            if (empty($profileData['skills']) || count($profileData['skills']) === 0) {
                $missingFields[] = 'compétences';
                $warnings[] = 'Aucune compétence trouvée dans le PDF';
                $suggestions[] = 'Ajoutez vos compétences sur la page "Mon profil"';
            }

            if (empty($profileData['contact']['website'])) {
                $missingFields[] = 'site web';
                $suggestions[] = 'Ajoutez votre site web sur la page "Mon profil"';
            }

            if ($yearsOfExperience === 0) {
                $warnings[] = 'Impossible de calculer les années d\'expérience';
                $suggestions[] = 'Vérifiez vos années d\'expérience sur la page "Mon profil"';
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil LinkedIn importé avec succès !',
                'data' => [
                    'name' => $profileData['name'] ?? '',
                    'headline' => $profileData['headline'] ?? '',
                    'experience_count' => count($profileData['experience'] ?? []),
                    'skills_count' => count($profileData['skills'] ?? []),
                    'import_count' => $profile->linkedin_import_count,
                    'years_of_experience' => $yearsOfExperience,
                ],
                'warnings' => $warnings,
                'missing_fields' => $missingFields,
                'suggestions' => ! empty($suggestions) ? [
                    'message' => 'Certaines données sont manquantes. Complétez votre profil :',
                    'actions' => $suggestions,
                ] : null,
            ]);

            // 📸 LOG DE SÉCURITÉ : On trace l'état des photos après l'import
            \Log::info('🛡️ Photo safety check - After LinkedIn Import (Success)', [
                'user_id' => $user->id,
                'photo_path' => $user->profile_photo_path,
                'photo_url' => $user->profile_photo_url,
            ]);

            return $response;

        } catch (\Throwable $e) {
            \Log::error('LinkedIn PDF import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // 📸 LOG DE SÉCURITÉ : On trace même en cas d'erreur
            \Log::warning('🛡️ Photo safety check - After LinkedIn Import (Failed)', [
                'user_id' => $user->id,
                'photo_path' => $user->profile_photo_path,
                'photo_url' => $user->profile_photo_url,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur critique lors du parsing : '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine(),
            ], 500);
        }
    }

    /**
     * Formater une URL pour s'assurer qu'elle commence par http(s)://
     */
    private function formatUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }

        $url = trim($url);

        // Si l'URL ne commence pas par http:// ou https://
        if (! preg_match('/^https?:\/\//i', $url)) {
            // Si elle commence par www., ou juste par un nom de domaine
            return 'https://'.ltrim($url, '/');
        }

        return $url;
    }

    /**
     * Calculer les années d'expérience totales depuis les périodes (fusion d'intervalles)
     */
    private function calculateYearsOfExperience($experiences)
    {
        if (empty($experiences)) {
            return 0;
        }

        $intervals = [];

        foreach ($experiences as $exp) {
            $startStr = $exp['start_date'] ?? null;
            $endStr = $exp['end_date'] ?? null;

            if (! $startStr) {
                continue;
            }

            try {
                $start = \Carbon\Carbon::parse($startStr)->startOfDay();
                $end = $endStr ? \Carbon\Carbon::parse($endStr)->endOfDay() : now()->endOfDay();

                if ($end->isBefore($start)) {
                    continue;
                }

                $intervals[] = ['start' => $start, 'end' => $end];
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($intervals)) {
            return 0;
        }

        // Tri par date de début
        usort($intervals, function ($a, $b) {
            return $a['start']->timestamp <=> $b['start']->timestamp;
        });

        // Fusion des intervalles
        $merged = [];
        $current = $intervals[0];

        for ($i = 1; $i < count($intervals); $i++) {
            if ($intervals[$i]['start']->isBefore($current['end']) || $intervals[$i]['start']->equalTo($current['end'])) {
                // Chevauchement ou contiguïté, on étend la fin
                if ($intervals[$i]['end']->isAfter($current['end'])) {
                    $current['end'] = $intervals[$i]['end'];
                }
            } else {
                // Pas de chevauchement, on enregistre l'intervalle actuel et on passe au suivant
                $merged[] = $current;
                $current = $intervals[$i];
            }
        }
        $merged[] = $current;

        // Calcul de la durée totale en jours
        $totalDays = 0;
        foreach ($merged as $interval) {
            $totalDays += $interval['start']->diffInDays($interval['end']);
        }

        // Conversion en années (moyenne de 365.25 jours par an)
        $years = $totalDays / 365.25;

        // On arrondit au supérieur si on est proche (ex: 4.8 -> 5) ou à l'entier
        return (int) round($years);
    }
}
