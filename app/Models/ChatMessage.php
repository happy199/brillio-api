<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ChatMessage - Messages individuels des conversations
 */
class ChatMessage extends Model
{
    use HasFactory;

    /**
     * Rôles possibles pour les messages
     */
    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'is_from_human',
        'is_system_message',
        'admin_id',
    ];

    protected $casts = [
        'is_from_human' => 'boolean',
        'is_system_message' => 'boolean',
    ];

    /**
     * Relation vers la conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Relation vers l'admin qui a envoyé le message (si conseiller humain)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Vérifie si le message vient de l'utilisateur
     */
    public function isFromUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Vérifie si le message vient de l'assistant IA
     */
    public function isFromAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    /**
     * Vérifie si le message vient d'un conseiller humain
     */
    public function isFromHumanAdvisor(): bool
    {
        return $this->is_from_human && $this->role === self::ROLE_ASSISTANT;
    }

    /**
     * Vérifie si c'est un message système
     */
    public function isSystemMessage(): bool
    {
        return $this->is_system_message ?? false;
    }
}
