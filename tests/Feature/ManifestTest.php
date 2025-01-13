<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Resources\Manifest
 */
class ManifestTest extends TestCase
{
    public function testWebManifest(): void
    {
        $this->getJson(route('manifest.webmanifest'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'name',
                'short_name',
                'start_url',
                'icons',
            ]);
    }
}
