<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Http\Controllers;

use Abdurrahaman\LaravelSeo\Services\SitemapGenerator;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SitemapController extends Controller
{
    public function index(SitemapGenerator $generator): Response
    {
        $xml = $generator->generateAndCache();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=' . config('laravel-seo.sitemap.cache_ttl', 3600),
        ]);
    }
}
