<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicDocument;
use App\Models\CvAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller pour la gestion des documents académiques dans le dashboard admin
 */
class DocumentController extends Controller
{
    /**
     * Liste tous les documents
     */
    public function index(Request $request)
    {
        $query = AcademicDocument::with('user');

        $validated = $request->validate([
            'type' => 'nullable|string|in:'.implode(',', array_keys(AcademicDocument::DOCUMENT_TYPES)),
            'user_id' => 'nullable|integer',
            'search' => 'nullable|string|max:255',
        ]);

        // Filtre par type
        if ($type = $validated['type'] ?? null) {
            $query->where('document_type', $type);
        }

        // Filtre par utilisateur
        if ($userId = $validated['user_id'] ?? null) {
            $query->where('user_id', $userId);
        }

        // Recherche par nom de fichier
        if ($search = $validated['search'] ?? null) {
            $query->where('file_name', 'like', "%{$search}%");
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(25);

        $documentTypes = AcademicDocument::DOCUMENT_TYPES;

        return view('admin.documents.index', compact('documents', 'documentTypes'));
    }

    /**
     * Visualise / prévisualise un document en ligne dans le navigateur
     */
    public function preview(AcademicDocument $document)
    {
        $fileInfo = $this->resolveFile($document->file_path);

        if (! $fileInfo) {
            return response(
                '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><style>body{font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f9fafb;color:#374151;}.card{background:#fff;border:1px solid #fca5a5;border-radius:1rem;padding:2.5rem;text-align:center;box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);max-w:480px;}.icon{color:#ef4444;width:48px;height:48px;margin:0 auto 1rem;}h3{margin:0 0 0.5rem;color:#991b1b;font-size:1.125rem;}p{margin:0;font-size:0.875rem;color:#6b7280;word-break:break-all;}</style></head><body><div class="card"><svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg><h3>Fichier introuvable sur le serveur</h3><p>Le fichier physique ('.$document->file_name.') n\'a pas été trouvé sur le stockage serveur.</p></div></body></html>',
                404,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }

        $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'txt' => 'text/plain',
            default => $document->mime_type ?? 'application/octet-stream',
        };

        if ($fileInfo['type'] === 'disk') {
            return Storage::disk($fileInfo['disk'])->response(
                $document->file_path,
                $document->file_name,
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="'.addslashes($document->file_name).'"',
                ]
            );
        }

        return response()->file($fileInfo['full_path'], [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.addslashes($document->file_name).'"',
        ]);
    }

    /**
     * Télécharge un document
     */
    public function download(AcademicDocument $document)
    {
        $fileInfo = $this->resolveFile($document->file_path);

        if (! $fileInfo) {
            return back()->with('error', 'Fichier introuvable sur le serveur');
        }

        if ($fileInfo['type'] === 'disk') {
            return Storage::disk($fileInfo['disk'])->download(
                $document->file_path,
                $document->file_name
            );
        }

        return response()->download($fileInfo['full_path'], $document->file_name);
    }

    /**
     * Résout l'emplacement physique du fichier sur les différents disques ou répertoires
     */
    private function resolveFile(string $path): ?array
    {
        if (Storage::disk('local')->exists($path)) {
            return ['type' => 'disk', 'disk' => 'local'];
        }

        if (Storage::disk('public')->exists($path)) {
            return ['type' => 'disk', 'disk' => 'public'];
        }

        $candidates = [
            storage_path('app/'.$path),
            storage_path('app/private/'.$path),
            storage_path('app/public/'.$path),
            public_path($path),
            public_path('storage/'.$path),
        ];

        foreach ($candidates as $fullPath) {
            if (file_exists($fullPath)) {
                return ['type' => 'path', 'full_path' => $fullPath];
            }
        }

        return null;
    }

    /**
     * Supprime un document
     */
    public function destroy(AcademicDocument $document)
    {
        $document->delete();

        return back()->with('success', 'Document supprimé avec succès');
    }

