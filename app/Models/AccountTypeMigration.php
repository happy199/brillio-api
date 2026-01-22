<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTypeMigration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'old_type',
        'new_type',
        'token',
        'oauth_data',
        'expires_at',
    ];

    protected $casts = [
        'oauth_data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifie si le token est expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
