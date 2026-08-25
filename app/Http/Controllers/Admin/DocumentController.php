<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicDocument;
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
}
