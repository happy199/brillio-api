<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Affiche la notice de vérification
     */
    public function notice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route($request->user()->isJeune() ? 'jeune.dashboard' : 'home')
            : view('auth.verify-email');
    }

    /**
     * Traite le lien de vérification
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->isJeune() ? 'jeune.dashboard' : 'home');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route($request->user()->isJeune() ? 'jeune.dashboard' : 'home')
            ->with('success', 'Votre adresse e-mail a été vérifiée avec succès !');
    }

    /**
     * Renvoie l'e-mail de vérification
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->isJeune() ? 'jeune.dashboard' : 'home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.');
    }
}
