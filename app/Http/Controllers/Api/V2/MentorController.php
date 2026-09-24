<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V1\MentorController as V1MentorController;
use App\Http\Requests\Mentor\CreateProfileRequest;
use App\Http\Requests\Mentor\CreateRoadmapStepRequest;
use App\Http\Requests\Mentor\UpdateRoadmapStepRequest;
use App\Models\MentorProfile;
use App\Services\LinkedInPdfParserService;
use App\Traits\FormatsUrls;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * Controller pour la recherche et consultation des mentors via API
 */
class MentorController extends V1MentorController
{
    use FormatsUrls;
    /**
     * @OA\Get(
     * path="/api/v2/mentors",
     * summary="Liste des mentors publiés",
     * tags={"Mentors"},
     *
     * @OA\Parameter(name="specialization", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="country", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     * @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *
     * @OA\Response(response= 200, description="Liste des mentors"),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return parent::index($request);
    }

    /**
     * @OA\Get(
     * path="/api/v2/mentors/{id}",
     * summary= "Détail d'un mentor",
     * tags={"Mentors"},
     *
     * @OA\Parameter(name="id", in="path", required= true, @OA\Schema(type="integer")),
     *
     * @OA\Response(response= 200, description="Détail du mentor"),
     * @OA\Response(response= 404, description="Mentor non trouvé"),
     * )
     */
    public function show(int $id): JsonResponse
    {
        return parent::show($id);
    }

    /**
     * @OA\Post(
     * path="/api/v2/mentor/profile",
     * summary="Crée ou met à jour le profil mentor",
     * tags={"Mentors"},
     *
     * @OA\Response(response= 200, description="Profil mis à jour"),
     * )
     */
    public function createOrUpdateProfile(CreateProfileRequest $request): JsonResponse
    {
        return parent::createOrUpdateProfile($request);
    }

    /**
     * Récupère le profil mentor de l'utilisateur connecté
     */
    public function myProfile(Request $request): JsonResponse
    {
        return parent::myProfile($request);
    }

    /**
     * Ajoute une étape au parcours
     */
    public function addRoadmapStep(CreateRoadmapStepRequest $request): JsonResponse
    {
        return parent::addRoadmapStep($request);
    }

    /**
     * Met à jour une étape du parcours
     */
    public function updateRoadmapStep(UpdateRoadmapStepRequest $request, int $stepId): JsonResponse
    {
        return parent::updateRoadmapStep($request, $stepId);
    }

    /**
     * Supprime une étape du parcours
     */
    public function deleteRoadmapStep(Request $request, int $stepId): JsonResponse
    {
        return parent::deleteRoadmapStep($request, $stepId);
    }

    /**
     * Réorganise les étapes du parcours
     */
    public function reorderSteps(Request $request): JsonResponse
    {
        return parent::reorderSteps($request);
    }

    /**
     * Publie ou dépublie le profil mentor
     */
    public function publish(Request $request): JsonResponse
    {
        return parent::publish($request);
    }

    /**
     * @OA\Get(
     * path="/api/v2/specializations",
     * summary="Liste des spécialisations disponibles",
     * tags={"Mentors"},
     *
     * @OA\Response(response= 200, description="Liste des spécialisations"),
     * )
     */
    public function specializations(): JsonResponse
    {
        return parent::specializations();
    }

