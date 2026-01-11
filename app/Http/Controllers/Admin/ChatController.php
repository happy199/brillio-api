<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ChatController extends Controller
{
    /**
     * Affiche la liste des conversations
     */
    public function index(Request $request)
    {
        $query = ChatConversation::with(['user'])
            ->withCount('messages');

        // Recherche par utilisateur ou contenu
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhereHas('messages', function ($msgQuery) use ($search) {
                    $msgQuery->where('content', 'like', "%{$search}%");
                });
            });
        }

        // Filtre par statut de support humain
        if ($request->filled('status')) {
            if ($request->status === 'needs_support') {
                $query->where('needs_human_support', true)
                      ->where('human_support_active', false);
            } elseif ($request->status === 'in_support') {
                $query->where('human_support_active', true);
            } elseif ($request->status === 'normal') {
                $query->where('needs_human_support', false)
                      ->where('human_support_active', false);
            }
        }

        // Priorité aux demandes de support
        $conversations = $query->orderByDesc('needs_human_support')
                              ->orderByDesc('human_support_active')
                              ->latest('updated_at')
                              ->paginate(20);

        // Statistiques
        $stats = [
            'total_conversations' => ChatConversation::count(),
            'total_messages' => ChatMessage::count(),
            'user_messages' => ChatMessage::where('role', 'user')->count(),
            'assistant_messages' => ChatMessage::where('role', 'assistant')->count(),
            'pending_support' => ChatConversation::where('needs_human_support', true)
                                                 ->where('human_support_active', false)
                                                 ->count(),
            'active_support' => ChatConversation::where('human_support_active', true)->count(),
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

    /**
     * L'admin prend en charge la conversation (active le mode conseiller)
     */
    public function takeOver(ChatConversation $conversation)
    {
        $conversation->update([
            'human_support_active' => true,
            'human_support_admin_id' => auth()->id(),
            'human_support_started_at' => now(),
        ]);

        return back()->with('success', 'Vous avez pris en charge cette conversation. Les messages de l\'utilisateur vous seront maintenant adressés.');
    }

    /**
     * L'admin envoie un message dans la conversation
     */
    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        // Créer le message du conseiller
        $message = $conversation->messages()->create([
            'role' => 'assistant', // On garde "assistant" pour l'affichage côté mobile
            'content' => $request->content,
            'is_from_human' => true, // Nouveau champ pour identifier que c'est un humain
            'admin_id' => auth()->id(),
        ]);

        // Mettre à jour la conversation
        $conversation->touch();

        return back()->with('success', 'Message envoyé avec succès.');
    }

    /**
     * L'admin termine la session de support
     */
    public function endSupport(ChatConversation $conversation)
    {
        $conversation->update([
            'needs_human_support' => false,
            'human_support_active' => false,
            'human_support_ended_at' => now(),
        ]);

        // Envoyer un message système pour informer l'utilisateur
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => "La session avec le conseiller est terminée. N'hésitez pas à continuer à poser vos questions, je suis là pour vous aider ! Si vous avez besoin de parler à nouveau à un conseiller humain, utilisez le bouton \"Parler à un conseiller\".",
            'is_from_human' => false,
            'is_system_message' => true,
        ]);

        return back()->with('success', 'Session de support terminée. L\'utilisateur peut à nouveau utiliser le chatbot IA.');
    }

    /**
     * Export de la conversation en PDF
     */
    public function exportPdf(ChatConversation $conversation)
    {
        $conversation->load(['user', 'messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        $pdf = Pdf::loadView('admin.exports.conversation', [
            'conversation' => $conversation,
            'generatedAt' => now(),
        ]);

        $filename = 'conversation-' . $conversation->id . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
