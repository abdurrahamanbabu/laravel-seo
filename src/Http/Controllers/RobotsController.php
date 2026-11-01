<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class RobotsController extends Controller
{
    public function index(): Response
    {
        $custom = config('laravel-seo.robots.content');

        if (is_string($custom) && $custom !== '') {
            $content = $custom;
        } else {
            $content = $this->generate();
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    protected function generate(): string
    {
        $lines = ['User-agent: *'];

        foreach (config('laravel-seo.robots.allow', ['/']) as $path) {
            $lines[] = 'Allow: ' . $path;
        }

        foreach (config('laravel-seo.robots.disallow', []) as $path) {
            $lines[] = 'Disallow: ' . $path;
        }

        $sitemapUrl = config('laravel-seo.robots.sitemap_url');

        if (! $sitemapUrl && config('laravel-seo.sitemap.enabled', true)) {
            $sitemapUrl = url(config('laravel-seo.sitemap.path', 'sitemap.xml'));
        }

        if ($sitemapUrl) {
            $lines[] = 'Sitemap: ' . $sitemapUrl;
        }

        return implode("\n", $lines) . "\n";
    }
}
