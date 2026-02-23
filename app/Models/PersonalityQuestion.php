<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalityQuestion extends Model
{
    protected $fillable = [
        'openmbti_id',
        'dimension',
        'left_trait_en',
        'left_trait_fr',
        'right_trait_en',
        'right_trait_fr',
    ];

    /**
     * Get formatted question for a specific locale
     */
    public function getFormattedQuestion(string $locale = 'fr'): array
    {
        $leftTrait = $locale === 'fr' ? $this->left_trait_fr : $this->left_trait_en;
        $rightTrait = $locale === 'fr' ? $this->right_trait_fr : $this->right_trait_en;

        return [
            'id' => $this->openmbti_id,
            'text' => "{$leftTrait} ou {$rightTrait} ?",
            'dimension' => $this->dimension,
            'left_trait' => $leftTrait,
            'right_trait' => $rightTrait,
        ];
    }

    /**
     * Get all questions formatted for a specific locale
     */
    public static function getAllFormatted(string $locale = 'fr'): array
    {
        return self::orderBy('openmbti_id')
            ->get()
            ->map(fn ($q) => $q->getFormattedQuestion($locale))
            ->toArray();
    }
}
