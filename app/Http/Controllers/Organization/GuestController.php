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
        return view('organization.guests.create');
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
            'website_url' => 'nullable|url|max:255',
            'bio' => 'nullable|string|max:2000',
            'academic_steps' => 'nullable|array',
            'academic_steps.*.title' => 'required|string|max:255',
            'academic_steps.*.institution' => 'required|string|max:255',
            'academic_steps.*.year' => 'required|integer|min:1950|max:'.date('Y'),
        ]);

        DB::beginTransaction();
        try {
            // Créer l'utilisateur invité
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt(Str::random(16)), // Mot de passe aléatoire, non utilisé
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
                'is_published' => false, // Défini par l'admin plus tard si besoin
                'is_validated' => true,  // Validé d'office car créé par une organisation Enterprise
                'validated_at' => now(),
                'public_slug' => Str::slug($user->name) . '-' . Str::random(6),
            ]);

            // Ajouter les étapes de parcours si présentes
            if (!empty($validated['academic_steps'])) {
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
        // Sécurité: vérifier que l'invité appartient à l'organisation
        if (!$guest->organizations->contains(auth()->user()->organization_id)) {
            abort(403);
        }

        $guest->load('mentorProfile.roadmapSteps');
        return view('organization.guests.edit', compact('guest'));
    }

    // Autres méthodes CRUD (update, destroy) à implémenter selon besoin...
}
