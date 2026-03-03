<?php

namespace App\Services;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $deepSeekService;

    public function __construct(DeepSeekService $deepSeekService)
    {
        $this->deepSeekService = $deepSeekService;
    }

    /**
     * Predire les mots cles a risque pour un binome mentor/jeune.
     */
    public function generateForbiddenKeywords(Mentorship $mentorship): array
    {
        $mentor = $mentorship->mentor;
        $mentee = $mentorship->mentee;

        $prompt = "Voici les profils d'un Mentor et d'un Jeune (Menté) qui vont commencer à échanger sur la plateforme Brillio.\n\n".
            "Mentor:\n".$this->formatUserProfile($mentor)."\n\n".
            "Jeune:\n".$this->formatUserProfile($mentee)."\n\n".
            "En te basant sur leurs profils, parcours, et centres d'intérêt, identifie 20 mots-clés ou sujets spécifiques (en français) ".
            "qui pourraient représenter un risque de dérapage, d'inapproprié ou de manque de professionnalisme dans LEUR contexte spécifique. ".
            "Inclus aussi des mots-clés généraux de sécurité (argent, cadeau, rencontre, numéro, etc.) s'ils te semblent pertinents pour ce binôme.\n".
            'Réponds UNIQUEMENT avec une liste JSON de chaînes de caractères. Exemple: ["mot1", "mot2"]';

        try {
            $response = $this->deepSeekService->analyzeText($prompt, 'Tu es un expert en modération de contenu et en psychologie sociale. Tu analyses les risques de communication entre mentors et jeunes.');
            $keywords = json_decode($this->deepSeekService->cleanJson($response), true);

            return is_array($keywords) ? $keywords : [];
        } catch (\Exception $e) {
            Log::error('AI Keyword Generation failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function formatUserProfile(User $user): string
    {
        $profile = "- Nom: {$user->name}\n";

        if ($user->jeuneProfile) {
            $profile .= "- Situation: {$user->jeuneProfile->current_situation}\n";
            $profile .= "- Objectif: {$user->jeuneProfile->professional_objective}\n";
        }

        if ($user->mentorProfile) {
            $profile .= "- Job: {$user->mentorProfile->job_title}\n";
            $profile .= "- Expérience: {$user->mentorProfile->experience_years} ans\n";
            $profile .= "- Bio: {$user->mentorProfile->bio}\n";
        }

        return $profile;
    }
}
