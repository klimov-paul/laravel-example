<?php

namespace App\Http\Controllers\Resources;

/**
 * Manifest generates web app manifest content.
 *
 * @see https://web.dev/add-manifest/
 */
class Manifest
{
    /**
     * Generate web app manifest content.
     */
    public function __invoke()
    {
        return [
            'name' => config('app.name'),
            'short_name' => config('app.name'),
            'start_url' => route('home'),
            'icons' => [
                [
                    'src' => asset('img/icons/android-chrome-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => asset('img/icons/android-chrome-256x256.png'),
                    'sizes' => '256x256',
                    'type' => 'image/png',
                ],
            ],
            'theme_color' => '#ffffff',
            'background_color' => '#ffffff',
            'display' => 'standalone',
        ];
    }
}
