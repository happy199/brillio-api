<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
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
