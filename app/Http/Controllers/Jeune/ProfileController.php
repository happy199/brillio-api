<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Affiche la page de profil
     */
    public function index()
    {
        $user = auth()->user()->load('personalityTest');

        // S'assurer que le profil existe
        $profile = $user->jeuneProfile ?? $user->jeuneProfile()->create();

        return view('jeune.profile', compact('user', 'profile'));
    }

    /**
     * Met à jour le profil
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $profile = $user->jeuneProfile;

        $validated = $request->validate([
            // Champs User
            'name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'city' => 'nullable|string|max:100',
            'linkedin_url' => 'nullable|url|max:255',

            // Champs JeuneProfile
            'bio' => 'nullable|string|max:2000',
            'portfolio_url' => 'nullable|url|max:255',
            'cv' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
            'is_public' => 'boolean',
        ]);

        // Mise à jour User
        $user->update([
            'name' => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'],
            'city' => $validated['city'],
            'linkedin_url' => $validated['linkedin_url'],
        ]);

        // Gestion du CV
        if ($request->hasFile('cv')) {
            // Supprimer l'ancien CV si existant
            if ($profile->cv_path) {
                Storage::delete($profile->cv_path);
            }
            $path = $request->file('cv')->store('cvs', 'public');
            $profile->cv_path = $path;
        }

        // Gestion du Slug Public
        $isPublic = $request->boolean('is_public');
        $slug = $profile->public_slug;

        if ($isPublic && empty($slug)) {
            // Générer un slug unique
            $baseSlug = Str::slug($user->name);
            $slug = $baseSlug;
            $counter = 1;
            while (\App\Models\JeuneProfile::where('public_slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        // Mise à jour JeuneProfile
        $profile->update([
            'bio' => $validated['bio'],
            'portfolio_url' => $validated['portfolio_url'],
            'is_public' => $isPublic,
            'public_slug' => $isPublic ? $slug : $slug, // On garde le slug même si on passe en privé
        ]);

        // Sauvegarder le chemin du CV si modifié
        if ($request->hasFile('cv')) {
            $profile->save();
        }

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
