<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'organization_id',
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
        'admin_feedback',
        'unpublished_at',
        'metadata',
        'mbti_types',
        'tags',
        'targeting',
    ];

    protected $casts = [
        'metadata' => 'array',
        'mbti_types' => 'array',
        'tags' => 'array',
        'targeting' => 'array',
        'is_premium' => 'boolean',
        'is_published' => 'boolean',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'unpublished_at' => 'datetime',
        'price' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isInternalTo(?Organization $organization): bool
    {
        return $organization !== null && (int) $this->organization_id === (int) $organization->id;
    }

    public function isExternal(): bool
    {
        return is_null($this->organization_id);
    }

    // Scopes potentiels
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function isDepublished(): bool
    {
        return ! $this->is_published && $this->admin_feedback !== null;
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function views()
    {
        return $this->hasMany(ResourceView::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function purchases()
    {
        return $this->morphMany(Purchase::class, 'item');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::saving(function ($resource) {
            if (empty($resource->slug)) {
                $resource->slug = Str::slug($resource->title);
            }
        });
    }

    /**
     * Accessor pour l'URL de la miniature
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->preview_image_path) {
            return asset('storage/'.$this->preview_image_path);
        }

        return null;
    }

    /**
     * Accessor pour les tags sous forme de tableau (alias pour 'tags')
     */
    public function getTagsArrayAttribute(): array
    {
        return $this->tags ?? [];
    }
}
