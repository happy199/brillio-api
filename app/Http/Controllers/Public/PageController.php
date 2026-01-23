<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use Illuminate\Http\Request;

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
        // Statistiques dynamiques
        $jeunesCount = \App\Models\User::where('user_type', 'jeune')->count();
        $mentorsCount = \App\Models\User::where('user_type', 'mentor')->count();
        $countriesCount = \App\Models\User::distinct('country')->whereNotNull('country')->count('country');

        return view('public.home', compact('jeunesCount', 'mentorsCount', 'countriesCount'));
    }

    /**
     * Page À propos
     */
    public function about()
    {
        return view('public.about');
    }

    /**
     * Page Contact
     */
    public function contact()
    {
        return view('public.contact');
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
        if (!$mentor->is_published) {
            abort(404);
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

        return view('public.mentor-profile', [
            'mentor' => $mentor,
            'publicData' => $publicData,
        ]);
    }
    /**
     * Profil public d'un jeune (partageable)
     */
    public function jeuneProfile($slug)
    {
        $profile = \App\Models\JeuneProfile::where('public_slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

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
}