    /**
     * Retourne les données complètes de l'analyse IA associée à un document CV
     */
    public function cvAnalysis(AcademicDocument $document)
    {
        $cvAnalysis = $this->findAssociatedCvAnalysis($document);

        if (! $cvAnalysis) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune analyse IA trouvée pour ce document.',
            ], 404);
        }

        $norm = $cvAnalysis->normalized_cv_data;

        return response()->json([
            'success' => true,
            'analysis' => [
                'id' => $cvAnalysis->id,
                'candidate_name' => $cvAnalysis->candidate_name,
                'candidate_title' => $cvAnalysis->candidate_title,
                'contact' => $cvAnalysis->candidate_contact ?? [],
                'global_score' => $cvAnalysis->global_score,
                'criteria_scores' => $cvAnalysis->criteria_scores ?? [],
                'strengths' => $cvAnalysis->strengths ?? [],
                'weaknesses' => $cvAnalysis->weaknesses ?? [],
                'recommendations' => $cvAnalysis->recommendations ?? [],
                'summary' => $cvAnalysis->summary,
                'profil' => $cvAnalysis->parsed_content['profil'] ?? '',
                'experiences' => $norm['experiences'] ?? [],
                'formation' => $norm['formation'] ?? [],
                'competences' => $norm['competences'] ?? [],
                'certifications' => $norm['certifications'] ?? [],
                'langues' => $norm['langues'] ?? [],
                'created_at' => $cvAnalysis->created_at->format('d/m/Y à H:i'),
            ],
        ]);
    }

    /**
     * Exporte les données des candidats au format CSV/Excel pour l'équipe commerciale
     */
    public function exportCandidates(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_score' => 'nullable|integer|min:0|max:100',
            'keyword' => 'nullable|string|max:100',
            'fields' => 'nullable|array',
            'fields.*' => 'string|in:candidate_name,email,phone,location,candidate_title,skills,global_score,registered_user,analyzed_at',
        ]);

        $selectedFields = $validated['fields'] ?? [
            'candidate_name', 'email', 'phone', 'location', 'candidate_title', 'skills', 'global_score', 'analyzed_at',
        ];

        $query = CvAnalysis::with('user');

        if (! empty($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }
        if (! empty($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }
        if (isset($validated['min_score']) && $validated['min_score'] !== '') {
            $query->where('global_score', '>=', (int) $validated['min_score']);
        }
        if (! empty($validated['keyword'])) {
            $kw = $validated['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('candidate_name', 'like', "%{$kw}%")
                    ->orWhere('candidate_title', 'like', "%{$kw}%")
                    ->orWhere('summary', 'like', "%{$kw}%");
            });
        }

        $analyses = $query->orderBy('created_at', 'desc')->get();
        $fileName = 'Candidats_Brillio_'.now()->format('Ymd_His').'.csv';

        return response()->stream(function () use ($analyses, $selectedFields) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            $labels = [
                'candidate_name' => 'Nom et Prénom',
                'email' => 'Email',
                'phone' => 'Téléphone',
                'location' => 'Ville / Pays',
                'candidate_title' => 'Intitulé du Poste',
                'skills' => 'Compétences Détectées',
                'global_score' => 'Score ATS (/100)',
                'registered_user' => 'Inscrit sur Brillio',
                'analyzed_at' => 'Date Analyse',
            ];

            $headerRow = [];
            foreach ($selectedFields as $f) {
                $headerRow[] = $labels[$f] ?? $f;
            }
            fputcsv($output, $headerRow, ';');

            foreach ($analyses as $cv) {
                $row = $this->buildCandidateExportRow($cv, $selectedFields);
                fputcsv($output, $row, ';');
            }

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Retrouve l'analyse CV associée à un document académique
     */
    private function findAssociatedCvAnalysis(AcademicDocument $document): ?CvAnalysis
    {
        if ($document->document_type !== 'cv') {
            return null;
        }

        return CvAnalysis::where('file_path', $document->file_path)->first()
            ?? CvAnalysis::where('user_id', $document->user_id)->where('original_filename', $document->file_name)->latest()->first()
            ?? CvAnalysis::where('user_id', $document->user_id)->latest()->first();
    }

    /**
     * Construit une ligne de données d'export pour un candidat
     */
    private function buildCandidateExportRow(CvAnalysis $cv, array $fields): array
    {
        $contact = (array) ($cv->candidate_contact ?? []);
        $user = $cv->user;
        $norm = $cv->normalized_cv_data;

        $email = $contact['email'] ?? ($user?->email ?? '');
        $phone = $contact['phone'] ?? ($user?->phone_number ?? ($user?->phone ?? ''));
        $location = $contact['location'] ?? ($user?->city ?? ($user?->country ?? ''));
        $skills = implode(', ', (array) ($norm['competences'] ?? []));

        $values = [
            'candidate_name' => $cv->candidate_name ?: ($user?->name ?? 'Non spécifié'),
            'email' => $email ?: 'Non spécifié',
            'phone' => $phone ?: 'Non spécifié',
            'location' => $location ?: 'Non spécifié',
            'candidate_title' => $cv->candidate_title ?: 'Non spécifié',
            'skills' => $skills ?: 'Non spécifié',
            'global_score' => (string) $cv->global_score,
            'registered_user' => $user ? 'Oui ('.$user->name.')' : 'Non (Invité)',
            'analyzed_at' => $cv->created_at->format('d/m/Y H:i'),
        ];

        $result = [];
        foreach ($fields as $f) {
            $result[] = $values[$f] ?? '';
        }

        return $result;
    }
}
