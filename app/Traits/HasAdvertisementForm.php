<?php

namespace App\Traits;

use App\Models\Advertisement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasAdvertisementForm
{
    /**
     * Abort with a validation error when PHP's upload_max_filesize is exceeded.
     * Must be called before request()->validate() because the file is lost at that point.
     */
    protected function abortIfFileTooLarge(): ?RedirectResponse
    {
        $file = request()->file('image');

        if ($file instanceof UploadedFile && ! $file->isValid() && $file->getError() === UPLOAD_ERR_INI_SIZE) {
            return back()->withInput()->withErrors([
                'image' => 'Le fichier image est trop volumineux. La configuration actuelle limite les téléchargements à '.ini_get('upload_max_filesize').'. Veuillez utiliser une image plus petite.',
            ]);
        }

        return null;
    }

    /**
     * Common validation rules for advertisement forms.
     *
     * @param  bool  $imageRequired  Whether the image field is required (create) or optional (update).
     */
    protected function advertisementValidationRules(bool $imageRequired = false): array
    {
        return [
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'image' => ($imageRequired ? 'required' : 'nullable').'|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ];
    }

    /**
     * Upload a new image for an advertisement and delete the previous one.
     * Returns the new image path, or null when no new file was uploaded.
     */
    protected function handleAdvertisementImageUpload(array $validated, ?Advertisement $advertisement = null): ?string
    {
        if (! isset($validated['image'])) {
            return null;
        }

        $imagePath = $this->uploadAndConvertToWebp($validated['image'], 'advertisements');

        if ($imagePath) {
            // Delete the old image when updating an existing advertisement
            if ($advertisement && $advertisement->image_path) {
                Storage::disk('public')->delete($advertisement->image_path);
            }
        }

        return $imagePath ?: null;
    }
}
