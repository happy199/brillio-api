<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\PersonalityTest;
use Illuminate\Http\Request;

class JeuneDashboardController extends Controller
{
    /**
     * Dashboard principal du jeune
     */
    public function index()
    {
        $user = auth()->user();

        // Verifier si l'onboarding est complete
        if (!$user->onboarding_completed) {
            return redirect()->route('jeune.onboarding');
        }

        // Stats du profil
        $stats = [
            'personality_completed' => $user->personalityTest && $user->personalityTest->completed_at,
            'documents_count' => $user->academicDocuments()->count(),
            'conversations_count' => $user->chatConversations()->count(),
            'messages_count' => $user->chatConversations()->withCount('messages')->get()->sum('messages_count'),
        ];

        // Mentors recommandes (basé sur les interets si disponibles)
        $recommendedMentors = MentorProfile::where('is_published', true)
            ->with('user')
            ->limit(4)
            ->get();

        return view('jeune.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recommendedMentors' => $recommendedMentors,
        ]);
    }

    /**
     * Page du test de personnalite
     */
    public function personalityTest()
    {
        $user = auth()->user();
        $test = $user->personalityTest;

        return view('jeune.personality', [
            'user' => $user,
            'test' => $test,
            'hasCompleted' => $test && $test->completed_at,
        ]);
    }

    /**
     * Page du chat IA
     */
    public function chat()
    {
        $user = auth()->user();
        $conversations = $user->chatConversations()
            ->with('messages')
            ->orderByDesc('updated_at')
            ->get();

        return view('jeune.chat', [
            'user' => $user,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Page des documents
     */
    public function documents()
    {
        $user = auth()->user();
        $documents = $user->academicDocuments()
            ->orderByDesc('created_at')
            ->get();

        return view('jeune.documents', [
            'user' => $user,
            'documents' => $documents,
        ]);
    }

    /**
     * Page des mentors
     */
    public function mentors(Request $request)
    {
        $query = MentorProfile::where('is_published', true)
            ->with(['user', 'roadmapSteps']);

        // Filtres
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', 'like', '%' . $request->country . '%');
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bio', 'like', '%' . $search . '%')
                    ->orWhere('current_position', 'like', '%' . $search . '%')
                    ->orWhere('current_company', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $mentors = $query->paginate(12);

        $specializations = MentorProfile::SPECIALIZATIONS;

        return view('jeune.mentors', [
            'mentors' => $mentors,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Page du profil
     */
    public function profile()
    {
        $user = auth()->user();

        return view('jeune.profile', [
            'user' => $user,
        ]);
    }
}
