<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\PersonalityTest;
use App\Services\BrillioIAService;
use App\Services\EstablishmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EstablishmentController extends Controller
{
    protected $iaService;

    protected $establishmentService;

    public function __construct(BrillioIAService $iaService, EstablishmentService $establishmentService)
    {
        $this->iaService = $iaService;
        $this->establishmentService = $establishmentService;
    }

    public function create()
    {
        $mbtiTypes = array_keys(PersonalityTest::PERSONALITY_TYPES);

        return view('admin.establishments.create', compact('mbtiTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->establishmentService::validationRules());

        $this->establishmentService->store($validated, $request);

        return redirect()->route('admin.establishments.index')->with('success', 'Établissement créé avec succès.');
    }

    public function edit(Establishment $establishment)
    {
        $mbtiTypes = array_keys(PersonalityTest::PERSONALITY_TYPES);

        return view('admin.establishments.edit', compact('establishment', 'mbtiTypes'));
    }

    public function update(Request $request, Establishment $establishment)
    {
        $validated = $request->validate($this->establishmentService::validationRules());

        $this->establishmentService->update($establishment, $validated, $request);

        return redirect()->route('admin.establishments.index')->with('success', 'Établissement mis à jour.');
    }

    public function destroy(Establishment $establishment)
    {
        if ($establishment->photo_path) {
            Storage::disk('public')->delete($establishment->photo_path);
        }
        $establishment->delete();

        return redirect()->route('admin.establishments.index')->with('success', 'Établissement supprimé.');
    }

    /**
     * AI Generation for establishments
     */
    public function autoGenerate(Request $request)
    {
        $existingNames = Establishment::pluck('name')->toArray();

        $suggestions = $this->iaService->generateEstablishments($existingNames);

        if (empty($suggestions)) {
            return back()->with('error', "Aucun nouvel établissement n'a pu être généré. Veuillez réessayer.");
        }

        $count = 0;
        foreach ($suggestions as $data) {
            Establishment::create([
                'name' => $data['name'],
                'type' => $data['type'] ?? 'university',
                'country' => $data['country'] ?? 'Bénin',
                'city' => $data['city'] ?? null,
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'website_url' => $data['website_url'] ?? null,
                'google_maps_url' => $data['google_maps_url'] ?? null,
                'tuition_min' => $data['tuition_min'] ?? null,
                'tuition_max' => $data['tuition_max'] ?? null,
                'sectors' => $data['sectors'] ?? [],
                'mbti_types' => $data['mbti_types'] ?? [],
                'social_links' => $data['social_links'] ?? [],
                'is_published' => false, // Review required by admin
            ]);
            $count++;
        }

        return back()->with('success', "$count nouveaux établissements originaux générés et enregistrés en brouillon.");
    }

    public function index()
    {
        $establishments = Establishment::withCount(['interests', 'clicks'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.establishments.index', compact('establishments'));
    }

    /**
     * Show interests for a specific establishment
     */
    public function interests(Establishment $establishment)
    {
        $interests = $establishment->interests()->with('user')->orderBy('created_at', 'desc')->paginate(20);

        $clicks = $establishment->clicks()
            ->select('user_id', \DB::raw('count(*) as clicks_count'), \DB::raw('max(created_at) as last_click_at'), \DB::raw('max(ip_address) as last_ip'), \DB::raw('max(user_agent) as last_agent'))
            ->with('user.personalityTest')
            ->groupBy('user_id')
            ->orderBy('last_click_at', 'desc')
            ->paginate(20);

        $clicksCount = $establishment->clicks()->count();

        return view('admin.establishments.interests', compact('establishment', 'interests', 'clicks', 'clicksCount'));
    }

    /**
     * Export interests to CSV
     */
    public function exportInterestsCsv(Establishment $establishment)
    {
        $interests = $establishment->interests()->with('user')->get();

        $filename = 'prospects_'.Str::slug($establishment->name).'_'.date('Y-m-d').'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Date', 'Utilisateur', 'Email', 'Téléphone', 'Type', 'Détails du formulaire'];

        $callback = function () use ($interests, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($interests as $interest) {
                $row['Date'] = $interest->created_at->format('d/m/Y H:i');
                $row['Utilisateur'] = $interest->user->name;
                $row['Email'] = $interest->user->email;
                $row['Téléphone'] = $interest->user->phone ?? 'Non renseigné';
                $row['Type'] = $interest->type === 'quick' ? 'Intérêt Rapide' : 'Formulaire Précis';
                $row['Détails'] = $interest->form_data ? json_encode($interest->form_data) : '-';

                fputcsv($file, array_values($row));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
