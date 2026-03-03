<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use Illuminate\Http\Request;

class MentorshipChatController extends Controller
{
    /**
     * Affiche la liste des conversations de mentorat
     */
    public function index(Request $request)
    {
        $query = Mentorship::with(['mentor', 'mentee', 'reporter'])
            ->withCount(['messages' => function ($q) {
            $q->where('is_flagged', true);
        }])
            ->with(['messages' => function ($q) {
            $q->latest()->limit(1);
        }]);

        // Filtrer par signalement (automatique ou manuel)
        if ($request->has('flagged')) {
            $query->where(function ($q) {
                $q->whereHas('messages', function ($sq) {
                        $sq->where('is_flagged', true);
                    }
                    )->orWhereNotNull('reported_at');
                });
        }

        // Recherche par utilisateur
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('mentor', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    }
                    )->orWhereHas('mentee', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    }
                    );
                });
        }

        $mentorships = $query->latest('updated_at')->paginate(20);

        return view('admin.mentorship-chat.index', compact('mentorships'));
    }

    /**
     * Voir le détail d'une conversation de mentorat
     */
    public function show(Mentorship $mentorship)
    {
        $mentorship->load(['mentor', 'mentee', 'messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        return view('admin.mentorship-chat.show', compact('mentorship'));
    }
}