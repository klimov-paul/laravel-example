<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Resources\Robots
 */
class RobotsTest extends TestCase
{
    public function testGet(): void
    {
        $content = $this->getJson('robots.txt')
            ->assertSuccessful()
            ->getContent();

        $this->assertStringContainsString(url('sitemap.xml'), $content);
    }
}
