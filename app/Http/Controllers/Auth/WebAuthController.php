<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MentorshipNotificationService;
use App\Services\SupabaseAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MentorProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;

/**
 * Controller pour l'authentification web (jeunes et mentors)
 * Gere OAuth via Supabase (Google, Facebook, LinkedIn)
 */
class WebAuthController extends Controller
{
    public function __construct(
        private SupabaseAuthService $supabase,
        private \App\Services\UserAvatarService $avatarService,
        private MentorshipNotificationService $notificationService
    ) {
    }

    /**
     * Cree ou met a jour un utilisateur mentor
     */
    protected function createOrUpdateMentorUser(array $userData): array
    {
        $linkedinData = $this->supabase->extractLinkedInData($userData);

        if (empty($linkedinData['email'])) {
            return [
                'success' => false,
                'error' => 'Impossible de recuperer votre adresse email LinkedIn.'
            ];
        }

        $user = User::where('email', $linkedinData['email'])->first();

        if ($user) {
            // Vérifier si l'utilisateur est bloqué
            if ($user->is_blocked) {
                return [
                    'success' => false,
                    'error' => 'Votre accès Mentor a été suspendu par l\'administration. Motif : ' . ($user->blocked_reason ?? 'Non spécifié')
                ];
            }

            // Verifier que c'est bien un compte mentor
            if ($user->user_type !== 'mentor') {
                // Compte jeune trouvé, proposer la migration
                return $this->handleCrossTypeReactivation($user, 'mentor', $userData, 'linkedin');
            }

            $user->update([
                'auth_provider' => 'linkedin',
                'provider_id' => $linkedinData['linkedin_id'],
                'last_login_at' => now(),
                // Reactivation automatique si archive
                'is_archived' => false,
                'archived_at' => null,
                'archived_reason' => null,
            ]);

            if ($user->wasChanged('is_archived')) {
                session()->flash('success', 'Bon retour ! Votre profil Mentor a été réactivé automatiquement.');
            }

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
                'last_login_at' => now(),
            ]);

            try {
                $this->notificationService->sendWelcomeEmail($user);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email bienvenue (OAuth Mentor): ' . $e->getMessage());
            }

