<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\Request;

/**
 * Controller pour la supervision des conversations chatbot
 */
class ChatLogController extends Controller
{
    /**
     * Liste toutes les conversations
     */
    public function index(Request $request)
    {
        $query = ChatConversation::with(['user', 'messages']);

        $validated = $request->validate([
            'user_id' => 'nullable|integer',
            'search' => 'nullable|string|max:255',
        ]);

        // Filtre par utilisateur
        if ($userId = $validated['user_id'] ?? null) {
            $query->where('user_id', $userId);
        }

        // Recherche dans les messages
        if ($search = $validated['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('messages', function ($mq) use ($search) {
                        $mq->where('content', 'like', "%{$search}%");
                    });
            });
        }

        $conversations = $query->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        return view('admin.chat.index', compact('conversations'));
    }

    /**
     * Affiche le détail d'une conversation
     */
    public function show(ChatConversation $conversation)
    {
        $conversation->load(['user', 'messages']);

        return view('admin.chat.show', compact('conversation'));
    }
}
