<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSuppression extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'reason',
        'source',
        'created_by',
    ];

    /**
     * Relation vers l'utilisateur admin ayant ajouté l'exclusion manuellement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
