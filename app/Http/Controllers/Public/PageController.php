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
        return view('public.home');
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

        $mentor->load(['user', 'specialization', 'roadmapSteps']);

        // Données sécurisées pour affichage public
        $publicData = [
            'name' => $mentor->user->name,
            'picture' => $mentor->linkedin_profile_data['picture'] ?? null,
            'current_position' => $mentor->current_position,
            'current_company' => $mentor->current_company,
            'years_of_experience' => $mentor->years_of_experience,
            'specialization' => $mentor->specialization?->name,
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
        ];

        return view('public.mentor-profile', [
            'mentor' => $mentor,
            'publicData' => $publicData,
        ]);
    }
}
