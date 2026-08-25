<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvisorVideoCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'counselor_id',
        'initiated_by',
        'status',
        'credits_cost',
        'meeting_id',
        'transcription_raw',
        'ai_summary',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'transcription_raw' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'credits_cost' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted' || $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_acceptance';
    }

    public function isRefused(): bool
    {
        return $this->status === 'refused';
    }
}
