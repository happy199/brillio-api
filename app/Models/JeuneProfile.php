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
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'profile_views' => 'integer',
        'mentor_views' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
