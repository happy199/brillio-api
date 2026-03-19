<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JeuneProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'portfolio_url',
        'cv_path',
        'is_public',
        'public_slug',
        'profile_views',
        'mentor_views',
        'has_unlocked_session_history',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'profile_views' => 'integer',
        'mentor_views' => 'integer',
        'has_unlocked_session_history' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
