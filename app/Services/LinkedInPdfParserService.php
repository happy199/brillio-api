<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class LinkedInPdfParserService
{
    private DeepSeekService $deepSeekService;

    public function __construct(DeepSeekService $deepSeekService)
    {
        $this->deepSeekService = $deepSeekService;
    }

    /**
     * Parse un PDF LinkedIn et extrait les informations via IA
     */
    public function parsePdf($pdfPath)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);

            // Extraire le texte brut
            $text = $pdf->getText();

            Log::info('📄 PDF Text extracted', [
                'length' => strlen($text),
                'first_1000_chars' => substr($text, 0, 1000)
            ]);

            // Prompt Système pour l'IA - Concatenated String to avoid Heredoc issues
            $systemPrompt = "Tu es un expert en extraction de données de CV (Resume Parser).\n" .
                "Ta mission est d'analyser le texte brut d'un profil LinkedIn PDF et d'en extraire les informations structurées au format JSON STRICT.\n\n" .
                "RÈGLES IMPORTANTES :\n" .
                "- Ne jamais inventer d'information. Si une info est manquante, mets null ou une chaine vide.\n" .
                "- Répont UNIQUEMENT avec le bloc JSON, sans texte avant ou après, sans balises markdown (```json), sans commentaires et sans virgules traînantes.\n" .
                "- Le format de sortie doit respecter exactement la structure demandée.\n\n" .
                "STRUCTURE JSON ATTENDUE :\n" .
                '{"name": "Nom complet", "headline": "Titre du profil ou poste actuel", "contact": {"email": "email found or empty", "phone": "phone found or empty", "linkedin": "linkedin url or empty", "website": "website url or empty"}, "summary": "Bio", "skills": ["Compétence 1"], "experience": [{"title": "Poste", "company": "Entreprise", "description": "Tâches", "start_date": "YYYY-MM-DD", "end_date": "YYYY-MM-DD", "duration_years": 0, "duration_months": 0}], "education": [{"school": "Ecole", "degree": "Diplôme", "year_start": 0, "year_end": 0}]}';

            $prompt = "Voici le contenu brut du PDF LinkedIn. Extrais les données en JSON :\n\n" . substr($text, 0, 60000); // Limite étendue pour gérer les longs profils (10+ pages)

            // Appel à l'IA
            Log::info('🤖 Sending PDF text to DeepSeek AI...');
            $jsonResponse = $this->deepSeekService->analyzeText($prompt, $systemPrompt);

            // Nettoyage et Décodage
            $cleanJson = $this->deepSeekService->cleanJson($jsonResponse);
            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('❌ JSON Parsing Error', [
                    'error' => json_last_error_msg(),
                    'clean_json' => $cleanJson,
                    'raw_response' => $jsonResponse
                ]);
                throw new \Exception('Erreur lors du décodage de la réponse IA.');
            }

            // Logger quelques stats
            Log::info('✅ AI Parsing Successful', [
                'name' => $data['name'] ?? 'Unknown',
                'experience_count' => count($data['experience'] ?? []),
                'skills_count' => count($data['skills'] ?? [])
            ]);

            return $data;

        }
        catch (\Exception $e) {
            Log::error('❌ PDF AI Parsing Critical Error', ['error' => $e->getMessage()]);
            throw new \Exception('Impossible d\'analyser le PDF avec l\'IA : ' . $e->getMessage());
        }
    }
}