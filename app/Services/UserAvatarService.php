<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserAvatarService
{
    /**
     * Télécharge un avatar depuis une URL et le stocke localement
     */
    public function downloadFromUrl(User $user, string $url): ?string
    {
        try {
            // Si l'URL n'a pas changé et qu'on a déjà un fichier, on ne fait rien
            if ($user->profile_photo_url === $url && $user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                return $user->profile_photo_path;
            }

            // Télécharger l'image
            $response = Http::get($url);

            if ($response->successful()) {
                // Supprimer l'ancienne image si elle existe
                $this->deleteCurrentAvatar($user);

                // Générer un nom de fichier unique
                $filename = 'profile-photos/' . $user->id . '_' . time() . '.jpg';

                // Stocker la nouvelle image
                Storage::disk('public')->put($filename, $response->body());

                // Mettre à jour le chemin
                $user->profile_photo_path = $filename;
                // On garde l'URL source aussi pour référence
                $user->profile_photo_url = $url;
                $user->save();

                Log::info('Avatar downloaded and stored via Service', ['user_id' => $user->id, 'path' => $filename]);

                return $filename;
            }
        }
        catch (\Exception $e) {
            Log::error('Failed to download avatar via Service', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Upload un fichier avatar
     */
    public function upload(User $user, UploadedFile $file): string
    {
        // Supprimer l'ancienne image si elle existe
        $this->deleteCurrentAvatar($user);

        // Stocker la nouvelle photo
        $path = $file->store('profile-photos', 'public');

        $user->forceFill([
            'profile_photo_path' => $path,
        ])->save();

        return $path;
    }

    /**
     * Supprime l'avatar actuel du stockage
     */
    public function deleteCurrentAvatar(User $user): void
    {
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
    }

    /**
     * Supprime l'avatar de l'utilisateur (fichier et base de données)
     */
    public function delete(User $user): void
    {
        $this->deleteCurrentAvatar($user);

        $user->forceFill([
            'profile_photo_path' => null,
            'profile_photo_url' => null,
        ])->save();
    }
}