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
        'needs_human_support',
        'human_support_active',
        'human_support_admin_id',
        'human_support_started_at',
        'human_support_ended_at',
    ];

    protected $casts = [
        'needs_human_support' => 'boolean',
        'human_support_active' => 'boolean',
        'human_support_started_at' => 'datetime',
        'human_support_ended_at' => 'datetime',
    ];

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers l'admin qui gère le support humain
     */
    public function supportAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'human_support_admin_id');
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

    /**
     * Vérifie si le support humain est actuellement actif
     */
    public function isHumanSupportActive(): bool
    {
        return $this->human_support_active;
    }

    /**
     * Vérifie si la conversation nécessite une attention humaine
     */
    public function needsAttention(): bool
    {
        return $this->needs_human_support && !$this->human_support_active;
    }

    /**
     * Demande un support humain
     */
    public function requestHumanSupport(): void
    {
        $this->update([
            'needs_human_support' => true,
        ]);
    }
}
