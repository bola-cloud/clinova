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
        
        // Robust Fallback for hyphen/underscore mismatch and directory location
        if (!$exists) {
            $altPath = null;
            if (str_starts_with($path, 'patient-files/')) {
                $altPath = str_replace('patient-files/', 'patient_files/', $path);
                
                // If it exists in the NEW format (requested OLD), use NEW
                if (Storage::disk('public')->exists($altPath)) {
                    $path = $altPath;
                    $exists = true;
                }
            } elseif (str_starts_with($path, 'patient_files/')) {
                $altPath = str_replace('patient_files/', 'patient-files/', $path);
                
                // If it exists in the OLD format (requested NEW), move it to NEW
                if (Storage::disk('public')->exists($altPath)) {
                    Storage::disk('public')->move($altPath, $path);
                    $exists = true;
                }
            }
        }

        // Final attempt: Search for the filename in common directories if still not found
        if (!$exists) {
            $filename = basename($path);
            $searchPaths = ['patient_files/', 'patient-files/', 'visits/', 'patients/'];
            foreach ($searchPaths as $searchPath) {
                // We use a flat search in these directories for the filename
                // Note: This is slower but only happens on 404
                $files = Storage::disk('public')->files($searchPath);
                foreach ($files as $f) {
                    if (basename($f) === $filename) {
                        $path = $f;
                        $exists = true;
                        break 2;
                    }
                }
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