    /**
     * @OA\Post(
     *     path="/api/v2/mentor/profile/import-linkedin",
     *     summary="Importe les données du profil mentor depuis un export PDF LinkedIn",
     *     tags={"Mentors"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"pdf"},
     *                 @OA\Property(property="pdf", type="string", format="binary", description="Export PDF LinkedIn (max 5MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profil importé avec succès"),
     *     @OA\Response(response=403, description="Accès réservé aux mentors"),
     *     @OA\Response(response=422, description="Erreur de validation ou profil non correspondant"),
     *     @OA\Response(response=500, description="Erreur de traitement du PDF")
     * )
     */
    public function importLinkedIn(Request $request, LinkedInPdfParserService $parserService): JsonResponse
    {
        $user = $request->user();

        if (! $user->isMentor()) {
            return $this->forbidden('Seuls les mentors peuvent importer un profil LinkedIn');
        }

        $profile = $user->mentorProfile;
        if (! $profile) {
            $profile = MentorProfile::create(['user_id' => $user->id]);
        }

        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:5120',
        ]);

        try {
            // Stocker temporairement le PDF
            $pdfPath = $request->file('pdf')->store('temp-linkedin-pdfs', 'local');
            $fullPath = Storage::disk('local')->path($pdfPath);

            // Parser le PDF
            $profileData = $parserService->parsePdf($fullPath);
            $profileData = $parserService->sanitizeUtf8($profileData);

            Log::info('LinkedIn PDF parsed via API', ['data' => $profileData]);

            // Vérifier ownership
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

            if (! $isOwner) {
                Storage::disk('local')->delete($pdfPath);

                $errorMessage = 'Ce profil LinkedIn ne semble pas vous appartenir.';
                if (isset($mismatchContext['name'])) {
                    $errorMessage .= ' Le nom dans le PDF ('.$profileData['name'].') ne correspond pas à votre nom ('.$user->name.').';
                }

                return $this->error($errorMessage, 422);
            }

            // Stocker le PDF définitivement
            $finalPdfPath = $request->file('pdf')->store('linkedin-pdfs', 'local');
            $originalName = $request->file('pdf')->getClientOriginalName();

            // Supprimer le fichier temporaire
            Storage::disk('local')->delete($pdfPath);

            // Supprimer anciennes étapes si réimport
            if ($profile->linkedin_import_count > 0) {
                $profile->roadmapSteps()->delete();
            }

            // Calculer années d'exp
            $yearsOfExperience = $this->calculateYearsOfExperience($profileData['experience'] ?? []);
            $latestExperience = ! empty($profileData['experience']) ? $profileData['experience'][0] : null;

            // Extraire ville et pays
            $extractedCity = null;
            $extractedCountry = null;
            $locationString = $profileData['location'] ?? $profileData['contact']['location'] ?? null;

            if (! empty($locationString)) {
                $locationParts = array_map('trim', explode(',', $locationString));
                if (count($locationParts) >= 2) {
                    $extractedCity = $locationParts[0];
                    $extractedCountry = trim(end($locationParts));
                } elseif (count($locationParts) === 1) {
                    $extractedCity = $locationParts[0];
                }
            }

            // Sauvegarder les données du profil non-destructivement
            $profile->update([
                'linkedin_raw_data' => $profileData,
                'linkedin_imported_at' => now(),
                'linkedin_pdf_path' => $finalPdfPath,
                'linkedin_pdf_original_name' => $originalName,
                'linkedin_import_count' => $profile->linkedin_import_count + 1,
                'current_position' => empty($profile->current_position) ? ($latestExperience['title'] ?? null) : $profile->current_position,
                'current_company' => empty($profile->current_company) ? ($latestExperience['company'] ?? null) : $profile->current_company,
                'bio' => empty($profile->bio) ? ((! empty($profileData['summary']) ? $profileData['summary'] : null) ?? $profileData['headline'] ?? null) : $profile->bio,
                'skills' => (empty($profile->skills) && ! empty($profileData['skills'])) ? $profileData['skills'] : $profile->skills,
                'linkedin_url' => empty($profile->linkedin_url) ? $this->formatUrl($profileData['contact']['linkedin'] ?? null) : $profile->linkedin_url,
                'website_url' => empty($profile->website_url) ? $this->formatUrl($profileData['contact']['website'] ?? null) : $profile->website_url,
                'years_of_experience' => ($profile->years_of_experience > 0) ? $profile->years_of_experience : ($yearsOfExperience > 0 ? $yearsOfExperience : $profile->years_of_experience),
            ]);

            // User updates
            $userUpdates = [];
            if (! empty($extractedCity) && empty($user->city)) {
                $userUpdates['city'] = $extractedCity;
            }
            if (! empty($extractedCountry) && empty($user->country)) {
                $userUpdates['country'] = $extractedCountry;
            }
            if (! empty($profileData['contact']['phone']) && empty($user->phone)) {
                $userUpdates['phone'] = $profileData['contact']['phone'];
            }
            if (! empty($userUpdates)) {
                $user->update($userUpdates);
            }

            // Importer les expériences
            $stepPosition = 0;
            if (! empty($profileData['experience'])) {
                foreach ($profileData['experience'] as $exp) {
                    $startDate = null;
                    $endDate = null;

                    if (! empty($exp['start_date'])) {
                        $startDate = strlen($exp['start_date']) === 4 ? $exp['start_date'].'-01-01' : $exp['start_date'];
                    }

                    if (array_key_exists('end_date', $exp)) {
                        if (! empty($exp['end_date'])) {
                            $endDate = strlen($exp['end_date']) === 4 ? $exp['end_date'].'-12-31' : $exp['end_date'];
                        } else {
                            $endDate = null;
                        }
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

            // Importer les formations
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

            // Warnings, suggestions
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

            return $this->success([
                'profile' => [
                    'name' => $profileData['name'] ?? '',
                    'headline' => $profileData['headline'] ?? '',
                    'experience_count' => count($profileData['experience'] ?? []),
                    'skills_count' => count($profileData['skills'] ?? []),
                    'import_count' => $profile->linkedin_import_count,
                    'years_of_experience' => $yearsOfExperience,
                ],
                'warnings' => $warnings,
                'missing_fields' => $missingFields,
                'suggestions' => ! empty($suggestions) ? [
                    'message' => 'Certaines données sont manquantes. Complétez votre profil :',
                    'actions' => $suggestions,
                ] : null,
            ], 'Profil LinkedIn importé avec succès !');

        } catch (\Throwable $e) {
            Log::error('LinkedIn PDF import API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Calculer les années d'expérience totales depuis les périodes (fusion d'intervalles)
     */
    private function calculateYearsOfExperience(array $experiences): int
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

        usort($intervals, function ($a, $b) {
            return $a['start']->timestamp <=> $b['start']->timestamp;
        });

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
