<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Traits\HasAdvertisementForm;
use App\Traits\HasWebpUpload;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    use HasAdvertisementForm, HasWebpUpload;

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
        if ($redirect = $this->abortIfFileTooLarge()) {
            return $redirect;
        }

        $request->validate($this->advertisementValidationRules(true));

        $organizationId = auth()->user()->organization_id;

        $imagePath = $this->handleAdvertisementImageUpload($request);

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

        if ($redirect = $this->abortIfFileTooLarge()) {
            return $redirect;
        }

        $request->validate($this->advertisementValidationRules());

        $data = [
            'title' => $request->title,
            'link_url' => $request->link_url,
        ];

        $imagePath = $this->handleAdvertisementImageUpload($request, $advertisement);
        if ($imagePath) {
            $data['image_path'] = $imagePath;
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
        abort_unless($advertisement->organization_id === $organizationId, 403);

        if ($advertisement->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($advertisement->image_path);
        }

        $advertisement->delete();

        return redirect()->route('organization.advertisements.index')
            ->with('success', 'La publicité a été supprimée avec succès.');
    }
}
