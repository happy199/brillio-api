<?php

namespace App\Http\Controllers\Organization\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm(Request $request)
    {
        $invitation = null;
        if ($request->has('ref')) {
            $invitation = \App\Models\OrganizationInvitation::where('referral_code', $request->ref)
                ->whereIn('role', ['admin', 'viewer'])
                ->first();

            if (!$invitation || !$invitation->isValid()) {
                return redirect()->route('organization.login')
                    ->with('error', 'Le lien d\'invitation est invalide ou expiré.');
            }
        }

        return view('organization.auth.register', compact('invitation'));
    }

    /**
     * Handle organization registration
     */
    public function register(Request $request)
    {
        // Check for invitation
        $invitation = null;
        $isJoining = false;

        if ($request->has('ref')) {
            $invitation = \App\Models\OrganizationInvitation::where('referral_code', $request->ref)
                ->whereIn('role', ['admin', 'viewer'])
                ->first();

            if ($invitation && $invitation->isValid()) {
                $isJoining = true;
            }
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        // Only require organization details if NOT joining an existing one
        if (!$isJoining) {
            $rules['organization_name'] = ['required', 'string', 'max:255'];
            $rules['sector'] = ['nullable', 'string', 'max:100'];
            $rules['phone'] = ['nullable', 'string', 'max:20'];
            $rules['website'] = ['nullable', 'url', 'max:255'];
            $rules['description'] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);

        if ($isJoining) {
            $organization = $invitation->organization;
            $role = $invitation->role; // 'admin' or 'viewer'

            // Mark invitation as used
            $invitation->markAsAccepted();
        }
        else {
            // Create new organization
            $organization = Organization::create([
                'name' => $validated['organization_name'],
                'contact_email' => $validated['email'],
                'sector' => $validated['sector'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'website' => $validated['website'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => 'active',
            ]);
            $role = 'owner';
        }

        // Create user account for organization
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'organization',
            'organization_id' => $organization->id, // Link to the organization
            'organization_role' => $role, // owner, admin, or viewer
            'onboarding_completed' => true,
            'last_login_at' => now(),
        ]);

        // Auto-login
        Auth::login($user);

        return redirect()->route('organization.dashboard')
            ->with('success', $isJoining ? "Bienvenue ! Vous avez rejoint l'équipe avec succès." : "Bienvenue ! Votre compte organisation a été créé avec succès.");
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('organization.auth.login');
    }

    /**
     * Handle organization login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt login
        if (Auth::attempt([
        ...$credentials,
        'user_type' => 'organization'
        ], $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            // Check if organization is active
            if ($user->organization && $user->organization->status === 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Votre compte organisation est inactif. Veuillez contacter le support.',
                ])->onlyInput('email');
            }

            return redirect()->intended(route('organization.dashboard'))
                ->with('success', 'Connexion réussie !');
        }

        return back()->withErrors([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('organization.login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }
}