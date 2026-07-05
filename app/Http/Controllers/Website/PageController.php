<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\JeuneProfile;
use App\Models\MentorProfile;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller pour les pages publiques du site vitrine
 */
class PageController extends Controller
{
    /**
     * Page d'accueil
     */
    public function home()
    {
        // Statistiques dynamiques avec résilience
        try {
            $jeunesCount = User::where('user_type', 'jeune')->count();
            $mentorsCount = User::where('user_type', 'mentor')->count();
            $countriesCount = User::distinct('country')->whereNotNull('country')->count('country');
        } catch (\Exception $e) {
            // Fallback en cas d'erreur DB pour ne pas casser la home
            Log::error('Erreur récupération stats home: '.$e->getMessage());
            $jeunesCount = 10000;
            $mentorsCount = 500;
            $countriesCount = 15;
        }

        $verifiedMentors = MentorProfile::with(['user', 'specializationModel', 'roadmapSteps'])
            ->where('is_validated', true)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        return view('public.home', compact('jeunesCount', 'mentorsCount', 'countriesCount', 'verifiedMentors'));
    }

    /**
     * Page À propos
     */
    public function about()
    {
        $partners = Organization::active()
            ->whereNotNull('logo_url')
            ->where('private_circle_plus_enabled', false)
            ->get();

        return view('public.about', compact('partners'));
    }

    /**
     * Page Contact
     */
    public function contact()
    {
        return view('public.contact');
    }

    /**
     * Page Ressources
     */
    public function resources()
    {
        $resources = \App\Models\Resource::where('is_published', true)
            ->where('is_validated', true)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('public.resources', compact('resources'));
    }

    /**
     * Traitement du formulaire de contact
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // TODO: Envoyer l'email ou stocker le message
        // Mail::to('contact@brillio.africa')->send(new ContactFormMail($validated));

        return back()->with('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
    }

    /**
     * Page Politique de confidentialité
     */
    public function privacy()
    {
        return view('public.privacy');
    }

    /**
     * Page Conditions d'utilisation
     */
    public function terms()
    {
        return view('public.terms');
    }

    /**
     * Profil public d'un mentor (partageable sur réseaux sociaux)
     */
    public function mentorProfile(MentorProfile $mentor)
    {
        // Vérifier que le profil est publié
        if (! $mentor->is_published) {
            abort(404);
        }

        // --- PRIVATE CIRCLE PLUS ISOLATION ---
        $isIsolated = $mentor->user->organizations()->where('private_circle_plus_enabled', true)->exists();
        if ($isIsolated) {
            $canSee = false;
            if (auth()->check()) {
                $authUser = auth()->user();
                // Check if they share at least one organization
                $mentorOrgIds = $mentor->user->organizations()->pluck('organizations.id')->toArray();
                $canSee = $authUser->organizations()->whereIn('organizations.id', $mentorOrgIds)->exists();
            }
            if (! $canSee) {
                abort(404);
            }
        }

        // Incrémenter le compteur de vues
        $mentor->increment('profile_views');

        $mentor->load(['user', 'user.personalityTest', 'specializationModel', 'roadmapSteps']);

        // Données sécurisées pour affichage public
        $publicData = [
            'name' => $mentor->user->name,
            'picture' => $mentor->linkedin_profile_data['picture'] ?? null,
            'current_position' => $mentor->current_position,
            'current_company' => $mentor->current_company,
            'years_of_experience' => $mentor->years_of_experience,
            'specialization' => $mentor->specializationModel?->name ?? $mentor->specialization,
            'bio' => $mentor->bio,
            'advice' => $mentor->advice,
            'linkedin_url' => $mentor->linkedin_url,
            'website_url' => $mentor->website_url,
            'roadmap' => $mentor->roadmapSteps->map(function ($step) {
                return [
                    'title' => $step->title,
                    'institution_company' => $step->institution_company,
                    'start_date' => $step->start_date,
                    'end_date' => $step->end_date,
                    'description' => $step->description,
                    'step_type' => $step->step_type,
                ];
            }),
            'personality' => $mentor->user->personalityTest ? [
                'type' => $mentor->user->personalityTest->personality_type,
                'label' => $mentor->user->personalityTest->personality_label ?? $mentor->user->personalityTest->personality_type,
                'description' => $mentor->user->personalityTest->personality_description,
            ] : null,
        ];

        $resources = \App\Models\Resource::where('user_id', $mentor->user_id)
            ->where('is_published', true)
            ->where('is_validated', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('public.mentor-profile', [
            'mentor' => $mentor,
            'publicData' => $publicData,
            'resources' => $resources,
        ]);
    }

    /**
     * Profil public d'un jeune (partageable)
     */
    public function jeuneProfile($slug)
    {
        $profile = JeuneProfile::where('public_slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        // --- PRIVATE CIRCLE PLUS ISOLATION ---
        $isIsolated = $profile->user->organizations()->where('private_circle_plus_enabled', true)->exists();
        if ($isIsolated) {
            $canSee = false;
            if (auth()->check()) {
                $authUser = auth()->user();
                // Check if they share at least one organization
                $jeuneOrgIds = $profile->user->organizations()->pluck('organizations.id')->toArray();
                $canSee = $authUser->organizations()->whereIn('organizations.id', $jeuneOrgIds)->exists();
            }
            if (! $canSee) {
                abort(404);
            }
        }

        // Incrémenter le compteur de vues
        $profile->increment('profile_views');

        // Si c'est un mentor connecté qui visite, incrémenter les vues par mentor
        if (auth()->check() && auth()->user()->isMentor()) {
            $profile->increment('mentor_views');
        }

        $profile->load(['user', 'user.personalityTest']);

        return view('public.jeune-profile', [
            'profile' => $profile,
            'user' => $profile->user,
        ]);
    }

    /**
     * Page de publicité publique
     */
    public function advertisements()
    {
        $advertisements = Advertisement::where('status', Advertisement::STATUS_APPROVED)
            ->inRandomOrder()
            ->get();

        return view('public.advertisements', compact('advertisements'));
    }

    /**
     * Increment the click counter for an advertisement
     */
    public function trackAdvertisementClick(Advertisement $advertisement)
    {
        $advertisement->increment('clicks');

        return response()->json(['success' => true, 'clicks' => $advertisement->clicks]);
    }
}
