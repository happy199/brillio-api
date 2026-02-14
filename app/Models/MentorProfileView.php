<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorProfileView extends Model
{
    protected $fillable = ['user_id', 'mentor_id', 'viewed_at'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class , 'mentor_id');
    }
}