<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userProfile = $user->onboarding_data ?? [];

        $userEducation = $userProfile['education_level'] ?? null;
        $userSituation = $userProfile['current_situation'] ?? null;
        $userInterests = $userProfile['interests'] ?? [];
        $userCountry = $userProfile['country'] ?? null;

        // Récupérer toutes les ressources validées et publiées
        $resources = Resource::where('is_published', true)
            ->where('is_validated', true)
            ->with('user') // Le créateur (Mentor/Admin)
            ->orderByDesc('created_at')
            ->get();

        // Filtrage PHP pour le ciblage
        $filteredResources = $resources->filter(function ($resource) use ($userEducation, $userSituation, $userInterests, $userCountry) {
            $targeting = $resource->targeting;

            // Si pas de ciblage, c'est pour tout le monde
            if (empty($targeting)) {
                return true;
            }

            // Vérification Niveau d'études
            $targetEducations = $targeting['education_levels'] ?? [];
            if (!empty($targetEducations) && $userEducation && !in_array($userEducation, $targetEducations)) {
                return false;
            }

            // Vérification Situation
            $targetSituations = $targeting['situations'] ?? [];
            if (!empty($targetSituations) && $userSituation && !in_array($userSituation, $targetSituations)) {
                return false;
            }

            // Vérification Pays
            $targetCountries = $targeting['countries'] ?? [];
            // Matching flou pour le pays (ex: "Benin" vs "Bénin") ou inclusion
            if (!empty($targetCountries) && $userCountry) {
                // On vérifie si le pays de l'user est dans la liste (simplifié)
                // Idéalement il faudrait normaliser les noms de pays
                $match = false;
                foreach ($targetCountries as $country) {
                    if (str_contains(strtolower($userCountry), strtolower($country))) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) {
                    return false;
                }
            }

            // Vérification Intérêts (Au moins un intérêt en commun)
            $targetInterests = $targeting['interests'] ?? [];
            if (!empty($targetInterests) && !empty($userInterests)) {
                $commonInterests = array_intersect($targetInterests, $userInterests);
                if (empty($commonInterests)) {
                    return false;
                }
            }

            return true;
        });

        // Pagination manuelle après filtrage (si nécessaire, ou juste take/slice)
        // Pour l'instant on retourne tout (MVP)

        return view('jeune.resources.index', [
            'resources' => $filteredResources,
            'user' => $user
        ]);
    }

    public function show(Resource $resource)
    {
        // Vérification basique
        if (!$resource->is_published || !$resource->is_validated) {
            abort(404);
        }

        return view('jeune.resources.show', compact('resource'));
    }
}
