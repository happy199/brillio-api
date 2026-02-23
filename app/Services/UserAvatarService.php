<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserAvatarService
{
    /**
     * Vérifie si une URL provient de LinkedIn
     */
    public function isLinkedInUrl(string $url): bool
    {
        return str_starts_with($url, 'https://media.licdn.com/dms/image/');
    }

    /**
     * Télécharge un avatar depuis une URL et le stocke localement
     */
    public function downloadFromUrl(User $user, string $url): ?string
    {
        try {
            // Si l'utilisateur a déjà une photo locale (path)
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {

                // Si c'est un mentor, on est très conservateur :
                // on ne remplace la photo que si la nouvelle URL est LinkedIn ET différente de l'actuelle
                if ($user->isMentor()) {
                    if (! $this->isLinkedInUrl($url)) {
                        Log::info('[Safety Check] skipping avatar download for mentor: incoming URL is not LinkedIn', [
                            'user_id' => $user->id,
                            'url' => $url,
                        ]);

                        return $user->profile_photo_path;
                    }

                    if ($user->profile_photo_url === $url) {
                        return $user->profile_photo_path;
                    }
                } else {
                    // Pour les autres types de comptes, comportement standard :
                    // on ne télécharge que si l'URL a changé
                    if ($user->profile_photo_url === $url) {
                        return $user->profile_photo_path;
                    }
                }
            }

            // Télécharger l'image
            $response = Http::get($url);

            if ($response->successful()) {
                // Supprimer l'ancienne image si elle existe
                $this->deleteCurrentAvatar($user);

                // Générer un nom de fichier unique
                $filename = 'profile-photos/'.$user->id.'_'.time().'.jpg';

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
        } catch (\Exception $e) {
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
