<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Mail\OrganizationUnlinkedNotificationMail;
use App\Mail\UserUnlinkedConfirmationMail;
use App\Models\MentorProfile;
use App\Models\Specialization;
use App\Services\LinkedInPdfParserService;
use App\Services\MentorLinkedInImportService;
use App\Traits\FormatsUrls;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MentorDashboardController extends Controller
{
    use FormatsUrls;

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
        $specializations = Specialization::active()
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
        // Nettoyer les URLs avant validation (si non vides et sans protocole)
        $request->merge([
            'linkedin_url' => $this->formatUrl($request->linkedin_url),
            'website_url' => $this->formatUrl($request->website_url),
        ]);

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
            $photoValidated = $request->validate(['profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048']);
            $path = $photoValidated['profile_photo']->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
            $user->save();
        }

        // Gérer la spécialisation
        if ($validated['specialization_id'] === 'new' && ! empty($validated['new_specialization_name'])) {
            // Vérifier si une spécialisation avec ce nom existe déjà
            $existingSpec = Specialization::where('name', $validated['new_specialization_name'])
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
                $newSpec = Specialization::create([
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
                    Specialization::find($profile->getOriginal('specialization_id'))?->updateMentorCount();
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
    public function importLinkedInData(Request $request, MentorLinkedInImportService $importService)
    {
        $user = auth()->user();

        // 📸 LOG DE SÉCURITÉ : On trace l'état des photos avant l'import
        Log::info('🛡️ Photo safety check - Before LinkedIn Import', [
            'user_id' => $user->id,
            'photo_path' => $user->profile_photo_path,
            'photo_url' => $user->profile_photo_url,
        ]);

        $validated = $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:5120', // 5MB max
        ]);

        $result = $importService->import($validated['pdf'], $user);

        // 📸 LOG DE SÉCURITÉ : On trace l'état des photos après l'import
        Log::info('🛡️ Photo safety check - After LinkedIn Import', [
            'user_id' => $user->id,
            'photo_path' => $user->profile_photo_path,
            'photo_url' => $user->profile_photo_url,
        ]);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Erreur lors de l\'import',
            ], $result['code'] ?? 422);
        }

        return response()->json($result);
    }

    /**
     * Calculer les années d'expérience totales depuis les périodes (fusion d'intervalles)
     */
    private function calculateYearsOfExperience($experiences)
    {
        return app(MentorLinkedInImportService::class)->calculateYearsOfExperience($experiences ?? []);
    }

    /**
     * Rompre le lien avec l'organisation
     */
    public function unlinkOrganization()
    {
        $user = auth()->user();
        $org = $user->organization ?? $user->organizations()->first();

        if (! $org) {
            return back()->with('error', 'Aucune organisation liée.');
        }

        // Email to Organization
        $orgEmail = $org->email ?? ($org->owner ? $org->owner->email : null);
        if ($orgEmail) {
            Mail::to($orgEmail)->send(new OrganizationUnlinkedNotificationMail($user, 'Mentor'));
        }

        // Email to User
        Mail::to($user->email)->send(new UserUnlinkedConfirmationMail($org));

        // Detach
        $user->organization_id = null;
        $user->save();
        $user->organizations()->detach($org->id);

        return back()->with('success', 'La relation de mentorat avec l\'organisation a été rompue avec succès.');
    }
}
