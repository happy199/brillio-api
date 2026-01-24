<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'content',
        'type',
        'file_path',
        'preview_image_path',
        'price',
        'is_premium',
        'is_published',
        'is_validated',
        'validated_at',
        'metadata',
        'mbti_types',
        'tags',
    ];

    protected $casts = [
        'metadata' => 'array',
        'mbti_types' => 'array',
        'tags' => 'array',
        'is_premium' => 'boolean',
        'is_published' => 'boolean',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'price' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes potentiels
    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('is_validated', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }
}
