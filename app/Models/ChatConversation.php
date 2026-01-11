<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle ChatConversation - Conversations avec le chatbot IA
 */
class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
    ];

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers les messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /**
     * Retourne les N derniers messages pour le contexte IA
     */
    public function getLastMessages(int $count = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->messages()
            ->orderBy('created_at', 'desc')
            ->take($count)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Génère automatiquement un titre basé sur le premier message
     */
    public function generateTitle(): void
    {
        $firstMessage = $this->messages()->where('role', 'user')->first();

        if ($firstMessage) {
            $this->title = \Str::limit($firstMessage->content, 50);
            $this->save();
        }
    }
}
