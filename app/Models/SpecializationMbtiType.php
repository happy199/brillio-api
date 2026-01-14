<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecializationMbtiType extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialization_id',
        'mbti_type_code',
    ];

    /**
     * Relation vers la spécialisation
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }
}
