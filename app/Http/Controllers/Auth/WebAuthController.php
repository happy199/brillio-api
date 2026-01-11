<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MentorProfile;
use App\Services\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Controller pour l'authentification web (jeunes et mentors)
 */
class WebAuthController extends Controller
{
    public function __construct(
        private SupabaseAuthService $supabase
    ) {}

    /**
     * Affiche la page de choix du type de compte
     */
    public function showChoice()
    {
        return view('auth.choice');
    }

    /**
     * Affiche le formulaire d'inscription jeune
     */
    public function showJeuneRegister()
    {
        return view('auth.jeune.register');
    }

    /**
     * Affiche le formulaire de connexion jeune
     */
    public function showJeuneLogin()
    {
        return view('auth.jeune.login');
    }

    /**
     * Inscription jeune par email
     */
    public function jeuneRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.unique' => 'Cette adresse email est deja utilisee.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'jeune',
            'auth_provider' => 'email',
        ]);

        Auth::login($user);

        // Rediriger vers l'onboarding si pas encore complete
        if (!$user->onboarding_completed) {
            return redirect()->route('jeune.onboarding');
        }

        return redirect()->route('jeune.dashboard');
    }

    /**
     * Connexion jeune par email
     */
    public function jeuneLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Verifier que c'est un compte jeune
        $user = User::where('email', $credentials['email'])
            ->where('user_type', 'jeune')
            ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Aucun compte jeune trouve avec cette adresse email.',
            ])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user->update(['last_login_at' => now()]);

            if (!$user->onboarding_completed) {
                return redirect()->route('jeune.onboarding');
            }

            return redirect()->intended(route('jeune.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis sont incorrects.',
        ])->withInput();
    }

    /**
     * Redirection OAuth pour les jeunes (Google/Facebook)
     */
    public function jeuneOAuthRedirect(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            abort(400, 'Provider non supporte');
        }

        $redirectUrl = route('auth.jeune.oauth.callback', ['provider' => $provider]);

        // Stocker le provider en session
        session(['oauth_provider' => $provider, 'oauth_type' => 'jeune']);

        $oauthUrl = $this->supabase->getOAuthUrl($provider, $redirectUrl);

        return redirect($oauthUrl);
    }

    /**
     * Callback OAuth pour les jeunes
     */
    public function jeuneOAuthCallback(Request $request, string $provider)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('auth.jeune.login')
                ->with('error', 'Erreur lors de l\'authentification. Veuillez reessayer.');
        }

        // Echanger le code contre un token
        $session = $this->supabase->exchangeCodeForSession($code);

        if (!$session) {
            return redirect()->route('auth.jeune.login')
                ->with('error', 'Erreur lors de la connexion. Veuillez reessayer.');
        }

        // Recuperer les infos utilisateur
        $userData = $this->supabase->getUser($session['access_token']);

        if (!$userData) {
            return redirect()->route('auth.jeune.login')
                ->with('error', 'Impossible de recuperer vos informations.');
        }

        $socialData = $this->supabase->extractSocialData($userData);

        // Chercher ou creer l'utilisateur
        $user = User::where('email', $socialData['email'])->first();

        if ($user) {
            // Verifier que c'est bien un compte jeune
            if ($user->user_type !== 'jeune') {
                return redirect()->route('auth.jeune.login')
                    ->with('error', 'Cette adresse email est associee a un compte mentor.');
            }

            // Mettre a jour les infos
            $user->update([
                'auth_provider' => $provider,
                'provider_id' => $userData['id'],
                'profile_photo_url' => $socialData['avatar_url'] ?? $user->profile_photo_url,
                'last_login_at' => now(),
            ]);
        } else {
            // Creer un nouveau compte jeune
            $user = User::create([
                'name' => $socialData['name'] ?? 'Utilisateur',
                'email' => $socialData['email'],
                'password' => Hash::make(Str::random(32)),
                'user_type' => 'jeune',
                'auth_provider' => $provider,
                'provider_id' => $userData['id'],
                'profile_photo_url' => $socialData['avatar_url'],
                'email_verified_at' => $socialData['email_verified'] ? now() : null,
            ]);
        }

        Auth::login($user, true);

        if (!$user->onboarding_completed) {
            return redirect()->route('jeune.onboarding');
        }

        return redirect()->route('jeune.dashboard');
    }

    /**
     * Affiche la page de connexion mentor (LinkedIn uniquement)
     */
    public function showMentorLogin()
    {
        return view('auth.mentor.login');
    }

    /**
     * Redirection OAuth LinkedIn pour les mentors
     */
    public function mentorLinkedInRedirect()
    {
        $redirectUrl = route('auth.mentor.linkedin.callback');

        session(['oauth_type' => 'mentor']);

        // LinkedIn OIDC avec scopes specifiques
        $scopes = ['openid', 'profile', 'email'];
        $oauthUrl = $this->supabase->getOAuthUrl('linkedin_oidc', $redirectUrl, $scopes);

        return redirect($oauthUrl);
    }

    /**
     * Callback LinkedIn pour les mentors
     */
    public function mentorLinkedInCallback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('auth.mentor.login')
                ->with('error', 'Erreur lors de l\'authentification LinkedIn.');
        }

        $session = $this->supabase->exchangeCodeForSession($code);

        if (!$session) {
            return redirect()->route('auth.mentor.login')
                ->with('error', 'Erreur lors de la connexion LinkedIn.');
        }

        $userData = $this->supabase->getUser($session['access_token']);

        if (!$userData) {
            return redirect()->route('auth.mentor.login')
                ->with('error', 'Impossible de recuperer vos informations LinkedIn.');
        }

        $linkedinData = $this->supabase->extractLinkedInData($userData);

        // Chercher ou creer l'utilisateur mentor
        $user = User::where('email', $linkedinData['email'])->first();

        if ($user) {
            // Verifier que c'est bien un compte mentor
            if ($user->user_type !== 'mentor') {
                return redirect()->route('auth.mentor.login')
                    ->with('error', 'Cette adresse email est associee a un compte jeune. Veuillez utiliser la connexion jeune.');
            }

            $user->update([
                'auth_provider' => 'linkedin',
                'provider_id' => $linkedinData['linkedin_id'],
                'profile_photo_url' => $linkedinData['avatar_url'] ?? $user->profile_photo_url,
                'last_login_at' => now(),
            ]);

            // Mettre a jour le profil mentor avec les donnees LinkedIn
            if ($user->mentorProfile) {
                $user->mentorProfile->update([
                    'linkedin_profile_data' => $linkedinData['raw_data'],
                ]);
            }
        } else {
            // Creer un nouveau compte mentor
            $user = User::create([
                'name' => $linkedinData['name'] ?? 'Mentor',
                'email' => $linkedinData['email'],
                'password' => Hash::make(Str::random(32)),
                'user_type' => 'mentor',
                'auth_provider' => 'linkedin',
                'provider_id' => $linkedinData['linkedin_id'],
                'profile_photo_url' => $linkedinData['avatar_url'],
                'email_verified_at' => now(),
            ]);

            // Creer le profil mentor avec les donnees LinkedIn
            MentorProfile::create([
                'user_id' => $user->id,
                'linkedin_profile_data' => $linkedinData['raw_data'],
            ]);
        }

        Auth::login($user, true);

        // Rediriger vers l'espace mentor
        return redirect()->route('mentor.dashboard');
    }

    /**
     * Deconnexion
     */
    public function logout(Request $request)
    {
        $userType = Auth::user()?->user_type;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Vous avez ete deconnecte avec succes.');
    }
}
