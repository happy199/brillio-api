<?php

namespace App\Console\Commands;

use App\Models\PersonalityQuestion;
use App\Services\DeepSeekService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncPersonalityQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'personality:sync-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les questions de personnalité depuis OpenMBTI et les traduit en français';

    /**
     * Execute the console command.
     */
    public function handle(DeepSeekService $deepSeekService): int
    {
        $this->info('Récupération des questions depuis OpenMBTI...');

        try {
            // 1. Récupérer les questions depuis OpenMBTI
            $response = Http::timeout(30)->get('https://openmbti.org/api/questions');

            if (! $response->successful()) {
                $this->error('Erreur lors de la récupération des questions depuis OpenMBTI');
                Log::error('OpenMBTI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return 1;
            }

            $data = $response->json();
            $questions = $data['questions'] ?? [];

            if (empty($questions)) {
                $this->error('Aucune question reçue de l\'API');

                return 1;
            }

            $this->info(count($questions).' questions récupérées');

            // 2. Traduire toutes les questions en une seule fois
            $this->info('Traduction des questions avec DeepSeek...');

            $translationPrompt = "Traduis les questions de test de personnalité MBTI suivantes de l'anglais vers le français. Retourne UNIQUEMENT un tableau JSON valide avec cette structure exacte, sans texte supplémentaire :
[
  {\"id\": 1, \"left_fr\": \"traduction\", \"right_fr\": \"traduction\"},
  {\"id\": 2, \"left_fr\": \"traduction\", \"right_fr\": \"traduction\"},
  ...
]

Questions à traduire :\n";

            foreach ($questions as $q) {
                $translationPrompt .= "ID {$q['id']}: Left=\"{$q['leftTrait']}\", Right=\"{$q['rightTrait']}\"\n";
            }

            $translatedJson = $deepSeekService->translate($translationPrompt);

            // Nettoyer la réponse
            $translatedJson = trim($translatedJson);
            $translatedJson = preg_replace('/^```json\s*/', '', $translatedJson);
            $translatedJson = preg_replace('/\s*```$/', '', $translatedJson);

            $translations = json_decode($translatedJson, true);

            if (! $translations || ! is_array($translations)) {
                $this->warn('La traduction automatique a échoué, utilisation des traductions par défaut');
                $translations = null;
            }

            // 3. Mettre à jour ou créer les questions
            $this->info('Mise à jour de la base de données...');
            $bar = $this->output->createProgressBar(count($questions));

            foreach ($questions as $question) {
                $translation = $translations ? collect($translations)->firstWhere('id', $question['id']) : null;

                PersonalityQuestion::updateOrCreate(
                    ['openmbti_id' => $question['id']],
                    [
                        'dimension' => $question['dimension'],
                        'left_trait_en' => $question['leftTrait'],
                        'left_trait_fr' => $translation['left_fr'] ?? $question['leftTrait'],
                        'right_trait_en' => $question['rightTrait'],
                        'right_trait_fr' => $translation['right_fr'] ?? $question['rightTrait'],
                    ]
                );

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $this->info('✅ Synchronisation terminée avec succès !');
            Log::info('Personality questions synced successfully', ['count' => count($questions)]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Erreur lors de la synchronisation : '.$e->getMessage());
            Log::error('Personality questions sync error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
