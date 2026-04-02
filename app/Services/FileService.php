<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Optimize an image file to reduce size while keeping it browser-compatible.
     * 
     * @param string $path Absolute path on the disk
     * @return bool Success
     */
    public static function optimizeImage(string $path): bool
    {
        if (!file_exists($path)) return false;

        $info = getimagesize($path);
        if (!$info) return false;

        $mime = $info['mime'];
        $image = null;

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($path);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($path);
                break;
        }

        if ($image) {
            // Resize if too large (e.g. max 2000px width/height)
            $width = imagesx($image);
            $height = imagesy($image);
            $maxDim = 2000;

            if ($width > $maxDim || $height > $maxDim) {
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth = $maxDim;
                    $newHeight = $maxDim / $ratio;
                } else {
                    $newHeight = $maxDim;
                    $newWidth = $maxDim * $ratio;
                }
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                if ($mime == 'image/png' || $mime == 'image/webp') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                }

                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $newImage;
            }

            // Save back as JPEG (most efficient) or original format
            // We'll stick to original format but with 75% quality
            switch ($mime) {
                case 'image/jpeg':
                    imagejpeg($image, $path, 75);
                    break;
                case 'image/png':
                    // PNG compression is 0-9
                    imagepng($image, $path, 6);
                    break;
                case 'image/webp':
                    imagewebp($image, $path, 75);
                    break;
            }

            imagedestroy($image);
            return true;
        }

        return false;
    }

    /**
     * Compress non-image binary content.
     */
    public static function compressContent(string $content): string
    {
        return gzencode($content, 9);
    }

    /**
     * Decompress binary content.
     */
    public static function decompressContent(string $content): string
    {
        try {
            $decompressed = @gzdecode($content);
            return $decompressed ?: $content;
        } catch (\Exception $e) {
            return $content;
        }
    }

    /**
     * Check if content is gzipped.
     */
    /**
     * Check if content is gzipped.
     */
    public static function isCompressed(string $content): bool
    {
        return str_starts_with($content, "\x1f\x8b\x08");
    }

    /**
     * Compress a file on disk (in place).
     */
    public static function compressFileInPlace(string $disk, string $path): void
    {
        $content = Storage::disk($disk)->get($path);
        
        // If already compressed or looks like one, skip
        if (self::isCompressed($content)) return;

        $compressed = self::compressContent($content);
        Storage::disk($disk)->put($path, $compressed);
    }

    /**
     * Optimize an image on disk (in place).
     */
    public static function optimizeImageInPlace(string $disk, string $path): void
    {
        $fullPath = Storage::disk($disk)->path($path);
        self::optimizeImage($fullPath);
    }
}
