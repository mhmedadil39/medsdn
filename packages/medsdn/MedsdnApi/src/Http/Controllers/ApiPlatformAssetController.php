<?php

namespace Webkul\MedsdnApi\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiPlatformAssetController extends Controller
{
    /**
     * Serve API Platform UI assets directly from the vendor package.
     */
    public function __invoke(string $path): BinaryFileResponse
    {
        $basePath = base_path('vendor/api-platform/laravel/public');
        $resolvedBasePath = realpath($basePath);

        abort_unless($resolvedBasePath !== false, 404);

        $candidatePath = realpath($resolvedBasePath.DIRECTORY_SEPARATOR.ltrim($path, '/'));

        abort_unless(
            $candidatePath !== false
            && is_file($candidatePath)
            && Str::startsWith($candidatePath, $resolvedBasePath.DIRECTORY_SEPARATOR),
            404
        );

        return response()->file($candidatePath, [
            'Content-Type' => $this->resolveContentType($candidatePath),
        ]);
    }

    /**
     * Resolve a browser-safe content type for API Platform assets.
     */
    protected function resolveContentType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'js'    => 'application/javascript; charset=UTF-8',
            'css'   => 'text/css; charset=UTF-8',
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            'jpg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'webp'  => 'image/webp',
            'html'  => 'text/html; charset=UTF-8',
            'map',
            'json'  => 'application/json; charset=UTF-8',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'eot'   => 'application/vnd.ms-fontobject',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };
    }
}
