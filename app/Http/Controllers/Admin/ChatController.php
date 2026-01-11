<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Affiche la liste des conversations
     */
    public function index(Request $request)
    {
        $query = ChatConversation::with(['user'])
            ->withCount('messages');

        // Recherche par utilisateur
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $conversations = $query->latest('updated_at')->paginate(20);

        // Statistiques
        $stats = [
            'total_conversations' => ChatConversation::count(),
            'total_messages' => ChatMessage::count(),
            'user_messages' => ChatMessage::where('role', 'user')->count(),
            'assistant_messages' => ChatMessage::where('role', 'assistant')->count(),
        ];

        return view('admin.chat.index', [
            'conversations' => $conversations,
            'stats' => $stats,
        ]);
    }

    /**
     * Affiche une conversation
     */
    public function show(ChatConversation $conversation)
    {
        $conversation->load(['user.personalityTest', 'messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return view('admin.chat.show', [
            'conversation' => $conversation,
        ]);
    }
}
