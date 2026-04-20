<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    /**
     * Serve a file, decompressing it if necessary.
     */
    public function serve(string $path)
    {
        // For security, you might want to check permissions here
        // For now, we assume the path is valid and within the public disk
        
        $exists = Storage::disk('public')->exists($path);
        
        // Fallback for hyphen/underscore mismatch (e.g. patient-files vs patient_files)
        if (!$exists) {
            $altPath = null;
            if (str_starts_with($path, 'patient-files/')) {
                $altPath = str_replace('patient-files/', 'patient_files/', $path);
                
                // If it exists in the old format, move it to the new format
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->move($path, $altPath);
                    $path = $altPath;
                    $exists = true;
                }
            } elseif (str_starts_with($path, 'patient_files/')) {
                $altPath = str_replace('patient_files/', 'patient-files/', $path);
            }

            if (!$exists && $altPath && Storage::disk('public')->exists($altPath)) {
                $path = $altPath;
                $exists = true;
            }
        }

        if (!$exists) {
            abort(404);
        }

        $content = Storage::disk('public')->get($path);
        
        // If it's compressed (Gzip), decompress it
        if (FileService::isCompressed($content)) {
            $content = FileService::decompressContent($content);
        }

        // Determine Mime Type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);

        // If detection fails or is generic, use extension as fallback
        if (!$mimeType || $mimeType === 'application/octet-stream' || $mimeType === 'text/plain') {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mimes = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'txt' => 'text/plain',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            $mimeType = $mimes[$extension] ?? $mimeType;
        }

        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"')
            ->header('Cache-Control', 'public, max-age=31536000');
    }
}
