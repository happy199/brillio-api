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
    public function showRegistrationForm()
    {
        return view('organization.auth.register');
    }

    /**
     * Handle organization registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'sector' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create organization
        $organization = Organization::create([
            'name' => $validated['organization_name'],
            'contact_email' => $validated['email'],
            'sector' => $validated['sector'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'website' => $validated['website'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => 'active',
        ]);

        // Create user account for organization
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'organization',
            'organization_id' => $organization->id, // Link to the created organization
            'onboarding_completed' => true,
        ]);

        // Auto-login
        Auth::login($user);

        return redirect()->route('organization.dashboard')
            ->with('success', 'Bienvenue ! Votre compte organisation a été créé avec succès.');
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

            // Check if organization is active
            $user = Auth::user();
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