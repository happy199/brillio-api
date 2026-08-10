<?php

namespace App\Http\Controllers\Organization\Auth;

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
            ? redirect()->route('organization.dashboard')
            : view('organization.auth.verify-email');
    }

    /**
     * Traite le lien de vérification
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('organization.dashboard');
        }

        if ($request->user()->markEmailAsVerified()) {
            $request->user()->forceFill([
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ])->save();

            event(new Verified($request->user()));
        }

        return redirect()->route('organization.dashboard')
            ->with('success', 'Votre adresse e-mail a été vérifiée avec succès. Bienvenue !');
    }

    /**
     * Traite la vérification manuelle par code à 6 chiffres pour les organisations
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (! $user) {
            return back()->withErrors(['code' => 'Session expirée. Veuillez vous reconnecter.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('organization.dashboard');
        }

        if (! $user->verification_code || $user->verification_code !== trim($request->code) || ($user->verification_code_expires_at && now()->greaterThan($user->verification_code_expires_at))) {
            return back()->withErrors(['code' => 'Le code de vérification saisi est invalide ou a expiré.']);
        }

        if ($user->markEmailAsVerified()) {
            $user->forceFill([
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ])->save();

            event(new Verified($user));
        }

        return redirect()->route('organization.dashboard')
            ->with('success', 'Votre adresse e-mail a été vérifiée avec succès. Bienvenue !');
    }

    /**
     * Renvoie l'e-mail de vérification
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('organization.dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Un nouveau lien de vérification et code OTP ont été envoyés à votre adresse e-mail.');
    }
}
