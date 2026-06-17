<?php

namespace App\Traits;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasWebpUpload
{
    /**
     * Upload an image file, convert it to WebP format, and save it to storage.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory
     * @param  int  $quality
     * @param  string  $disk
     * @return string|bool The stored path relative to the disk or false on failure
     */
    protected function uploadAndConvertToWebp($file, $directory = 'publicities', $quality = 85, $disk = 'public')
    {
        try {
            $tempPath = $file->getRealPath();
            $info = @getimagesize($tempPath);
            $mime = $info['mime'] ?? '';

            // Handle type-specific creation
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = @imagecreatefromjpeg($tempPath);
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($tempPath);
                    break;
                case 'image/gif':
                    $image = @imagecreatefromgif($tempPath);
                    break;
                case 'image/webp':
                    $image = @imagecreatefromwebp($tempPath);
                    break;
                default:
                    $image = @imagecreatefromstring(file_get_contents($tempPath));
                    break;
            }

            if (! $image) {
                Log::warning('GD could not load image for WebP conversion, storing original file instead.', [
                    'mime' => $mime,
                    'original_name' => $file->getClientOriginalName(),
                ]);

                return $file->store($directory, $disk);
            }

            // Create temporary filename
            $filename = Str::uuid().'.webp';
            $tempWebpPath = tempnam(sys_get_temp_dir(), 'webp_');

            // Save with GD as WebP
            if (! @imagewebp($image, $tempWebpPath, $quality)) {
                imagedestroy($image);
                @unlink($tempWebpPath);
                Log::warning('GD imagewebp execution failed, storing original file instead.');

                return $file->store($directory, $disk);
            }

            imagedestroy($image);

            // Put file in target directory
            $path = Storage::disk($disk)->putFileAs($directory, new File($tempWebpPath), $filename);

            @unlink($tempWebpPath);

            return $path;
        } catch (\Exception $e) {
            Log::error('WebP conversion exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Fallback: store original file
            return $file->store($directory, $disk);
        }
    }
}
