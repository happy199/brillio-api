<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Services\BrillioIAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CareerController extends Controller
{
    protected $brillioAIService;

    public function __construct(BrillioIAService $brillioAIService)
    {
        $this->brillioAIService = $brillioAIService;
    }

    public function index(Request $request)
    {
        $query = Career::query();

        // Recherche par titre
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        // Filtre par Impact IA
        if ($request->filled('impact')) {
            $query->where('ai_impact_level', $request->impact);
        }

        // Filtre par Niveau de Demande
        if ($request->filled('demand')) {
            $query->where('demand_level', $request->demand);
        }

        // Filtre par MBTI
        if ($request->filled('mbti')) {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                    ->from('career_mbti')
                    ->whereRaw('career_mbti.career_id = careers.id')
                    ->where('mbti_type', strtoupper($request->mbti));
            });
        }

        // Filtre par Secteur
        if ($request->filled('sector')) {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                    ->from('career_sector')
                    ->whereRaw('career_sector.career_id = careers.id')
                    ->where('sector_code', $request->sector);
            });
        }

        $careers = $query->orderBy('title')->paginate(20);

        // Liste des niveaux de demande uniques pour le filtre
        $demandLevels = Career::whereNotNull('demand_level')
            ->where('demand_level', '!=', '')
            ->distinct()
            ->pluck('demand_level');

        return view('admin.careers.index', compact('careers', 'demandLevels'));
    }

    public function create()
    {
        return view('admin.careers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:careers,title',
            'description' => 'required|string',
            'future_prospects' => 'nullable|string',
            'african_context' => 'nullable|string',
            'ai_impact_level' => 'nullable|string|max:255',
            'ai_impact_explanation' => 'nullable|string',
            'demand_level' => 'nullable|string|max:255',
            'mbti_types' => 'nullable|array',
            'sectors' => 'nullable|array',
        ]);

        $career = Career::create($request->only([
            'title', 'description', 'future_prospects', 'african_context', 'ai_impact_level', 'ai_impact_explanation', 'demand_level',
        ]));

        if ($request->has('mbti_types')) {
            foreach ($request->mbti_types as $mbtiType) {
                DB::table('career_mbti')->insert([
                    'career_id' => $career->id,
                    'mbti_type' => strtoupper($mbtiType),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($request->has('sectors')) {
            foreach ($request->sectors as $sectorCode) {
                DB::table('career_sector')->insert([
                    'career_id' => $career->id,
                    'sector_code' => $sectorCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.careers.index')->with('success', 'Métier ajouté avec succès.');
    }

    public function edit(Career $career)
    {
        $mbtiTypes = DB::table('career_mbti')->where('career_id', $career->id)->pluck('mbti_type')->toArray();
        $sectors = DB::table('career_sector')->where('career_id', $career->id)->pluck('sector_code')->toArray();

        return view('admin.careers.edit', compact('career', 'mbtiTypes', 'sectors'));
    }

    public function update(Request $request, Career $career)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:careers,title,'.$career->id,
            'description' => 'required|string',
            'future_prospects' => 'nullable|string',
            'african_context' => 'nullable|string',
            'ai_impact_level' => 'nullable|string|max:255',
            'ai_impact_explanation' => 'nullable|string',
            'demand_level' => 'nullable|string|max:255',
            'mbti_types' => 'nullable|array',
            'sectors' => 'nullable|array',
        ]);

        $career->update($request->only([
            'title', 'description', 'future_prospects', 'african_context', 'ai_impact_level', 'ai_impact_explanation', 'demand_level',
        ]));

        DB::table('career_mbti')->where('career_id', $career->id)->delete();
        if ($request->has('mbti_types')) {
            foreach ($request->mbti_types as $mbtiType) {
                DB::table('career_mbti')->insert([
                    'career_id' => $career->id,
                    'mbti_type' => strtoupper($mbtiType),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('career_sector')->where('career_id', $career->id)->delete();
        if ($request->has('sectors')) {
            foreach ($request->sectors as $sectorCode) {
                DB::table('career_sector')->insert([
                    'career_id' => $career->id,
                    'sector_code' => $sectorCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.careers.index')->with('success', 'Métier mis à jour avec succès.');
    }

    public function destroy(Career $career)
    {
        $career->delete();

        return redirect()->route('admin.careers.index')->with('success', 'Métier supprimé avec succès.');
    }

    /**
     * Identifie les métiers incomplets
     */
    public function bulkAudit()
    {
        $incompleteCareers = Career::whereNull('description')
            ->orWhereNull('future_prospects')
            ->orWhereNull('african_context')
            ->orWhereNull('ai_impact_level')
            ->orWhereNull('ai_impact_explanation')
            ->orWhereNull('demand_level')
            ->orWhere('description', '')
            ->orWhere('future_prospects', '')
            ->orWhere('african_context', '')
            ->orWhere('ai_impact_explanation', '')
            ->orWhere('demand_level', '')
            ->get();

        return response()->json([
            'count' => $incompleteCareers->count(),
            'ids' => $incompleteCareers->pluck('id'),
        ]);
    }

    /**
     * Traite un seul métier (audit AI)
     */
    public function processSingleAudit(Career $career)
    {
        $systemPrompt = "Tu es un expert en ressources humaines et en orientation professionnelle en Afrique.\n".
            "Ta mission est de compléter une fiche métier existante.\n".
            "Réponds UNIQUEMENT sous forme d'un objet JSON.\n\n".
            "FORMAT JSON ATTENDU :\n".
            "{\n".
            "  'description': '...', \n".
            "  'african_context': '...', \n".
            "  'future_prospects': '...', \n".
            "  'demand_level': '...', \n".
            "  'ai_impact_level': 'low|medium|high', \n".
            "  'ai_impact_explanation': '...'\n".
            '}';

        $prompt = "Complète les informations pour le métier : {$career->title}";

        try {
            $response = $this->brillioAIService->analyzeText($prompt, $systemPrompt);
            $json = $this->brillioAIService->cleanJson($response);
            $data = json_decode($json, true);

            if (! $data) {
                return response()->json(['error' => 'Format IA invalide'], 500);
            }

            // On ne remplit QUE ce qui est vide
            $updates = [];
            if (empty($career->description)) {
                $updates['description'] = $data['description'] ?? null;
            }
            if (empty($career->african_context)) {
                $updates['african_context'] = $data['african_context'] ?? null;
            }
            if (empty($career->future_prospects)) {
                $updates['future_prospects'] = $data['future_prospects'] ?? null;
            }
            if (empty($career->demand_level)) {
                $updates['demand_level'] = $data['demand_level'] ?? null;
            }
            if (empty($career->ai_impact_level)) {
                $rawImpact = strtolower($data['ai_impact_level'] ?? 'low');
                // Normalize typos (AI can be creative)
                if (str_contains($rawImpact, 'low')) {
                    $updates['ai_impact_level'] = 'low';
                } elseif (str_contains($rawImpact, 'medi')) {
                    $updates['ai_impact_level'] = 'medium';
                } elseif (str_contains($rawImpact, 'high')) {
                    $updates['ai_impact_level'] = 'high';
                } else {
                    $updates['ai_impact_level'] = 'low';
                } // Fallback
            }
            if (empty($career->ai_impact_explanation)) {
                $updates['ai_impact_explanation'] = $data['ai_impact_explanation'] ?? null;
            }

            if (! empty($updates)) {
                $career->update($updates);
            }

            return response()->json(['success' => true, 'title' => $career->title]);
        } catch (\Exception $e) {
            Log::error('Bulk Audit Error for job '.$career->id.': '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
