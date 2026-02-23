<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // Rate limiting - 3 tentatives par heure
        $key = 'newsletter-subscribe:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return redirect()->to(url()->previous().'#newsletter')
                ->with('error', 'Trop de tentatives. Réessaye dans 1 heure.');
        }

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Vérifier si déjà inscrit
        $existing = NewsletterSubscriber::where('email', $request->email)->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return redirect()->to(url()->previous().'#newsletter')
                    ->with('info', 'Tu es déjà inscrit à notre newsletter !');
            } else {
                // Réactiver l'abonnement
                $existing->update([
                    'status' => 'active',
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);

                return redirect()->to(url()->previous().'#newsletter')
                    ->with('success', 'Ton abonnement a été réactivé !');
            }
        }

        // Créer nouvel abonné
        NewsletterSubscriber::create([
            'email' => $request->email,
            'status' => 'active',
            'ip_address' => $request->ip(),
        ]);

        RateLimiter::hit($key, 3600); // 1 heure

        return redirect()->to(url()->previous().'#newsletter')
            ->with('success', 'Merci ! Tu es maintenant inscrit à notre newsletter.');
    }

    public function unsubscribe($token)
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();

        if ($subscriber->status === 'unsubscribed') {
            return view('public.newsletter-unsubscribed', ['already' => true]);
        }

        $subscriber->unsubscribe();

        return view('public.newsletter-unsubscribed', ['already' => false]);
    }
}
