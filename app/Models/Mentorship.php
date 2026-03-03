<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mentorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'mentor_id',
        'mentee_id',
        'status', // pending, accepted, refused, disconnected
        'request_message',
        'refusal_reason',
        'diction_reason', // reason for disconnection
        'custom_forbidden_keywords',
        'reported_at',
        'reported_by_id',
        'report_reason',
    ];

    protected $casts = [
        'custom_forbidden_keywords' => 'array',
        'reported_at' => 'datetime',
    ];

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function isReported(): bool
    {
        return ! empty($this->reported_at);
    }

    public function getTranslatedStatusAttribute()
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'accepted' => 'Accepté',
            'refused' => 'Refusé',
            'disconnected' => 'Terminé', // ou "Déconnecté" selon la terminologie préférée
            default => $this->status,
        };
    }
}
