<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Support\Carbon;

/**
 * Sitemap generates sitemap in XML format.
 */
class Sitemap
{
    /**
     * Generate sitemap.
     */
    public function __invoke()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>';
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $content .= $this->renderUrl(route('home'));
        $content .= $this->renderUrl(route('help.faq'));
        $content .= $this->renderUrl(route('help.contact'));

        $content .= '</urlset>';

        return response($content, 200, ['Content-Type' => 'text/xml']);
    }

    private function renderUrl(string $url): string
    {
        $mofifiedAt = Carbon::now()->format(DATE_ISO8601);

        return "
    <url>
        <loc>{$url}</loc>
        <priority>0.9</priority>
        <lastmod>{$mofifiedAt}</lastmod>
        <changefreq>monthly</changefreq>
    </url>
";
    }
}
