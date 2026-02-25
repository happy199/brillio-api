<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachAuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion coach
     */
    public function showLoginForm()
    {
        return view('admin.auth.login', [
            'title' => 'Espace Coach Brillio',
            'loginRoute' => 'coach.login.post',
        ]);
    }

    /**
     * Traite la connexion coach
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Vérifier si l'utilisateur est un coach
            if (! Auth::user()->isCoach()) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Accès réservé aux coachs.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('coach.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ])->onlyInput('email');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('coach.login');
    }
}
