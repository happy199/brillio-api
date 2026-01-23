<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller pour la gestion des mentors dans le dashboard admin
 */
class MentorController extends Controller
{
    /**
     * Liste des spécialisations
     */
    protected array $specializations = [
        'tech' => 'Technologie',
        'business' => 'Business & Management',
        'health' => 'Santé',
        'education' => 'Éducation',
        'arts' => 'Arts & Culture',
        'engineering' => 'Ingénierie',
        'law' => 'Droit',
        'finance' => 'Finance',
        'marketing' => 'Marketing',
        'other' => 'Autre',
    ];

    /**
     * Liste tous les profils mentors
     */
    public function index(Request $request)
    {
        $query = MentorProfile::with(['user', 'roadmapSteps', 'specializationModel']);

        // Filtre par statut de publication
        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        // Filtre par spécialisation
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        // Recherche
        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $mentors = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistiques
        $stats = [
            'total' => MentorProfile::count(),
            'published' => MentorProfile::where('is_published', true)->count(),
            'draft' => MentorProfile::where('is_published', false)->count(),
            'total_steps' => RoadmapStep::count(),
        ];

        return view('admin.mentors.index', [
            'mentors' => $mentors,
            'stats' => $stats,
            'specializations' => $this->specializations,
        ]);
    }

    /**
     * Affiche le détail d'un profil mentor
     */
    public function show(MentorProfile $mentor)
    {
        $mentor->load(['user', 'roadmapSteps', 'specializationModel']);

        return view('admin.mentors.show', [
            'mentor' => $mentor,
            'specializations' => $this->specializations,
        ]);
    }

    /**
     * Toggle publication du profil mentor
     */
    public function togglePublish(MentorProfile $mentor)
    {
        $mentor->update([
            'is_published' => !$mentor->is_published,
        ]);

        return back()->with(
            'success',
            $mentor->is_published
            ? 'Profil mentor publié.'
            : 'Profil mentor dépublié.'
        );
    }

    /**
     * Approuve et publie un profil mentor
     */
    public function approve(MentorProfile $mentor)
    {
        $mentor->is_published = true;
        $mentor->is_validated = true;
        $mentor->validated_at = now();
        $mentor->save();

        return back()->with('success', "Le profil de {$mentor->user->name} a été validé et publié");
    }

    /**
     * Rejette (dépublie) un profil mentor
     */
    public function reject(MentorProfile $mentor)
    {
        $mentor->is_published = false;
        $mentor->save();

        return back()->with('warning', "Le profil de {$mentor->user->name} a été retiré");
    }

    /**
     * Télécharge le fichier profil LinkedIn du mentor
     */
    public function downloadLinkeInProfile(MentorProfile $mentor)
    {
        if (!$mentor->linkedin_pdf_path) {
            return back()->with('error', 'Aucun fichier profil associé.');
        }

        // Le fichier est stocké sur le disque 'local' (storage/app/linkedin-pdfs)
        if (!Storage::disk('local')->exists($mentor->linkedin_pdf_path)) {
            return back()->with('error', 'Fichier introuvable sur le serveur.');
        }

        return Storage::disk('local')->download(
            $mentor->linkedin_pdf_path,
            $mentor->linkedin_pdf_original_name ?? 'profil-linkedin.pdf'
        );
    }
}
