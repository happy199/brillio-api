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
        ]);

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

        return view('mentor.stats', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}
