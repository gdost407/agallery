<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PwaController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $pwaAsset): BinaryFileResponse
    {
        $contentTypes = [
            'manifest.webmanifest' => 'application/manifest+json',
            'service-worker.js' => 'application/javascript',
            'pwa.js' => 'application/javascript',
            'pwa.css' => 'text/css',
            'offline.html' => 'text/html',
            'pwa-icon-180.png' => 'image/png',
            'pwa-icon-192.png' => 'image/png',
            'pwa-icon-512.png' => 'image/png',
            'pwa-icon-maskable-512.png' => 'image/png',
        ];

        abort_unless(isset($contentTypes[$pwaAsset]), 404);
        $path = public_path($pwaAsset);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => $contentTypes[$pwaAsset],
            'Cache-Control' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ])->setPublic();
    }
}
