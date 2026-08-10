<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            $request->user()->forceFill([
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ])->save();

            event(new Verified($request->user()));
        }

        return redirect()->route($request->user()->isJeune() ? 'jeune.dashboard' : 'home')
            ->with('success', 'Votre adresse e-mail a été vérifiée avec succès !');
    }

    /**
     * Traite la vérification manuelle par code à 6 chiffres
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'email' => 'nullable|email',
        ]);

        $user = $request->user() ?? ($request->filled('email') ? User::where('email', $request->email)->first() : null);

        if (! $user) {
            return back()->withErrors(['code' => 'Utilisateur introuvable. Veuillez vous connecter.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->isJeune() ? 'jeune.dashboard' : 'home')
                ->with('success', 'Votre adresse e-mail est déjà vérifiée.');
        }

        $code = trim($request->code);
        $isValid = $user->verification_code && $user->verification_code === $code && (! $user->verification_code_expires_at || now()->lessThanOrEqualTo($user->verification_code_expires_at));

        if ($isValid && $user->markEmailAsVerified()) {
            $user->forceFill([
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ])->save();

            event(new Verified($user));

            return redirect()->route($user->isJeune() ? 'jeune.dashboard' : 'home')
                ->with('success', 'Votre adresse e-mail a été vérifiée avec succès !');
        }

        return back()->withErrors(['code' => 'Le code de vérification saisi est invalide ou a expiré.']);
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

        return back()->with('success', 'Un nouveau lien de vérification et code OTP ont été envoyés à votre adresse e-mail.');
    }
}
