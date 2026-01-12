<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use Illuminate\Http\Request;

class MentorDashboardController extends Controller
{
    /**
     * Dashboard principal du mentor
     */
    public function index()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        // Stats
        $stats = [
            'profile_views' => 0, // A implementer avec un systeme de tracking
            'roadmap_steps' => $profile ? $profile->roadmapSteps()->count() : 0,
            'is_published' => $profile ? $profile->is_published : false,
            'profile_complete' => $profile ? $profile->isComplete() : false,
        ];

        return view('mentor.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    /**
     * Page du profil mentor
     */
    public function profile()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        $specializations = MentorProfile::SPECIALIZATIONS;

        return view('mentor.profile', [
            'user' => $user,
            'profile' => $profile,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Mise a jour du profil mentor
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'bio' => 'required|string|max:2000',
            'current_position' => 'required|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'years_of_experience' => 'required|integer|min:0|max:50',
            'specialization' => 'required|string|in:' . implode(',', array_keys(MentorProfile::SPECIALIZATIONS)),
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'advice' => 'nullable|string|max:1000',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');

        $user = auth()->user();
        $profile = $user->mentorProfile;

        if ($profile) {
            $profile->update($validated);
        } else {
            $profile = MentorProfile::create([
                'user_id' => $user->id,
                ...$validated,
            ]);
        }

        return back()->with('success', 'Votre profil a ete mis a jour.');
    }

    /**
     * Page du parcours (roadmap)
     */
    public function roadmap()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;
        $steps = $profile ? $profile->roadmapSteps()->orderBy('position')->get() : collect();

        return view('mentor.roadmap', [
            'user' => $user,
            'profile' => $profile,
            'steps' => $steps,
        ]);
    }

    /**
     * Page des statistiques
     */
    public function stats()
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        $stats = [
            'profile_views' => 0, // A implementer
        ];

        return view('mentor.stats', [
            'user' => $user,
            'profile' => $profile,
            'stats' => $stats,
        ]);
    }

    /**
     * Recuperer une etape du roadmap
     */
    public function getStep($stepId)
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $step = $profile->roadmapSteps()->findOrFail($stepId);

        return response()->json($step);
    }

    /**
     * Ajouter une etape au roadmap
     */
    public function storeStep(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'year_start' => 'nullable|integer|min:1950|max:2030',
            'year_end' => 'nullable|integer|min:1950|max:2030',
            'description' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
        ]);

        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (!$profile) {
            $profile = MentorProfile::create(['user_id' => $user->id]);
        }

        // Determiner la position
        $maxPosition = $profile->roadmapSteps()->max('position') ?? 0;

        $step = $profile->roadmapSteps()->create([
            ...$validated,
            'position' => $maxPosition + 1,
        ]);

        return response()->json($step, 201);
    }

    /**
     * Mettre a jour une etape
     */
    public function updateStep(Request $request, $stepId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'year_start' => 'nullable|integer|min:1950|max:2030',
            'year_end' => 'nullable|integer|min:1950|max:2030',
            'description' => 'nullable|string|max:1000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
        ]);

        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $step = $profile->roadmapSteps()->findOrFail($stepId);
        $step->update($validated);

        return response()->json($step);
    }

    /**
     * Supprimer une etape
     */
    public function deleteStep($stepId)
    {
        $user = auth()->user();
        $profile = $user->mentorProfile;

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $step = $profile->roadmapSteps()->findOrFail($stepId);
        $step->delete();

        return response()->json(['success' => true]);
    }
}
