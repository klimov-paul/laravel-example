<?php

namespace App\Http\Controllers\Resources;

/**
 * Robots generates content for 'robots.txt' file.
 */
class Robots
{
    /**
     * Generate 'robots.txt' content.
     */
    public function __invoke()
    {
        if (config('appearance.allow_robots')) {
            $disallowPaths = [
                '/admin/',
                '/administration/',
            ];

            $disallowParts = array_map(function ($value) {
                return 'Disallow: '.$value;
            }, $disallowPaths);

            $disallow = implode("\n", $disallowParts);
        } else {
            $disallow = 'Disallow: /';
        }

        $siteMapUrl = url('sitemap.xml');

        $content = <<<ROBOTS
User-agent: *
{$disallow}

Sitemap: {$siteMapUrl}
ROBOTS;

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
