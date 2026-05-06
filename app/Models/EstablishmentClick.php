<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstablishmentClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'establishment_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relation vers l'utilisateur (le jeune qui a cliqué)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers l'établissement cliqué
     */
    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }
}
