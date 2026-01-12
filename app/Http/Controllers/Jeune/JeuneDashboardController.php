<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\MentorProfile;
use App\Models\PersonalityTest;
use Illuminate\Http\Request;

class JeuneDashboardController extends Controller
{
    /**
     * Dashboard principal du jeune
     */
    public function index()
    {
        $user = auth()->user();

        // Verifier si l'onboarding est complete
        if (!$user->onboarding_completed) {
            return redirect()->route('jeune.onboarding');
        }

        // Stats du profil
        $stats = [
            'personality_completed' => $user->personalityTest && $user->personalityTest->completed_at,
            'documents_count' => $user->academicDocuments()->count(),
            'conversations_count' => $user->chatConversations()->count(),
            'messages_count' => $user->chatConversations()->withCount('messages')->get()->sum('messages_count'),
        ];

        // Mentors recommandes (basé sur les interets si disponibles)
        $recommendedMentors = MentorProfile::where('is_published', true)
            ->with('user')
            ->limit(4)
            ->get();

        return view('jeune.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recommendedMentors' => $recommendedMentors,
        ]);
    }

    /**
     * Page du test de personnalite
     */
    public function personalityTest()
    {
        $user = auth()->user();
        $personalityTest = $user->personalityTest;

        return view('jeune.personality', [
            'user' => $user,
            'personalityTest' => $personalityTest,
        ]);
    }

    /**
     * Page du chat IA
     */
    public function chat()
    {
        $user = auth()->user();
        $conversations = $user->chatConversations()
            ->with('messages')
            ->orderByDesc('updated_at')
            ->get();

        return view('jeune.chat', [
            'user' => $user,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Page des documents
     */
    public function documents()
    {
        $user = auth()->user();
        $documents = $user->academicDocuments()
            ->orderByDesc('created_at')
            ->get();

        return view('jeune.documents', [
            'user' => $user,
            'documents' => $documents,
        ]);
    }

    /**
     * Page des mentors
     */
    public function mentors(Request $request)
    {
        $query = MentorProfile::where('is_published', true)
            ->with(['user', 'roadmapSteps']);

        // Filtres
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country', 'like', '%' . $request->country . '%');
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bio', 'like', '%' . $search . '%')
                    ->orWhere('current_position', 'like', '%' . $search . '%')
                    ->orWhere('current_company', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $mentors = $query->paginate(12);

        $specializations = MentorProfile::SPECIALIZATIONS;

        return view('jeune.mentors', [
            'mentors' => $mentors,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Page du profil
     */
    public function profile()
    {
        $user = auth()->user();

        return view('jeune.profile', [
            'user' => $user,
        ]);
    }

    /**
     * Mise a jour du profil
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ]);

        auth()->user()->update($validated);

        return back()->with('success', 'Profil mis a jour avec succes.');
    }

    /**
     * Detail d'un mentor
     */
    public function mentorShow(MentorProfile $mentor)
    {
        $mentor->load(['user', 'roadmapSteps']);

        // Mentors similaires (meme specialisation)
        $similarMentors = MentorProfile::where('is_published', true)
            ->where('id', '!=', $mentor->id)
            ->where('specialization', $mentor->specialization)
            ->with('user')
            ->limit(3)
            ->get();

        return view('jeune.mentor-show', [
            'mentor' => $mentor,
            'similarMentors' => $similarMentors,
        ]);
    }

    /**
     * Upload de document
     */
    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_type' => 'required|in:bulletin,diplome,attestation,autre',
            'school_year' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document');
        $path = $file->store('documents/' . auth()->id(), 'public');

        auth()->user()->academicDocuments()->create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'document_type' => $validated['document_type'],
            'school_year' => $validated['school_year'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Document ajoute avec succes.');
    }

    /**
     * Telecharger un document
     */
    public function downloadDocument($document)
    {
        $doc = auth()->user()->academicDocuments()->findOrFail($document);

        return response()->download(storage_path('app/public/' . $doc->file_path), $doc->original_name);
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument($document)
    {
        $doc = auth()->user()->academicDocuments()->findOrFail($document);
        \Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['success' => true]);
    }
}
