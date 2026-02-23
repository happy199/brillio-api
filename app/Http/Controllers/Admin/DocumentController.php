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

        // Filtre par type
        if ($type = $request->get('type')) {
            $query->where('document_type', $type);
        }

        // Filtre par utilisateur
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        // Recherche par nom de fichier
        if ($search = $request->get('search')) {
            $query->where('file_name', 'like', "%{$search}%");
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(25);

        $documentTypes = AcademicDocument::DOCUMENT_TYPES;

        return view('admin.documents.index', compact('documents', 'documentTypes'));
    }

    /**
     * Télécharge un document
     */
    public function download(AcademicDocument $document)
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            return back()->with('error', 'Fichier introuvable');
        }

        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name
        );
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
