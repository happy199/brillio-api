<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
}