            // Creer le profil mentor avec les donnees LinkedIn
            MentorProfile::create([
                'user_id' => $user->id,
                'linkedin_profile_data' => $linkedinData['raw_data'],
            ]);
        }

        // Télécharger et stocker l'avatar via le service
        if (!empty($linkedinData['avatar_url'])) {
            $this->avatarService->downloadFromUrl($user, $linkedinData['avatar_url']);
        }

        // --- NEW: Mentor Auto-linking via Invitations ---
        $referralCode = session('referral_code');
        if ($referralCode) {
            $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)
                ->where('status', 'pending')
                ->whereDate('expires_at', '>=', now())
                ->first();
            
            if ($invitation) {
                // Link mentor to organization in pivot table
                $user->organizations()->syncWithoutDetaching([
                    $invitation->organization_id => ['referral_code_used' => $referralCode]
                ]);
                
                // Mark invitation as used
                $invitation->markAsAccepted();
                
                // Clear referral code from session
                session()->forget(['referral_code', 'organization_name']);
                
                Log::info('Mentor auto-linked to organization via invitation', [
                    'user_id' => $user->id,
                    'organization_id' => $invitation->organization_id,
                    'referral_code' => $referralCode
                ]);
            }
        }

        Auth::login($user, true);

        if ($user->user_type === 'organization') {
            return [
                'success' => true,
                'redirect' => route('organization.dashboard')
            ];
        }

        return [
            'success' => true,
            'redirect' => route('mentor.dashboard')
        ];
    }

    /**
     * Affiche la page de choix du type de compte (inscription)
     */
    public function showChoice(Request $request)
    {
        // Detect referral code from URL (?ref=CODE)
        if ($request->has('ref')) {
            $referralCode = $request->get('ref');
            
            // Validate that the invitation exists
            $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)->first();
            
            if ($invitation) {
                if ($invitation->isValid()) {
                    // Store referral code in session
                    session(['referral_code' => $referralCode]);
                    session(['organization_name' => $invitation->organization->name]);
                    session()->forget('invitation_expired'); // Just in case
                } else {
                    // Invitation exists but is NOT valid (expired or accepted)
                    session(['invitation_expired' => true]);
                    session()->forget(['referral_code', 'organization_name']);
                }
            } else {
                session()->forget(['referral_code', 'organization_name', 'invitation_expired']);
            }
        }

        // Si l'utilisateur est déjà connecté, le rediriger vers son espace
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isJeune()) {
                // Rediriger vers l'onboarding si pas complété, sinon vers le dashboard
                return redirect()->route($user->onboarding_completed ? 'jeune.dashboard' : 'jeune.onboarding');
            }

            if ($user->isMentor()) {
                return redirect()->route('mentor.dashboard');
            }
        }

        return view('auth.choice');
    }

    /**
     * Affiche la page de choix du type de compte (connexion)
     */
    public function showLoginChoice()
    {
        // Si l'utilisateur est déjà connecté, le rediriger vers son espace
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isJeune()) {
                return redirect()->route($user->onboarding_completed ? 'jeune.dashboard' : 'jeune.onboarding');
            }

            if ($user->isMentor()) {
                return redirect()->route('mentor.dashboard');
            }
        }

        return view('auth.login-choice');
    }

    /**
     * Affiche le formulaire d'inscription jeune
     */
    public function showJeuneRegister(Request $request)
    {
        // Detect referral code from URL (?ref=CODE)
        if ($request->has('ref')) {
            $referralCode = $request->get('ref');
            
            // Validate that the invitation exists
            $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)->first();
            
            if ($invitation) {
                if ($invitation->isValid()) {
                    // Store referral code in session
                    session(['referral_code' => $referralCode]);
                    session(['organization_name' => $invitation->organization->name]);
                    session()->forget('invitation_expired');
                } else {
                    session(['invitation_expired' => true]);
                    session()->forget(['referral_code', 'organization_name']);
                }
            }
        }
        
        return view('auth.jeune.register', [
            'referralCode' => session('referral_code'),
            'organizationName' => session('organization_name'),
            'isExpired' => session('invitation_expired', false),
        ]);
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
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
        ], [
            'name.required' => 'Le nom complet est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password' => 'Le mot de passe doit contenir au moins 8 caractères, une lettre et un chiffre.',
        ]);

        // Optionnellement, creer l'utilisateur dans Supabase aussi
        $supabaseResult = $this->supabase->signUpWithEmail(
            $validated['email'],
            $validated['password'],
            ['name' => $validated['name']]
        );

        // Check for referral code in request (hidden field) or session
        $referralCode = $request->input('referral_code') ?? session('referral_code');
        $organizationId = null;

        Log::info('Jeune Registration Debug', [
            'referral_code_input' => $request->input('referral_code'),
            'referral_code_session' => session('referral_code'),
            'resolved_code' => $referralCode
        ]);
        
        if ($referralCode) {
            $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)
                ->where('status', 'pending')
                ->whereDate('expires_at', '>=', now()) // Only if NOT expired
                ->first();
            
            if ($invitation) {
                $organizationId = $invitation->organization_id;
                Log::info('Invitation found and valid', ['organization_id' => $organizationId]);
            } else {
                Log::warning('Invitation not found, not pending or expired', ['code' => $referralCode]);
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'jeune',
            'auth_provider' => 'email',
            'provider_id' => $supabaseResult['user']['id'] ?? null,
            'sponsored_by_organization_id' => $organizationId,
            'referral_code_used' => $referralCode,
            'last_login_at' => now(),
        ]);
        
        // Mark invitation as used
        if ($referralCode && isset($invitation)) {
            // Link user to organization in pivot table
            $user->organizations()->syncWithoutDetaching([
                $organizationId => ['referral_code_used' => $referralCode]
            ]);

            $invitation->markAsAccepted();
            
            // Clear referral code from session
            session()->forget(['referral_code', 'organization_name']);
        }

        try {
            $this->notificationService->sendWelcomeEmail($user);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email bienvenue: ' . $e->getMessage());
        }
 
        event(new Registered($user));
 
        Auth::login($user);

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
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
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

        // Vérifier si l'utilisateur est bloqué
        if ($user->is_blocked) {
            return back()->withErrors([
                'email' => 'Votre accès à Brillio a été suspendu pour non-respect des règles de la plateforme. Motif : ' . ($user->blocked_reason ?? 'Non spécifié'),
            ])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user->update(['last_login_at' => now()]);

            // Check for referral code in session (existing user logging in via link)
            $referralCode = session('referral_code');
            if ($referralCode) {
                $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)
                    ->where('status', 'pending')
                    ->whereDate('expires_at', '>=', now())
                    ->first();
                
                if ($invitation) {
                    // Link existing user to organization in pivot table
                    $user->organizations()->syncWithoutDetaching([
                        $invitation->organization_id => ['referral_code_used' => $referralCode]
                    ]);
                    
                    // Mark invitation as used
                    $invitation->markAsAccepted();
                    
                    // Clear referral code from session
                    session()->forget(['referral_code', 'organization_name']);
                }
            }

            // Reactivate archived account automatically
            if ($user->is_archived) {
                $user->is_archived = false;
                $user->archived_at = null;
                $user->archived_reason = null;
                $user->save();

                session()->flash('success', 'Bon retour ! Votre compte a été réactivé automatiquement.');
            }

            if ($user->user_type === 'organization') {
                 return redirect()->intended(route('organization.dashboard'));
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
        session([
            'oauth_provider' => $provider,
            'oauth_type' => 'jeune'
        ]);

        $oauthUrl = $this->supabase->getOAuthUrl($provider, $redirectUrl);

        return redirect($oauthUrl);
    }

    /**
     * Callback OAuth pour les jeunes - Affiche la page intermediaire
     * Supabase retourne les tokens dans le hash (#), pas en query params
     */
    public function jeuneOAuthCallback(Request $request, string $provider)
    {
        // Verifier si on a un code (Authorization Code flow)
        $code = $request->get('code');

        if ($code) {
            // Traiter directement le code
            return $this->processJeuneOAuthCode($code, $provider);
        }

        // Sinon, afficher la page intermediaire pour capturer le hash
        return view('auth.oauth-callback', [
            'processUrl' => route('auth.jeune.oauth.process', ['provider' => $provider]),
            'errorUrl' => route('auth.jeune.login'),
        ]);
    }

    /**
     * Traitement du code OAuth (Authorization Code flow)
     */
    protected function processJeuneOAuthCode(string $code, string $provider)
    {
        $session = $this->supabase->exchangeCodeForSession($code);

        if (!$session || !isset($session['access_token'])) {
            Log::error('Supabase code exchange failed', ['provider' => $provider]);
            return redirect()->route('auth.jeune.login')
                ->with('error', 'Erreur lors de la connexion. Veuillez reessayer.');
        }

        return $this->authenticateJeuneWithToken($session['access_token'], $provider);
    }

    /**
     * Traitement AJAX du token OAuth pour les jeunes
     */
    public function jeuneOAuthProcess(Request $request, string $provider)
    {
        try {
            Log::info('OAuth process started', [
                'provider' => $provider,
                'has_access_token' => $request->has('access_token'),
                'has_code' => $request->has('code'),
            ]);

            // Verifier si on a un access_token (Implicit/PKCE flow)
            if ($request->has('access_token')) {
                $accessToken = $request->input('access_token');

                Log::info('Getting user from Supabase with access token');

                $userData = $this->supabase->getUser($accessToken);

                if (!$userData) {
                    Log::warning('Failed to get user data from Supabase');
                    return response()->json([
                        'success' => false,
                        'error' => 'Impossible de recuperer vos informations.'
                    ], 400);
                }

                Log::info('User data retrieved', ['email' => $userData['email'] ?? 'unknown']);

                $result = $this->createOrUpdateJeuneUser($userData, $provider);

                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'redirect' => $result['redirect']
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }

            // Verifier si on a un code (Authorization Code flow)
            if ($request->has('code')) {
                $code = $request->input('code');

                Log::info('Exchanging code for session');

                $session = $this->supabase->exchangeCodeForSession($code);

                if (!$session || !isset($session['access_token'])) {
                    Log::warning('Failed to exchange code for session');
                    return response()->json([
                        'success' => false,
                        'error' => 'Erreur lors de l\'echange du code.'
                    ], 400);
                }

                $userData = $this->supabase->getUser($session['access_token']);

                if (!$userData) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Impossible de recuperer vos informations.'
                    ], 400);
                }

                $result = $this->createOrUpdateJeuneUser($userData, $provider);

                // Gestion spécifique pour la redirection de confirmation de type
                if (isset($result['success']) && $result['success'] === 'redirect_confirm') {
                    return response()->json([
                        'success' => false,
                        'redirect' => $result['redirect']
                    ]);
                }

                if (isset($result['success']) && $result['success'] === true) {
                    return response()->json([
                        'success' => true,
                        'redirect' => $result['redirect']
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Une erreur est survenue.'
                ], 400);
            }

            return response()->json([
                'success' => false,
                'error' => 'Aucune information d\'authentification fournie.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('OAuth process error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Une erreur inattendue est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authentifie un jeune avec un token d'acces
     */
    protected function authenticateJeuneWithToken(string $accessToken, string $provider)
    {
        $userData = $this->supabase->getUser($accessToken);

        if (!$userData) {
            return redirect()->route('auth.jeune.login')
                ->with('error', 'Impossible de recuperer vos informations.');
        }

        $result = $this->createOrUpdateJeuneUser($userData, $provider);

        if (!$result['success']) {
            return redirect()->route('auth.jeune.login')
                ->with('error', $result['error']);
        }

        return redirect($result['redirect']);
    }

    /**
     * Cree ou met a jour un utilisateur jeune
     */
    protected function createOrUpdateJeuneUser(array $userData, string $provider): array
    {
        $socialData = $this->supabase->extractSocialData($userData);

        if (empty($socialData['email'])) {
            return [
                'success' => false,
                'error' => 'Impossible de recuperer votre adresse email.'
            ];
        }

        $user = User::where('email', $socialData['email'])->first();

        if ($user) {
            // Vérifier si l'utilisateur est bloqué
            if ($user->is_blocked) {
                return [
                    'success' => false,
                    'error' => 'Votre accès Jeune a été suspendu par l\'administration. Motif : ' . ($user->blocked_reason ?? 'Non spécifié')
                ];
            }

            // Verifier que c'est bien un compte jeune
            if ($user->user_type !== 'jeune') {
                // Compte mentor trouvé, proposer la migration
                return $this->handleCrossTypeReactivation($user, 'jeune', $userData, $provider);
            }

            // Mettre a jour les infos
            $user->update([
                'auth_provider' => $provider,
                'provider_id' => $userData['id'] ?? $user->provider_id,
                'profile_photo_url' => $socialData['avatar_url'] ?? $user->profile_photo_url,
                'last_login_at' => now(),
                // Reactivation automatique si archive
                'is_archived' => false,
                'archived_at' => null,
                'archived_reason' => null,
            ]);

            if ($user->wasChanged('is_archived')) {
                session()->flash('success', 'Bon retour ! Votre compte a été réactivé automatiquement.');
            }

            // Check for referral code in session (existing user logging in via link)
            $referralCode = session('referral_code');
            if ($referralCode) {
                $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)
                    ->where('status', 'pending')
                    ->whereDate('expires_at', '>=', now())
                    ->first();
                
                if ($invitation) {
                    // Link existing user to organization in pivot table
                    $user->organizations()->syncWithoutDetaching([
                        $invitation->organization_id => ['referral_code_used' => $referralCode]
                    ]);
                    
                    // Mark invitation as used
                    $invitation->markAsAccepted();
                    
                    // Clear referral code from session
                    session()->forget(['referral_code', 'organization_name']);
                }
            }
        } else {
            // Check for referral code in session (for OAuth)
            $referralCode = session('referral_code');
            $organizationId = null;

            if ($referralCode) {
                $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)
                    ->where('status', 'pending')
                    ->whereDate('expires_at', '>=', now())
                    ->first();
                
                if ($invitation) {
                    $organizationId = $invitation->organization_id;
                    
                    // Mark invitation as used
                    $invitation->markAsAccepted();
                    
                    // Clear referral code from session
                    session()->forget(['referral_code', 'organization_name']);
                }
            }

            // Creer un nouveau compte jeune
            $user = User::create([
                'sponsored_by_organization_id' => $organizationId,
                'referral_code_used' => $referralCode,
                'name' => $socialData['name'] ?? 'Utilisateur',
                'email' => $socialData['email'],
                'password' => Hash::make(Str::random(32)),
                'user_type' => 'jeune',
                'auth_provider' => $provider,
                'provider_id' => $userData['id'] ?? null,
                'profile_photo_url' => $socialData['avatar_url'],
                'email_verified_at' => now(), // Social login users are considered verified
                'last_login_at' => now(),
            ]);

            // Also link to organization via pivot table if registering via link
            if ($organizationId) {
                $user->organizations()->syncWithoutDetaching([
                    $organizationId => ['referral_code_used' => $referralCode]
                ]);
            }

            try {
                $this->notificationService->sendWelcomeEmail($user);
            } catch (\Exception $e) {
                Log::error('Erreur envoi email bienvenue (OAuth Jeune): ' . $e->getMessage());
            }
        }

        // Télécharger et stocker l'avatar via le service
        if (!empty($socialData['avatar_url'])) {
            $this->avatarService->downloadFromUrl($user, $socialData['avatar_url']);
        }

        Auth::login($user, true);

        $redirect = !$user->onboarding_completed
            ? route('jeune.onboarding')
            : route('jeune.dashboard');

        return [
            'success' => true,
            'redirect' => $redirect
        ];
    }

    /**
     * Affiche la page de connexion mentor (LinkedIn uniquement)
     */
    public function showMentorLogin(Request $request)
    {
        // Detect referral code from URL (?ref=CODE)
        if ($request->has('ref')) {
            $referralCode = $request->get('ref');
            
            // Validate that the invitation exists
            $invitation = \App\Models\OrganizationInvitation::where('referral_code', $referralCode)->first();
            
            if ($invitation) {
                if ($invitation->isValid()) {
                    // Store referral code in session
                    session(['referral_code' => $referralCode]);
                    session(['organization_name' => $invitation->organization->name]);
                    session()->forget('invitation_expired');
                } else {
                    session(['invitation_expired' => true]);
                    session()->forget(['referral_code', 'organization_name']);
                }
            }
        }

        return view('auth.mentor.login', [
            'isExpired' => session('invitation_expired', false),
        ]);
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
     * Callback LinkedIn pour les mentors - Affiche la page intermediaire
     */
    public function mentorLinkedInCallback(Request $request)
    {
        // Verifier si on a un code (Authorization Code flow)
        $code = $request->get('code');

        if ($code) {
            return $this->processMentorLinkedInCode($code);
        }

        // Sinon, afficher la page intermediaire pour capturer le hash
        return view('auth.oauth-callback', [
            'processUrl' => route('auth.mentor.linkedin.process'),
            'errorUrl' => route('auth.mentor.login'),
        ]);
    }

    /**
     * Traitement du code LinkedIn (Authorization Code flow)
     */
    protected function processMentorLinkedInCode(string $code)
    {
        $session = $this->supabase->exchangeCodeForSession($code);

        if (!$session || !isset($session['access_token'])) {
            Log::error('Supabase LinkedIn code exchange failed');
            return redirect()->route('auth.mentor.login')
                ->with('error', 'Erreur lors de la connexion LinkedIn.');
        }

        return $this->authenticateMentorWithToken($session['access_token']);
    }

    /**
     * Traitement AJAX du token LinkedIn pour les mentors
     */
    public function mentorLinkedInProcess(Request $request)
    {
        try {
            // Verifier si on a un access_token (Implicit/PKCE flow)
            if ($request->has('access_token')) {
                $accessToken = $request->input('access_token');

                $userData = $this->supabase->getUser($accessToken);

                if (!$userData) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Impossible de recuperer vos informations LinkedIn.'
                    ], 400);
                }

                $result = $this->createOrUpdateMentorUser($userData);

                // Gestion spécifique pour la redirection de confirmation de type
                if (isset($result['success']) && $result['success'] === 'redirect_confirm') {
                    return response()->json([
                        'success' => false,
                        'redirect' => $result['redirect']
                    ]);
                }

                if (isset($result['success']) && $result['success'] === true) {
                    return response()->json([
                        'success' => true,
                        'redirect' => $result['redirect']
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Une erreur est survenue.'
                ], 400);
            }

            // Verifier si on a un code (Authorization Code flow)
            if ($request->has('code')) {
                $code = $request->input('code');
                $session = $this->supabase->exchangeCodeForSession($code);

                if (!$session || !isset($session['access_token'])) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Erreur lors de l\'echange du code LinkedIn.'
                    ], 400);
                }

                $userData = $this->supabase->getUser($session['access_token']);

                if (!$userData) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Impossible de recuperer vos informations LinkedIn.'
                    ], 400);
                }

                $result = $this->createOrUpdateMentorUser($userData);

                // Gestion spécifique pour la redirection de confirmation de type
                if (isset($result['success']) && $result['success'] === 'redirect_confirm') {
                    return response()->json([
                        'success' => false,
                        'redirect' => $result['redirect']
                    ]);
                }

                if (isset($result['success']) && $result['success'] === true) {
                    return response()->json([
                        'success' => true,
                        'redirect' => $result['redirect']
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Une erreur est survenue.'
                ], 400);
            }

            return response()->json([
                'success' => false,
                'error' => 'Aucune information d\'authentification fournie.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('LinkedIn process error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Une erreur inattendue est survenue.'
            ], 500);
        }
    }

    /**
     * Authentifie un mentor avec un token d'acces
     */
    protected function authenticateMentorWithToken(string $accessToken)
    {
        $userData = $this->supabase->getUser($accessToken);

        if (!$userData) {
            return redirect()->route('auth.mentor.login')
                ->with('error', 'Impossible de recuperer vos informations LinkedIn.');
        }

        $result = $this->createOrUpdateMentorUser($userData);

        if (!$result['success']) {
            return redirect()->route('auth.mentor.login')
                ->with('error', $result['error']);
        }

        return redirect($result['redirect']);
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

    /**
     * Afficher le formulaire de mot de passe oublié
     */
    public function showForgotPasswordForm()
    {
        return view('auth.jeune.forgot-password');
    }

    /**
     * Envoyer le lien de réinitialisation
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)
            ->where('user_type', 'jeune')
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Aucun compte jeune trouve avec cette adresse email.']);
        }

        // Générer un token unique
        $token = Str::random(64);

        // Stocker le token (expire après 10 minutes)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Envoyer l'email
        try {
            Mail::to($user)->send(new \App\Mail\ResetPasswordMail($user, $token));
        } catch (\Exception $e) {
            Log::error('Erreur envoi email reset password: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email. Veuillez reessayer.']);
        }

        return back()->with('status', 'Un lien de reinitialisation a ete envoye a votre adresse email.');
    }

    /**
     * Afficher le formulaire de réinitialisation
     */
    public function showResetForm($token, Request $request)
    {
        return view('auth.jeune.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.min' => 'Le mot de passe doit contenir au moins 8 caracteres.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        // Vérifier le token
        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Lien de reinitialisation invalide.']);
        }

        // Vérifier que le token n'a pas expiré (10 minutes)
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(10)->isPast()) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Le lien de reinitialisation a expire. Veuillez en demander un nouveau.']);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Lien de reinitialisation invalide.']);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)
            ->where('user_type', 'jeune')
            ->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Utilisateur introuvable.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token utilisé
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('auth.jeune.login')
            ->with('status', 'Votre mot de passe a ete reinitialise avec succes. Vous pouvez maintenant vous connecter.');
    }

    // ==========================================
    // ACCOUNT TYPE MIGRATION METHODS
    // ==========================================

    /**
     * Gère la réactivation cross-type (jeune → mentor ou mentor → jeune)
     */
    protected function handleCrossTypeReactivation(User $user, string $newType, array $oauthData, string $provider): array
    {
        // Si le compte est ACTIF (pas archivé), refuser la migration
        if (!$user->is_archived) {
            $errorMessage = $user->user_type === 'jeune'
                ? "Un compte jeune actif existe déjà avec cet email. Pour devenir mentor, vous devez d'abord archiver votre compte jeune depuis la page Profil > Zone de danger. <br><br><strong>⚠️ Important :</strong> Les comptes mentors sont soumis à une vérification stricte pour garantir la qualité. Si nous détectons que votre profil ne correspond pas aux critères de mentor, vous serez rétrogradé en compte jeune."
                : "Un compte mentor actif existe déjà avec cet email. Pour devenir jeune, vous devez d'abord archiver votre compte mentor depuis la page Statistiques > Zone de danger.";

            // Déterminer l'URL de redirection correcte selon le type de compte existant
            $redirectUrl = $user->user_type === 'jeune'
                ? route('auth.jeune.login')
                : route('auth.mentor.login');

            return [
                'success' => false,
                'error' => $errorMessage,
                'redirect_url' => $redirectUrl // URL de la page de login du compte existant
            ];
        }

        // Compte archivé → Créer un token de migration temporaire
        $migration = \App\Models\AccountTypeMigration::create([
            'user_id' => $user->id,
            'old_type' => $user->user_type,
            'new_type' => $newType,
            'token' => Str::random(64),
            'oauth_data' => [
                'provider' => $provider,
                'provider_id' => $oauthData['id'] ?? null,
                'email' => $oauthData['email'] ?? $user->email,
                'name' => $oauthData['user_metadata']['name'] ?? $oauthData['name'] ?? null,
                'avatar_url' => $oauthData['user_metadata']['avatar_url'] ?? $oauthData['avatar_url'] ?? null,
            ],
            'expires_at' => now()->addHours(24),
        ]);

        // Rediriger vers la page de confirmation
        return [
            'success' => 'redirect_confirm',
            'redirect' => route('auth.confirm-type-change', ['token' => $migration->token])
        ];
    }

    /**
     * Affiche la page de confirmation de changement de type
     */
    public function showConfirmTypeChange(Request $request)
    {
        $migration = \App\Models\AccountTypeMigration::where('token', $request->token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('auth.confirm-type-change', [
            'user' => $migration->user,
            'oldType' => $migration->old_type,
            'newType' => $migration->new_type,
            'token' => $migration->token,
            'isArchived' => $migration->user->is_archived,
        ]);
    }

    /**
     * Traite la confirmation de changement de type
     */
    public function confirmTypeChange(Request $request)
    {
        $migration = \App\Models\AccountTypeMigration::where('token', $request->token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = $migration->user;
        $action = $request->action;

        switch ($action) {
            case 'migrate':
                return $this->migrateAccountType($user, $migration);

            case 'keep':
                return $this->keepCurrentType($user, $migration);

            default:
                abort(400, 'Action invalide');
        }
    }

    /**
     * Migre le compte vers le nouveau type
     */
    protected function migrateAccountType(User $user, \App\Models\AccountTypeMigration $migration)
    {
        // Réactiver si archivé
        if ($user->is_archived) {
            $user->is_archived = false;
            $user->archived_at = null;
            $user->archived_reason = null;
        }

        // Changer le type
        $user->user_type = $migration->new_type;

        // Mettre à jour OAuth data si fournies
        if ($migration->oauth_data) {
            $user->auth_provider = $migration->oauth_data['provider'] ?? $user->auth_provider;
            $user->provider_id = $migration->oauth_data['provider_id'] ?? $user->provider_id;

            if (!empty($migration->oauth_data['avatar_url'])) {
                $user->profile_photo_url = $migration->oauth_data['avatar_url'];
            }
        }

        $user->last_login_at = now();
        $user->save();

        // Créer profil si nécessaire
        if ($migration->new_type === 'mentor' && !$user->mentorProfile) {
            $user->mentorProfile()->create([]);
        } elseif ($migration->new_type === 'jeune' && !$user->jeuneProfile) {
            $user->jeuneProfile()->create([]);
        }

        // Supprimer le token de migration
        $migration->delete();

        // Connecter l'utilisateur
        Auth::login($user, true);

        return redirect()->route($migration->new_type . '.dashboard')
            ->with('success', "Votre compte a été réactivé en tant que {$migration->new_type} !");
    }

    /**
     * Garde le type actuel et redirige vers la bonne page de connexion
     */
    protected function keepCurrentType(User $user, \App\Models\AccountTypeMigration $migration)
    {
        // Supprimer le token de migration
        $migration->delete();

        // Rediriger vers la page de connexion appropriée
        $route = $user->user_type === 'jeune' ? 'auth.jeune.login' : 'auth.mentor.login';

        return redirect()->route($route)
            ->with('info', "Veuillez vous connecter avec votre compte {$user->user_type}.");
    }

    /**
     * Traite l'acceptation de promotion (Lien magique depuis l'email)
     */
    public function acceptPromotion(Request $request, User $user)
    {
        // 1. Archiver le compte actuel (Jeune) pour libérer l'accès
        $user->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_reason' => 'Acceptation de la promotion au statut Mentor (Automatique).',
        ]);

        // 2. Déconnecter l'utilisateur s'il est connecté pour éviter les conflits de session
        if (Auth::check() && Auth::id() === $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // 3. Rediriger directement vers la connexion Mentor via LinkedIn
        return redirect()->route('auth.mentor.login')
            ->with('success', 'Félicitations ! Votre compte Jeune a été archivé. Vous pouvez maintenant vous connecter via LinkedIn pour activer votre profil Mentor.');
    }

}

 