<?php

namespace App\Services;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoRecordingService
{
    /**
     * Validate and process video upload request from HTTP controllers
     */
    public function processUploadRequest(Request $request, string $prefix): ?string
    {
        $validated = $request->validate([
            'video' => ['nullable', 'file', 'mimes:webm,mp4,mkv,avi,mov'], // NOSONAR: Video recording uploads for long meetings can be arbitrarily large
            'file' => ['nullable', 'file', 'mimes:webm,mp4,mkv,avi,mov'],  // NOSONAR
        ]);

        $file = $validated['video'] ?? $validated['file'] ?? null;

        if (! $file) {
            return null;
        }

        return $this->storeRecording($file, $prefix);
    }

    /**
     * Handle monetized video recording download for a user (Jeune or Mentor)
     */
    public function handleSessionVideoDownload(User $user, MentoringSession $session, string $walletRedirectRoute)
    {
        if (empty($session->video_recording_url)) {
            return redirect()->back()->with('error', "L'enregistrement vidéo n'est pas disponible pour cette séance.");
        }

        $cost = app(WalletService::class)->getFeatureCost('video_recording_download', 15);

        if ($user->credits_balance < $cost) {
            $missing = $cost - $user->credits_balance;

            return redirect()->route($walletRedirectRoute)->with('warning', "Votre solde de crédits est insuffisant ({$cost} crédits requis). Il vous manque {$missing} crédits pour télécharger cet enregistrement vidéo.");
        }

        app(WalletService::class)->deductCredits(
            $user,
            $cost,
            'feature_use',
            "Téléchargement de l'enregistrement vidéo de la séance : {$session->title}",
            $session
        );

        return redirect()->away($session->video_recording_url);
    }

    /**
     * Store a video recording file either on Cloudinary (if configured) or locally in public storage.
     *
     * @param  UploadedFile|string  $file
     * @return string Public URL of the saved video
     */
    public function storeRecording($file, string $filenamePrefix = 'video_meeting'): string
    {
        $cloudinaryUrl = config('services.cloudinary.url') ?? env('CLOUDINARY_URL');
        $cloudName = config('services.cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME');

        // Check if Cloudinary is configured
        if (! empty($cloudinaryUrl) || ! empty($cloudName)) {
            try {
                $url = $this->uploadToCloudinary($file, $filenamePrefix);
                if (! empty($url)) {
                    Log::info("Video successfully uploaded to Cloudinary: {$url}");

                    return $url;
                }
            } catch (\Throwable $e) {
                Log::error('Cloudinary Video Upload Failed, falling back to local storage: '.$e->getMessage());
            }
        } else {
            Log::warning('Cloudinary credentials missing (CLOUDINARY_URL or CLOUDINARY_CLOUD_NAME not found). Falling back to local storage.');
        }

        // Local storage fallback
        return $this->uploadToLocalStorage($file, $filenamePrefix);
    }

    /**
     * Upload to Cloudinary using REST API
     */
    private function uploadToCloudinary($file, string $prefix): ?string
    {
        $cloudinaryUrl = config('services.cloudinary.url') ?? env('CLOUDINARY_URL');
        $cloudName = config('services.cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME');
        $uploadPreset = config('services.cloudinary.upload_preset') ?? env('CLOUDINARY_UPLOAD_PRESET', 'ml_default');

        if (empty($cloudName) && ! empty($cloudinaryUrl)) {
            if (str_contains($cloudinaryUrl, '@')) {
                $parts = explode('@', $cloudinaryUrl);
                $cloudName = trim(end($parts));
            }
        }

        if (empty($cloudName)) {
            $cloudName = 'uysp6lxe';
        }

        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $folder = str_contains($prefix, 'advisor_call')
            ? 'brillio_recordings/orientation'
            : 'brillio_recordings/mentoring';

        $response = Http::timeout(180)
            ->attach('file', file_get_contents($filePath), $prefix.'_'.time().'.webm')
            ->post("https://api.cloudinary.com/v1_1/{$cloudName}/video/upload", [
                'upload_preset' => $uploadPreset,
                'folder' => $folder,
            ]);

        if ($response->successful()) {
            return $response->json('secure_url');
        }

        Log::warning('Cloudinary response error (Status '.$response->status().'): '.$response->body());

        return null;
    }

    /**
     * Upload to local public storage (storage/app/public/video_recordings/...)
     */
    private function uploadToLocalStorage($file, string $prefix): string
    {
        $subfolder = str_contains($prefix, 'advisor_call')
            ? 'video_recordings/orientation'
            : 'video_recordings/mentoring';

        $filename = $prefix.'_'.Str::uuid()->toString().'.webm';

        if ($file instanceof UploadedFile) {
            $path = $file->storeAs($subfolder, $filename, 'public');
        } else {
            Storage::disk('public')->put($subfolder.'/'.$filename, file_get_contents($file));
            $path = $subfolder.'/'.$filename;
        }

        return Storage::url($path);
    }
}
