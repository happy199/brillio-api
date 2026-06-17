<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Traits\HasWebpUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    use HasWebpUpload;

    /**
     * Display a listing of all advertisements.
     */
    public function index()
    {
        $advertisements = Advertisement::with(['organization', 'creator'])
            ->orderByDesc('id')
            ->get();

        return view('admin.advertisements.index', compact('advertisements'));
    }

    /**
     * Show the form for creating a new advertisement.
     */
    public function create()
    {
        return view('admin.advertisements.create');
    }

    /**
     * Store a newly created advertisement in storage.
     */
    public function store(Request $request)
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE) {
            return back()->withInput()->withErrors([
                'image' => "Le fichier image est trop volumineux. La configuration actuelle de PHP sur votre serveur (MAMP) limite les téléchargements à " . ini_get('upload_max_filesize') . ". Veuillez utiliser une image plus petite ou augmenter cette limite dans la configuration de votre serveur (php.ini)."
            ]);
        }

        $request->validate([
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'image' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);



        if ($request->hasFile('image')) {
            $imagePath = $this->uploadAndConvertToWebp($request->file('image'), 'advertisements');

            if ($imagePath) {
                Advertisement::create([
                    'title' => $request->title,
                    'image_path' => $imagePath,
                    'link_url' => $request->link_url,
                    'status' => Advertisement::STATUS_APPROVED, // Admin created ads are immediately active
                    'organization_id' => null, // None, created by admin
                    'created_by' => auth()->id(),
                    'validated_by' => auth()->id(),
                    'validated_at' => now(),
                ]);

                return redirect()->route('admin.advertisements.index')
                    ->with('success', 'La publicité a été créée et publiée avec succès.');
            }
        }

        return back()->with('error', 'Une erreur est survenue lors de la création du visuel.');
    }

    /**
     * Show the form for editing the specified advertisement.
     */
    public function edit(Advertisement $advertisement)
    {
        return view('admin.advertisements.edit', compact('advertisement'));
    }

    /**
     * Update the specified advertisement in storage.
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE) {
            return back()->withInput()->withErrors([
                'image' => "Le fichier image est trop volumineux. La configuration actuelle de PHP sur votre serveur (MAMP) limite les téléchargements à " . ini_get('upload_max_filesize') . ". Veuillez utiliser une image plus petite ou augmenter cette limite dans la configuration de votre serveur (php.ini)."
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

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'La publicité a été mise à jour avec succès.');
    }

    /**
     * Approve a pending advertisement.
     */
    public function approve(Advertisement $advertisement)
    {
        $advertisement->update([
            'status' => Advertisement::STATUS_APPROVED,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'La proposition de publicité a été validée avec succès et est désormais publiée.');
    }

    /**
     * Reject a pending advertisement.
     */
    public function reject(Advertisement $advertisement)
    {
        $advertisement->update([
            'status' => Advertisement::STATUS_REJECTED,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'La proposition de publicité a été rejetée.');
    }

    /**
     * Remove the specified advertisement from storage.
     */
    public function destroy(Advertisement $advertisement)
    {
        if ($advertisement->image_path) {
            Storage::disk('public')->delete($advertisement->image_path);
        }

        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'La publicité a été supprimée avec succès.');
    }
}
