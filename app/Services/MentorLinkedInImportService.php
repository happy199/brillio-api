<?php

namespace App\Services;

use App\Models\MentorProfile;
use App\Models\User;
use App\Traits\FormatsUrls;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MentorLinkedInImportService
{
    use FormatsUrls;

    public function __construct(
        protected LinkedInPdfParserService $parserService
    ) {}

    /**
     * Importe un fichier PDF exporté de LinkedIn dans le profil et parcours du mentor.
     */
    public function import(UploadedFile $pdfFile, User $user): array
    {
        $profile = $user->mentorProfile;
        if (! $profile) {
            $profile = MentorProfile::create(['user_id' => $user->id]);
        }

        // 1. Stocker temporairement le PDF pour parsing
        $pdfPath = $pdfFile->store('temp-linkedin-pdfs', 'local');
        $fullPath = Storage::disk('local')->path($pdfPath);

        try {
            // 2. Parser le PDF et nettoyer l'encodage
            $profileData = $this->parserService->parsePdf($fullPath);
            $profileData = $this->parserService->sanitizeUtf8($profileData);

            Log::info('LinkedIn PDF parsed successfully', ['user_id' => $user->id, 'data' => $profileData]);

            // 3. Vérifier que le PDF appartient bien au mentor connecté
            $ownership = $this->verifyOwnership($profileData, $user);
            if (! $ownership['is_owner']) {
                Storage::disk('local')->delete($pdfPath);

                $errorMessage = 'Ce profil LinkedIn ne semble pas vous appartenir.';
                if (isset($ownership['context']['name'])) {
                    $errorMessage .= ' Le nom dans le PDF ('.$profileData['name'].') ne correspond pas à votre nom ('.$user->name.').';
                }

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'code' => 422,
                    'context' => $ownership['context'],
                ];
            }

            // 4. Stocker le PDF de façon permanente
            $finalPdfPath = $pdfFile->store('linkedin-pdfs', 'local');
            $originalName = $pdfFile->getClientOriginalName();

            Storage::disk('local')->delete($pdfPath);

            // 5. Réinitialiser les étapes de roadmap précédentes si réimport
            if ($profile->linkedin_import_count > 0) {
                $profile->roadmapSteps()->delete();
            }

            // 6. Calcul des années d'expérience et extraction de la localisation
            $yearsOfExperience = $this->calculateYearsOfExperience($profileData['experience'] ?? []);
            $latestExperience = ! empty($profileData['experience']) ? $profileData['experience'][0] : null;
            [$extractedCity, $extractedCountry] = $this->extractLocation($profileData);

            // 7. Mise à jour non-destructive du profil mentor
            $profile->update([
                'linkedin_raw_data' => $profileData,
                'linkedin_imported_at' => now(),
                'linkedin_pdf_path' => $finalPdfPath,
                'linkedin_pdf_original_name' => $originalName,
                'linkedin_import_count' => $profile->linkedin_import_count + 1,
                'current_position' => empty($profile->current_position)
                    ? ($latestExperience['title'] ?? null)
                    : $profile->current_position,
                'current_company' => empty($profile->current_company)
                    ? ($latestExperience['company'] ?? null)
                    : $profile->current_company,
                'bio' => empty($profile->bio)
                    ? ((! empty($profileData['summary']) ? $profileData['summary'] : null) ?? $profileData['headline'] ?? null)
                    : $profile->bio,
                'skills' => (empty($profile->skills) && ! empty($profileData['skills']))
                    ? $profileData['skills']
                    : $profile->skills,
                'linkedin_url' => empty($profile->linkedin_url)
                    ? $this->formatUrl($profileData['contact']['linkedin'] ?? null)
                    : $profile->linkedin_url,
                'website_url' => empty($profile->website_url)
                    ? $this->formatUrl($profileData['contact']['website'] ?? null)
                    : $profile->website_url,
                'years_of_experience' => ($profile->years_of_experience > 0)
                    ? $profile->years_of_experience
                    : ($yearsOfExperience > 0 ? $yearsOfExperience : $profile->years_of_experience),
            ]);

            // 8. Remplir les informations utilisateur si manquantes
            $this->updateUserIfEmpty($user, $extractedCity, $extractedCountry, $profileData['contact']['phone'] ?? null);

            // 9. Créer les étapes du parcours (expériences & formations)
            $this->createRoadmapSteps($profile, $profileData);

            // 10. Construire les recommandations et avertissements
            $feedback = $this->buildFeedback($profileData, $yearsOfExperience);

            return [
                'success' => true,
                'message' => 'Profil LinkedIn importé avec succès !',
                'data' => [
                    'profile' => [
                        'name' => $profileData['name'] ?? '',
                        'headline' => $profileData['headline'] ?? '',
                        'experience_count' => count($profileData['experience'] ?? []),
                        'skills_count' => count($profileData['skills'] ?? []),
                        'import_count' => $profile->linkedin_import_count,
                        'years_of_experience' => $yearsOfExperience,
                    ],
                    'name' => $profileData['name'] ?? '',
                    'headline' => $profileData['headline'] ?? '',
                    'experience_count' => count($profileData['experience'] ?? []),
                    'skills_count' => count($profileData['skills'] ?? []),
                    'import_count' => $profile->linkedin_import_count,
                    'years_of_experience' => $yearsOfExperience,
                    'warnings' => $feedback['warnings'],
                    'missing_fields' => $feedback['missing_fields'],
                    'suggestions' => ! empty($feedback['suggestions']) ? [
                        'message' => 'Certaines données sont manquantes. Complétez votre profil :',
                        'actions' => $feedback['suggestions'],
                    ] : null,
                ],
                'warnings' => $feedback['warnings'],
                'missing_fields' => $feedback['missing_fields'],
                'suggestions' => ! empty($feedback['suggestions']) ? [
                    'message' => 'Certaines données sont manquantes. Complétez votre profil :',
                    'actions' => $feedback['suggestions'],
                ] : null,
            ];

        } catch (\Throwable $e) {
            Storage::disk('local')->delete($pdfPath);

            Log::error('Mentor LinkedIn import service error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Vérifier l'appartenance du profil LinkedIn (email puis nom).
     */
    protected function verifyOwnership(array $profileData, User $user): array
    {
        $isOwner = false;
        $mismatchContext = [];

        if (! empty($profileData['contact']['email'])) {
            $pdfEmail = strtolower(trim($profileData['contact']['email']));
            $userEmail = strtolower(trim($user->email));

            if ($pdfEmail === $userEmail) {
                $isOwner = true;
            } else {
                $mismatchContext['email'] = ['pdf' => $pdfEmail, 'user' => $userEmail];
            }
        }

        if (! $isOwner && ! empty($profileData['name'])) {
            $pdfName = strtolower(trim($profileData['name']));
            $userName = strtolower(trim($user->name));

            if ($pdfName === $userName || str_contains($pdfName, $userName) || str_contains($userName, $pdfName)) {
                $isOwner = true;
            } else {
                $mismatchContext['name'] = ['pdf' => $pdfName, 'user' => $userName];
            }
        }

        return ['is_owner' => $isOwner, 'context' => $mismatchContext];
    }

    /**
     * Extraire la ville et le pays depuis la chaîne de localisation.
     */
    protected function extractLocation(array $profileData): array
    {
        $city = null;
        $country = null;
        $locationString = $profileData['location'] ?? $profileData['contact']['location'] ?? null;

        if (! empty($locationString)) {
            $parts = array_map('trim', explode(',', $locationString));
            if (count($parts) >= 2) {
                $city = $parts[0];
                $country = trim(end($parts));
            } elseif (count($parts) === 1) {
                $city = $parts[0];
            }
        }

        return [$city, $country];
    }

    /**
     * Mettre à jour la localisation et le téléphone de l'utilisateur s'ils sont vides.
     */
    protected function updateUserIfEmpty(User $user, ?string $city, ?string $country, ?string $phone): void
    {
        $updates = [];
        if (! empty($city) && empty($user->city)) {
            $updates['city'] = $city;
        }
        if (! empty($country) && empty($user->country)) {
            $updates['country'] = $country;
        }
        if (! empty($phone) && empty($user->phone)) {
            $updates['phone'] = $phone;
        }

        if (! empty($updates)) {
            $user->update($updates);
        }
    }

    /**
     * Créer les étapes de roadmap (expériences professionnelles et formations).
     */
    protected function createRoadmapSteps(MentorProfile $profile, array $profileData): void
    {
        $stepPosition = 0;

        if (! empty($profileData['experience'])) {
            foreach ($profileData['experience'] as $exp) {
                $startDate = null;
                $endDate = null;

                if (! empty($exp['start_date'])) {
                    $startDate = strlen($exp['start_date']) === 4 ? $exp['start_date'].'-01-01' : $exp['start_date'];
                }

                if (array_key_exists('end_date', $exp)) {
                    $endDate = ! empty($exp['end_date'])
                        ? (strlen($exp['end_date']) === 4 ? $exp['end_date'].'-12-31' : $exp['end_date'])
                        : null;
                } else {
                    $currentYear = date('Y');
                    $durationYears = $exp['duration_years'] ?? 0;
                    if ($durationYears > 0) {
                        $endDate = $currentYear.'-12-31';
                        $startDate = ($currentYear - $durationYears).'-01-01';
                    }
                }

                $profile->roadmapSteps()->create([
                    'step_type' => 'work',
                    'title' => $exp['title'] ?? 'Sans titre',
                    'institution_company' => $exp['company'] ?? null,
                    'description' => trim($exp['description'] ?? ''),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'position' => $stepPosition++,
                ]);
            }
        }

        if (! empty($profileData['education'])) {
            foreach ($profileData['education'] as $edu) {
                $profile->roadmapSteps()->create([
                    'step_type' => 'education',
                    'title' => $edu['degree'] ?? 'Formation',
                    'institution_company' => $edu['school'] ?? null,
                    'description' => 'Formation académique',
                    'start_date' => ! empty($edu['year_start']) ? $edu['year_start'].'-01-01' : null,
                    'end_date' => ! empty($edu['year_end']) ? $edu['year_end'].'-12-31' : null,
                    'position' => $stepPosition++,
                ]);
            }
        }
    }

    /**
     * Construire les avertissements et suggestions d'actions post-import.
     */
    protected function buildFeedback(array $profileData, int $yearsOfExperience): array
    {
        $warnings = [];
        $missingFields = [];
        $suggestions = [];

        if (empty($profileData['summary'])) {
            $missingFields[] = 'bio';
            $warnings[] = 'Aucun résumé trouvé dans le PDF';
            $suggestions[] = 'Ajoutez une bio sur votre profil';
        }

        if (empty($profileData['skills']) || count($profileData['skills']) === 0) {
            $missingFields[] = 'compétences';
            $warnings[] = 'Aucune compétence trouvée dans le PDF';
            $suggestions[] = 'Ajoutez vos compétences sur votre profil';
        }

        if (empty($profileData['contact']['website'])) {
            $missingFields[] = 'site web';
            $suggestions[] = 'Ajoutez votre site web sur votre profil';
        }

        if ($yearsOfExperience === 0) {
            $warnings[] = 'Impossible de calculer les années d\'expérience';
            $suggestions[] = 'Vérifiez vos années d\'expérience sur votre profil';
        }

        if (! empty($profileData['is_fallback'])) {
            $warnings[] = 'L\'IA a rencontré une difficulté technique. Un parseur simplifié a été utilisé.';
        }

        return [
            'warnings' => $warnings,
            'missing_fields' => $missingFields,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Calculer les années d'expérience cumulées avec fusion d'intervalles de dates.
     */
    public function calculateYearsOfExperience(array $experiences): int
    {
        if (empty($experiences)) {
            return 0;
        }

        $intervals = [];
        foreach ($experiences as $exp) {
            $startStr = $exp['start_date'] ?? null;
            $endStr = $exp['end_date'] ?? null;

            if (! $startStr) {
                continue;
            }

            try {
                $start = Carbon::parse($startStr)->startOfDay();
                $end = $endStr ? Carbon::parse($endStr)->endOfDay() : now()->endOfDay();

                if ($end->isBefore($start)) {
                    continue;
                }

                $intervals[] = ['start' => $start, 'end' => $end];
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($intervals)) {
            return 0;
        }

        usort($intervals, fn ($a, $b) => $a['start']->timestamp <=> $b['start']->timestamp);

        $merged = [];
        $current = $intervals[0];

        for ($i = 1; $i < count($intervals); $i++) {
            if ($intervals[$i]['start']->isBefore($current['end']) || $intervals[$i]['start']->equalTo($current['end'])) {
                if ($intervals[$i]['end']->isAfter($current['end'])) {
                    $current['end'] = $intervals[$i]['end'];
                }
            } else {
                $merged[] = $current;
                $current = $intervals[$i];
            }
        }
        $merged[] = $current;

        $totalDays = 0;
        foreach ($merged as $interval) {
            $totalDays += $interval['start']->diffInDays($interval['end']);
        }

        $years = $totalDays / 365.25;

        return (int) round($years);
    }
}
