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
        
        if (!Storage::disk('public')->exists($path)) {
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
