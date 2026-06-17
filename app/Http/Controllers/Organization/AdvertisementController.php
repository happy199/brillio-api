<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Traits\HasWebpUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    use HasWebpUpload;

    /**
     * Display a listing of advertisements proposed by this organization.
     */
    public function index()
    {
        $organizationId = auth()->user()->organization_id;

        $advertisements = Advertisement::where('organization_id', $organizationId)
            ->orderByDesc('id')
            ->get();

        return view('organization.advertisements.index', compact('advertisements'));
    }

    /**
     * Show the form for proposing a new advertisement.
     */
    public function create()
    {
        return view('organization.advertisements.create');
    }

    /**
     * Store a newly proposed advertisement in storage.
     */
    public function store(Request $request)
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE) {
            return back()->withInput()->withErrors([
                'image' => 'Le fichier image est trop volumineux. La configuration actuelle de PHP sur votre serveur (MAMP) limite les téléchargements à '.ini_get('upload_max_filesize').'. Veuillez utiliser une image plus petite ou augmenter cette limite dans la configuration de votre serveur (php.ini).',
            ]);
        }

        $request->validate([
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'image' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120', // Max 5MB
        ]);

        $organizationId = auth()->user()->organization_id;

        if ($request->hasFile('image')) {
            $imagePath = $this->uploadAndConvertToWebp($request->file('image'), 'advertisements');

            if ($imagePath) {
                Advertisement::create([
                    'title' => $request->title,
                    'image_path' => $imagePath,
                    'link_url' => $request->link_url,
                    'status' => Advertisement::STATUS_PENDING,
                    'organization_id' => $organizationId,
                    'created_by' => auth()->id(),
                ]);

                return redirect()->route('organization.advertisements.index')
                    ->with('success', 'Votre proposition de publicité a été soumise avec succès ! Elle sera visible sur la page publique après validation par un administrateur.');
            }
        }

        return back()->with('error', "Une erreur est survenue lors de l'envoi du visuel.");
    }

    /**
     * Show the form for editing the specified advertisement.
     */
    public function edit(Advertisement $advertisement)
    {
        $organizationId = auth()->user()->organization_id;
        abort_unless($advertisement->organization_id === $organizationId, 403);

        return view('organization.advertisements.edit', compact('advertisement'));
    }

    /**
     * Update the specified advertisement in storage.
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        $organizationId = auth()->user()->organization_id;
        abort_unless($advertisement->organization_id === $organizationId, 403);

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE) {
            return back()->withInput()->withErrors([
                'image' => 'Le fichier image est trop volumineux. La configuration actuelle de PHP sur votre serveur (MAMP) limite les téléchargements à '.ini_get('upload_max_filesize').'. Veuillez utiliser une image plus petite ou augmenter cette limite dans la configuration de votre serveur (php.ini).',
            ]);
        }

        $request->validate([
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $data = [
            'title' => $request->title,
            'link_url' => $request->link_url,
        ];

        if ($request->hasFile('image')) {
            $imagePath = $this->uploadAndConvertToWebp($request->file('image'), 'advertisements');

            if ($imagePath) {
                // Delete old image if it exists
                if ($advertisement->image_path) {
                    Storage::disk('public')->delete($advertisement->image_path);
                }
                $data['image_path'] = $imagePath;
            }
        }

        $advertisement->update($data);

        return redirect()->route('organization.advertisements.index')
            ->with('success', 'Votre publicité a été mise à jour avec succès.');
    }

    /**
     * Remove the specified advertisement from storage.
     */
    public function destroy(Advertisement $advertisement)
    {
        $organizationId = auth()->user()->organization_id;

        // Ensure ownership
        abort_unless($advertisement->organization_id === $organizationId, 403);

        // Delete visual file
        if ($advertisement->image_path) {
            Storage::disk('public')->delete($advertisement->image_path);
        }

        $advertisement->delete();

        return redirect()->route('organization.advertisements.index')
            ->with('success', 'La publicité a été supprimée avec succès.');
    }
}
