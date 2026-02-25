<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MentorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CoachController extends Controller
{
    /**
     * Liste tous les coachs
     */
    public function index(Request $request)
    {
        $query = User::where('is_coach', true)->with(['mentorProfile']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $coaches = $query->paginate(25);

        // Liste des mentors non-coachs pour la promotion
        $availableMentors = User::where('user_type', User::TYPE_MENTOR)
            ->where('is_coach', false)
            ->orderBy('name')
            ->get();

        return view('admin.coaches.index', compact('coaches', 'availableMentors'));
    }

    /**
     * Promouvoir un ou plusieurs mentors en coach ou créer un nouveau coach
     */
    public function store(Request $request)
    {
        // Cas 1 : Promotion de mentors existants
        if ($request->has('user_ids')) {
            $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $users = User::whereIn('id', $request->user_ids)->get();

            foreach ($users as $user) {
                // Pour la promotion, on génère un mot de passe temporaire
                $tempPassword = Str::random(12);
                $user->update([
                    'is_coach' => true,
                    'password' => Hash::make($tempPassword),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);

                // Envoyer l'email avec les accès
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\Admin\CoachCredentialsMail($user, $tempPassword));
            }

            $count = $users->count();
            $message = $count > 1 ? "{$count} mentors ont été promus Coach et ont reçu leurs accès par email." : "Le mentor a été promu Coach et a reçu ses accès par email.";

            return back()->with('success', $message);
        }

        // Cas 2 : Promotion d'un seul mentor (fallback legacy si besoin)
        if ($request->has('user_id')) {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $user = User::findOrFail($request->user_id);
            $tempPassword = Str::random(12);
            $user->update([
                'is_coach' => true,
                'password' => Hash::make($tempPassword),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\Admin\CoachCredentialsMail($user, $tempPassword));

            return back()->with('success', "{$user->name} a été promu Coach et a reçu ses accès par email.");
        }

        // Cas 2 : Création d'un nouveau coach
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'user_type' => User::TYPE_MENTOR,
            'is_coach' => true,
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);

        // Créer un profil mentor basique
        MentorProfile::create([
            'user_id' => $user->id,
            'is_validated' => true,
            'bio' => 'Compte Coach créé par l\'administration.',
        ]);

        // Envoyer l'email
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\Admin\CoachCredentialsMail($user, $password));

        return back()->with('success', "Nouveau compte Coach créé. Les accès ont été envoyés à {$user->email}.");
    }

    /**
     * Réinitialiser le mot de passe d'un coach et lui envoyer ses accès
     */
    public function resetPassword(User $coach)
    {
        $password = Str::random(12);
        $coach->update([
            'password' => Hash::make($password)
        ]);

        \Illuminate\Support\Facades\Mail::to($coach->email)->send(new \App\Mail\Admin\CoachCredentialsMail($coach, $password));

        return back()->with('success', "Le mot de passe de {$coach->name} a été réinitialisé et envoyé par email.");
    }

    /**
     * Révoquer le statut de coach
     */
    public function destroy(User $coach)
    {
        $coach->update(['is_coach' => false]);

        return back()->with('success', "Le statut de Coach a été révoqué pour {$coach->name}.");
    }
}