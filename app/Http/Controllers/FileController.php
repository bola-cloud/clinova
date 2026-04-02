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
        
        try {
            $mimeType = Storage::disk('public')->mimeType($path);
        } catch (\Exception $e) {
            $mimeType = 'application/octet-stream';
        }

        // If it's compressed (Gzip), decompress it
        if (FileService::isCompressed($content)) {
            $content = FileService::decompressContent($content);
        }

        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
}
