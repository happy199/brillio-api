<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Establishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'photo_path',
        'country',
        'city',
        'description',
        'email',
        'phone',
        'address',
        'website_url',
        'social_links',
        'mbti_types',
        'sectors',
        'tuition_min',
        'tuition_max',
        'presentation_videos',
        'brochures',
        'has_precise_form',
        'precise_form_config',
        'is_published',
        'organization_id',
        'google_maps_url',
        'gallery',
    ];

    protected $casts = [
        'social_links' => 'array',
        'mbti_types' => 'array',
        'sectors' => 'array',
        'presentation_videos' => 'array',
        'brochures' => 'array',
        'precise_form_config' => 'array',
        'has_precise_form' => 'boolean',
        'is_published' => 'boolean',
        'tuition_min' => 'decimal:2',
        'tuition_max' => 'decimal:2',
        'gallery' => 'array',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($establishment) {
            if (empty($establishment->slug)) {
                $establishment->slug = Str::slug($establishment->name).'-'.Str::random(5);
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function interests()
    {
        return $this->hasMany(EstablishmentInterest::class);
    }
}
