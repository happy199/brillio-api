<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\CvAnalysis;
use App\Services\CvAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OpportunityController extends Controller
{
    /**
     * Page d'accueil publique de la section Opportunités
     */
    public function index(Request $request)
    {
        $existingToken = session('pending_cv_token');
        $latestCv = null;

        if ($existingToken) {
            $latestCv = CvAnalysis::where('guest_token', $existingToken)->first();
        }

        return view('public.opportunities', [
            'latestCv' => $latestCv,
        ]);
    }

    /**
     * Analyse un CV téléversé par un visiteur (ou utilisateur connecté)
     */
    public function analyzeCv(Request $request, CvAnalysisService $cvService)
    {
        $validated = $request->validate([
            'cv_file' => ['required', 'file', 'mimes:pdf,docx,png,jpg,jpeg', 'max:10240'],
        ], [
            'cv_file.required' => 'Veuillez sélectionner un fichier CV à importer.',
            'cv_file.file' => 'Le document téléversé est invalide.',
            'cv_file.mimes' => 'Format de fichier non supporté. Formats acceptés : PDF, DOCX, JPG ou PNG.',
            'cv_file.max' => 'La taille du document ne peut pas dépasser 10 Mo.',
        ]);

        try {
            $user = auth()->check() ? auth()->user() : null;
            $cvAnalysis = $cvService->processAndAnalyze($validated['cv_file'], $user);

            // Mémorisation du token dans la session
            session(['pending_cv_token' => $cvAnalysis->guest_token]);

            return $this->buildAnalysisSuccessResponse($request, $cvAnalysis);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'analyse du CV : '.$e->getMessage());

            return $this->buildAnalysisErrorResponse($request);
        }
    }

    private function buildAnalysisSuccessResponse(Request $request, CvAnalysis $cvAnalysis)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'token' => $cvAnalysis->guest_token,
                'score' => $cvAnalysis->global_score,
                'status_label' => $cvAnalysis->status_label,
                'candidate_name' => $cvAnalysis->candidate_name,
                'candidate_title' => $cvAnalysis->candidate_title,
                'candidate_contact' => $cvAnalysis->candidate_contact,
                'parsed_content' => $cvAnalysis->parsed_content,
                'redirect_url' => route('public.opportunities.score', ['token' => $cvAnalysis->guest_token]),
            ]);
        }

        return redirect()->route('public.opportunities.score', ['token' => $cvAnalysis->guest_token]);
    }

    private function buildAnalysisErrorResponse(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'analyse de votre CV. Veuillez réessayer.',
            ], 500);
        }

        return back()->withErrors([
            'cv_file' => 'Une erreur est survenue lors de l\'analyse de votre CV. Veuillez réessayer.',
        ]);
    }

    /**
     * Affiche la page de score pour un visiteur invité
     */
    public function showScore(Request $request, string $token)
    {
        // Validation sécurisée du token
        if (! preg_match('/^[a-zA-Z0-9]{20,64}$/', $token)) {
            abort(404);
        }

        $cvAnalysis = CvAnalysis::where('guest_token', $token)->firstOrFail();

        // Si l'utilisateur est déjà connecté en tant que jeune, le rediriger directement vers son espace débloqué
        if (auth()->check() && auth()->user()->isJeune()) {
            if (! $cvAnalysis->user_id) {
                $cvAnalysis->update([
                    'user_id' => auth()->id(),
                    'is_claimed' => true,
                ]);
            }

            return redirect()->route('jeune.documents', ['tab' => 'cv']);
        }

        // Mémoriser le token en session au cas où il clique sur s'inscrire
        session(['pending_cv_token' => $token]);

        return view('public.opportunities', [
            'cvAnalysis' => $cvAnalysis,
            'isScoreView' => true,
        ]);
    }
}
