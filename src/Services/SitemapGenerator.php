<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Services;

use Abdurrahaman\LaravelSeo\Contracts\SitemapProviderInterface;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SitemapGenerator
{
    public function __construct(
        protected SitemapProviderInterface $provider
    ) {}

    public function generate(): string
    {
        $urls = $this->collectUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";

            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars($url['lastmod'], ENT_XML1) . "</lastmod>\n";
            }

            if (! empty($url['changefreq'])) {
                $xml .= '    <changefreq>' . htmlspecialchars($url['changefreq'], ENT_XML1) . "</changefreq>\n";
            }

            if (isset($url['priority'])) {
                $xml .= '    <priority>' . number_format((float) $url['priority'], 1) . "</priority>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    public function generateAndCache(): string
    {
        $ttl = config('laravel-seo.sitemap.cache_ttl', 3600);

        return Cache::remember('laravel_seo_sitemap', $ttl, fn () => $this->generate());
    }

    public function clearCache(): void
    {
        Cache::forget('laravel_seo_sitemap');
    }

    /**
     * @return array<int, array{loc: string, lastmod?: string|null, changefreq?: string|null, priority?: float|null}>
     */
    protected function collectUrls(): array
    {
        $urls = [];

        // From provider (models + custom)
        $providerUrls = $this->provider->getUrls();
        foreach ($providerUrls as $url) {
            if ($this->shouldInclude($url['loc'])) {
                $urls[] = $url;
            }
        }

        // Config custom URLs
        $custom = config('laravel-seo.sitemap.custom_urls', []);
        foreach ($custom as $item) {
            if (! empty($item['loc']) && $this->shouldInclude($item['loc'])) {
                $urls[] = [
                    'loc' => $this->absoluteUrl($item['loc']),
                    'lastmod' => $item['lastmod'] ?? null,
                    'changefreq' => $item['changefreq'] ?? config('laravel-seo.sitemap.changefreq'),
                    'priority' => $item['priority'] ?? config('laravel-seo.sitemap.priority'),
                ];
            }
        }

        // Deduplicate by loc
        $seen = [];
        $unique = [];
        foreach ($urls as $url) {
            if (! isset($seen[$url['loc']])) {
                $seen[$url['loc']] = true;
                $unique[] = $url;
            }
        }

        return $unique;
    }

    protected function shouldInclude(string $loc): bool
    {
        if (! config('laravel-seo.sitemap.exclude_noindex', true)) {
            return true;
        }

        // Check if any seo_meta marks this as noindex or excluded
        $path = parse_url($loc, PHP_URL_PATH) ?? $loc;

        $excluded = SeoMeta::query()
            ->where(function ($q) {
                $q->where('robots_index', false)
                    ->orWhere('exclude_from_sitemap', true);
            })
            ->where(function ($q) use ($loc, $path) {
                $q->where('canonical_url', $loc)
                    ->orWhere('canonical_url', $path);
            })
            ->exists();

        return ! $excluded;
    }

    protected function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim(config('laravel-seo.site.url', config('app.url')), '/') . '/' . ltrim($url, '/');
    }
}
