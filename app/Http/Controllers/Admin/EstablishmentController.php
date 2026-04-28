<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentInterest;
use App\Models\PersonalityTest;
use App\Services\BrillioIAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EstablishmentController extends Controller
{
    protected $iaService;

    public function __construct(BrillioIAService $iaService)
    {
        $this->iaService = $iaService;
    }

    public function index()
    {
        $establishments = Establishment::withCount('interests')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.establishments.index', compact('establishments'));
    }

    public function create()
    {
        $mbtiTypes = array_keys(PersonalityTest::PERSONALITY_TYPES);
        return view('admin.establishments.create', compact('mbtiTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'country' => 'required|string',
            'city' => 'nullable|string',
            'description' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website_url' => 'nullable|url',
            'google_maps_url' => 'nullable|url',
            'mbti_types' => 'nullable|array',
            'sectors' => 'nullable|array',
            'tuition_min' => 'nullable|numeric',
            'tuition_max' => 'nullable|numeric',
            'photo' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:5120',
            'brochures.*' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'presentation_videos' => 'nullable|array|max:3',
            'presentation_videos.*' => 'nullable|url',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('establishments/photos', 'public');
        }

        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = $file->store('establishments/gallery', 'public');
            }
            $validated['gallery'] = $galleryPaths;
        }

        if ($request->hasFile('brochures')) {
            $brochurePaths = [];
            foreach ($request->file('brochures') as $file) {
                $brochurePaths[] = $file->store('establishments/brochures', 'public');
            }
            $validated['brochures'] = $brochurePaths;
        }

        if (isset($validated['presentation_videos'])) {
            $validated['presentation_videos'] = array_values(array_filter($validated['presentation_videos']));
        }

        $validated['is_published'] = $request->has('is_published');
        $validated['has_precise_form'] = $request->has('has_precise_form');
        $validated['precise_form_config'] = $request->has('has_precise_form') 
            ? array_values($request->input('precise_form_config', [])) 
            : null;
        
        // Handle Social Links from separate inputs
        $validated['social_links'] = [
            'linkedin' => $request->input('linkedin'),
            'facebook' => $request->input('facebook'),
            'instagram' => $request->input('instagram'),
        ];

        Establishment::create($validated);

        return redirect()->route('admin.establishments.index')->with('success', 'Établissement créé avec succès.');
    }

    public function edit(Establishment $establishment)
    {
        $mbtiTypes = array_keys(PersonalityTest::PERSONALITY_TYPES);
        return view('admin.establishments.edit', compact('establishment', 'mbtiTypes'));
    }

    public function update(Request $request, Establishment $establishment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'country' => 'required|string',
            'city' => 'nullable|string',
            'description' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website_url' => 'nullable|url',
            'google_maps_url' => 'nullable|url',
            'mbti_types' => 'nullable|array',
            'sectors' => 'nullable|array',
            'tuition_min' => 'nullable|numeric',
            'tuition_max' => 'nullable|numeric',
            'photo' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:5120',
            'brochures.*' => 'nullable|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'presentation_videos' => 'nullable|array|max:3',
            'presentation_videos.*' => 'nullable|url',
        ]);

        if ($request->hasFile('photo')) {
            if ($establishment->photo_path) {
                Storage::disk('public')->delete($establishment->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('establishments/photos', 'public');
        }

        $existingGallery = $establishment->gallery ?? [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $existingGallery[] = $file->store('establishments/gallery', 'public');
            }
        }
        $validated['gallery'] = $existingGallery;

        $existingBrochures = $establishment->brochures ?? [];
        if ($request->hasFile('brochures')) {
            foreach ($request->file('brochures') as $index => $file) {
                if (isset($existingBrochures[$index])) {
                    Storage::disk('public')->delete($existingBrochures[$index]);
                }
                $existingBrochures[$index] = $file->store('establishments/brochures', 'public');
            }
        }
        $validated['brochures'] = $existingBrochures;

        if ($request->has('presentation_videos')) {
            $validated['presentation_videos'] = array_values(array_filter($request->input('presentation_videos')));
        }

        $validated['is_published'] = $request->has('is_published');
        $validated['has_precise_form'] = $request->has('has_precise_form');
        $validated['precise_form_config'] = $request->has('has_precise_form') 
            ? array_values($request->input('precise_form_config', [])) 
            : null;
        
        $validated['social_links'] = [
            'linkedin' => $request->input('linkedin'),
            'facebook' => $request->input('facebook'),
            'instagram' => $request->input('instagram'),
        ];

        $establishment->update($validated);

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

    /**
     * Show interests for a specific establishment
     */
    public function interests(Establishment $establishment)
    {
        $interests = $establishment->interests()->with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.establishments.interests', compact('establishment', 'interests'));
    }

    /**
     * Export interests to CSV
     */
    public function exportInterestsCsv(Establishment $establishment)
    {
        $interests = $establishment->interests()->with('user')->get();
        
        $filename = "prospects_" . Str::slug($establishment->name) . "_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Utilisateur', 'Email', 'Téléphone', 'Type', 'Détails du formulaire'];

        $callback = function() use($interests, $columns) {
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
