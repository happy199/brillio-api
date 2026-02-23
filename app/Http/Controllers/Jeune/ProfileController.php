<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        // Ensure profile exists to avoid 500 error if accessing update directly
        $profile = $user->jeuneProfile ?? $user->jeuneProfile()->create();

        $validated = $request->validate([
            // Champs User
            'name' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'city' => 'sometimes|nullable|string|max:100',
            'linkedin_url' => 'sometimes|nullable|url|max:255',

            // Champs JeuneProfile
            'bio' => 'sometimes|nullable|string|max:2000',
            'portfolio_url' => 'sometimes|nullable|url|max:255',
            'cv' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
            'is_public' => 'sometimes|boolean',
        ]);

        // Mise à jour User (seulement les champs présents)
        $userUpdates = [];
        if (array_key_exists('name', $validated)) {
            $userUpdates['name'] = $validated['name'];
        }
        if (array_key_exists('date_of_birth', $validated)) {
            $userUpdates['date_of_birth'] = $validated['date_of_birth'];
        }
        if (array_key_exists('city', $validated)) {
            $userUpdates['city'] = $validated['city'];
        }
        if (array_key_exists('linkedin_url', $validated)) {
            $userUpdates['linkedin_url'] = $validated['linkedin_url'];
        }

        if (! empty($userUpdates)) {
            $user->update($userUpdates);
        }

        // Gestion de la photo de profil
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si existante
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->update(['profile_photo_path' => $path]);
        }

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

        // Si is_public n'est pas dans la requête, on garde la valeur actuelle (pour les forms partiels)
        if (! $request->has('is_public')) {
            $isPublic = $profile->is_public;
        }

        $slug = $profile->public_slug;

        if ($isPublic && empty($slug)) {
            // Générer un slug unique avec hash (format: nom-prenom-hash)
            $baseSlug = Str::slug(Str::limit($user->name, 30, ''));
            $hash = substr(md5(uniqid().time()), 0, 8);
            $slug = $baseSlug.'-'.$hash;

            // Vérification au cas où (collision très improbable)
            $counter = 1;
            while (\App\Models\JeuneProfile::where('public_slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$hash.'-'.$counter;
                $counter++;
            }
        }

        // Mise à jour JeuneProfile (seulement les champs présents + logique is_public)
        $profileUpdates = [];
        if (array_key_exists('bio', $validated)) {
            $profileUpdates['bio'] = $validated['bio'];
        }
        if (array_key_exists('portfolio_url', $validated)) {
            $profileUpdates['portfolio_url'] = $validated['portfolio_url'];
        }

        // Toujours mettre à jour is_public et public_slug si is_public a changé ou est présent
        if ($request->has('is_public')) {
            $profileUpdates['is_public'] = $isPublic;
            $profileUpdates['public_slug'] = $isPublic ? $slug : $slug;
        } elseif ($isPublic && empty($profile->public_slug)) {
            // Cas rare: on était déjà public mais sans slug (ex: migration), on force update slug
            $profileUpdates['public_slug'] = $slug;
        }

        if (! empty($profileUpdates)) {
            $profile->update($profileUpdates);
        }

        // Sauvegarder le chemin du CV si modifié
        if ($request->hasFile('cv')) {
            $profile->save();
        }

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Publier le profil jeune
     */
    public function publishProfile()
    {
        $user = auth()->user();
        $profile = $user->jeuneProfile ?? $user->jeuneProfile()->create();

        // Si déjà public, rien à faire
        if ($profile->is_public) {
            return back()->with('info', 'Votre profil est déjà visible.');
        }

        // Génération du slug si nécessaire (logique idem update)
        $slug = $profile->public_slug;
        if (empty($slug)) {
            $baseSlug = Str::slug(Str::limit($user->name, 30, ''));
            $hash = substr(md5(uniqid().time()), 0, 8);
            $slug = $baseSlug.'-'.$hash;

            $counter = 1;
            while (\App\Models\JeuneProfile::where('public_slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$hash.'-'.$counter;
                $counter++;
            }
        }

        $profile->update([
            'is_public' => true,
            'public_slug' => $slug,
        ]);

        return back()->with('success', 'Votre profil est maintenant visible par les mentors !');
    }
}
