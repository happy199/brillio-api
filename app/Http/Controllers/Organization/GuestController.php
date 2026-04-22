<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\RoadmapStep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    /**
     * Liste des formateurs invités de l'organisation
     */
    public function index(Request $request)
    {
        $organization = auth()->user()->organization;
        
        $query = User::where('is_guest', true)
            ->whereHas('organizations', function ($q) use ($organization) {
                $q->where('organizations.id', $organization->id);
            })
            ->with('mentorProfile');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $mentors = $query->latest()->paginate(15);
        $type = 'guest';

        return view('organization.mentors.index', compact('mentors', 'organization', 'type'));
    }

    /**
     * Formulaire de création d'un invité
     */
    public function create()
    {
        $specializations = \App\Models\Specialization::active()->orderBy('name')->get();
        $countries = User::getCountries();
        return view('organization.guests.create', compact('specializations', 'countries'));
    }

    /**
     * Enregistrer un nouvel invité
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'specialization_id' => 'nullable|exists:specializations,id',
            'custom_specialization' => 'nullable|string|max:100',
            'years_of_experience' => 'nullable|integer|min:0|max:60',
            'photo' => 'nullable|image|max:2048',
            'website_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string|max:2000',
            'academic_steps' => 'nullable|array',
            'academic_steps.*.title' => 'required|string|max:255',
            'academic_steps.*.institution' => 'required|string|max:255',
            'academic_steps.*.year' => 'required|integer|min:1950|max:'.date('Y'),
        ]);

        DB::beginTransaction();
        try {
            // Gestion de la spécialisation à la volée
            $specId = $validated['specialization_id'];
            if (empty($specId) && !empty($validated['custom_specialization'])) {
                $newSpec = \App\Models\Specialization::firstOrCreate(
                    ['name' => $validated['custom_specialization']],
                    ['status' => 'active', 'created_by_admin' => false]
                );
                $specId = $newSpec->id;
            }

            // Gestion de la photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('profile-photos', 'public');
            }

            // Créer l'utilisateur invité
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
                'profile_photo_path' => $photoPath,
                'password' => bcrypt(Str::random(16)),
                'user_type' => User::TYPE_MENTOR,
                'is_guest' => true,
                'email_verified_at' => now(),
            ]);

            // Lier à l'organisation
            $user->organizations()->attach(auth()->user()->organization_id, ['role' => 'mentor']);

            // Créer le profil mentor
            $profile = MentorProfile::create([
                'user_id' => $user->id,
                'bio' => $validated['bio'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'specialization_id' => $specId,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'is_published' => false,
                'is_validated' => true,
                'validated_at' => now(),
                'public_slug' => Str::slug($user->name) . '-' . Str::random(6),
            ]);

            // Ajouter les étapes de parcours si présentes
            if (!empty($validated['academic_steps'])) {
                // Trier par année pour être sûr de prendre la plus récente comme "actuelle"
                $steps = collect($validated['academic_steps'])->sortByDesc('year');
                $mostRecentStep = $steps->first();

                foreach ($validated['academic_steps'] as $index => $step) {
                    RoadmapStep::create([
                        'mentor_profile_id' => $profile->id,
                        'step_type' => 'education',
                        'title' => $step['title'],
                        'institution_company' => $step['institution'],
                        'start_date' => $step['year'] . '-01-01',
                        'position' => $index,
                    ]);
                }

                // Mettre à jour l'entreprise et le poste actuel depuis l'étape la plus récente
                if ($mostRecentStep) {
                    $profile->current_company = $mostRecentStep['institution'];
                    $profile->current_position = $mostRecentStep['title'];
                    $profile->save();
                    
                    \Illuminate\Support\Facades\Log::info("Sync Guest Profile (Store): {$user->name} created with Company: {$profile->current_company}");
                }
            }

            DB::commit();

            return redirect()->route('organization.guests.index')
                ->with('success', "Le formateur invité {$user->name} a été créé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', "Une erreur est survenue lors de la création de l'invité : " . $e->getMessage());
        }
    }

    /**
     * Modifier un invité
     */
    public function edit(User $guest)
    {
        if (!$guest->organizations->contains(auth()->user()->organization_id)) {
            abort(403);
        }

        $guest->load('mentorProfile.roadmapSteps');
        $specializations = \App\Models\Specialization::active()->orderBy('name')->get();
        $countries = User::getCountries();
        
        return view('organization.guests.edit', compact('guest', 'specializations', 'countries'));
    }

    /**
     * Mettre à jour un invité
     */
    public function update(Request $request, User $guest)
    {
        if (!$guest->organizations->contains(auth()->user()->organization_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$guest->id,
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'specialization_id' => 'nullable|exists:specializations,id',
            'custom_specialization' => 'nullable|string|max:100',
            'years_of_experience' => 'nullable|integer|min:0|max:60',
            'photo' => 'nullable|image|max:2048',
            'website_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string|max:2000',
            'academic_steps' => 'nullable|array',
            'academic_steps.*.title' => 'required|string|max:255',
            'academic_steps.*.institution' => 'required|string|max:255',
            'academic_steps.*.year' => 'required|integer|min:1950|max:'.date('Y'),
        ]);

        DB::beginTransaction();
        try {
            // Gestion de la spécialisation à la volée
            $specId = $validated['specialization_id'];
            if (empty($specId) && !empty($validated['custom_specialization'])) {
                $newSpec = \App\Models\Specialization::firstOrCreate(
                    ['name' => $validated['custom_specialization']],
                    ['status' => 'active', 'created_by_admin' => false]
                );
                $specId = $newSpec->id;
            }

            // Gestion de la photo
            if ($request->hasFile('photo')) {
                if ($guest->profile_photo_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($guest->profile_photo_path);
                }
                $photoPath = $request->file('photo')->store('profile-photos', 'public');
                $guest->profile_photo_path = $photoPath;
            }

            // Mise à jour de l'utilisateur
            $guest->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
            ]);

            // Mise à jour du profil mentor
            $profile = $guest->mentorProfile;
            $profile->update([
                'bio' => $validated['bio'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'specialization_id' => $specId,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
            ]);

            // Mise à jour des étapes
            $profile->roadmapSteps()->where('step_type', 'education')->delete();
            if (!empty($validated['academic_steps'])) {
                // Trier par année pour être sûr de prendre la plus récente comme "actuelle"
                $steps = collect($validated['academic_steps'])->sortByDesc('year');
                $mostRecentStep = $steps->first();

                foreach ($validated['academic_steps'] as $index => $step) {
                    RoadmapStep::create([
                        'mentor_profile_id' => $profile->id,
                        'step_type' => 'education',
                        'title' => $step['title'],
                        'institution_company' => $step['institution'],
                        'start_date' => $step['year'] . '-01-01',
                        'position' => $index,
                    ]);
                }
                
                // Mettre à jour l'entreprise et le poste actuel depuis l'étape la plus récente
                if ($mostRecentStep) {
                    $profile->current_company = $mostRecentStep['institution'];
                    $profile->current_position = $mostRecentStep['title'];
                    $profile->save();
                    
                    \Illuminate\Support\Facades\Log::info("Sync Guest Profile: {$guest->name} updated with Company: {$profile->current_company}");
                }
            }

            DB::commit();

            return redirect()->route('organization.guests.index')
                ->with('success', "Le profil de {$guest->name} a été mis à jour.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', "Erreur lors de la mise à jour : " . $e->getMessage());
        }
    }

    /**
     * Supprimer un invité
     */
    public function destroy(User $guest)
    {
        if (!$guest->organizations->contains(auth()->user()->organization_id)) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Supprimer la photo
            if ($guest->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($guest->profile_photo_path);
            }

            // Supprimer l'utilisateur et ses relations (le cascade s'occupe du profil mentor et roadmap)
            $guest->delete();

            DB::commit();

            return redirect()->route('organization.guests.index')
                ->with('success', "L'invité a été supprimé avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Erreur lors de la suppression : " . $e->getMessage());
        }
    }
}
