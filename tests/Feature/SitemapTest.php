<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Resources\Sitemap
 */
class SitemapTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
    }

    public function testGet()
    {
        $content = $this->getJson('sitemap.xml')
            ->assertSuccessful()
            ->getContent();

        $xml = simplexml_load_string($content);
        $xmlNamespaces = $xml->getNamespaces();
        $xml->registerXPathNamespace('ns', reset($xmlNamespaces));

        $urls = array_map(function ($element) {
            return (string) $element;
        }, $xml->xpath('ns:url/ns:loc'));

        $this->assertContains(route('home'), $urls);
        $this->assertContains(route('help.faq'), $urls);
        $this->assertContains(route('help.contact'), $urls);
    }
}
