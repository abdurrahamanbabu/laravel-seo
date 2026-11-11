<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Services;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Illuminate\Support\Str;

class MetaTagGenerator
{
    public function generate(SeoData $data): string
    {
        $tags = [];

        $title = $this->buildTitle($data);
        if ($title) {
            $tags[] = '<title>' . e($title) . '</title>';
        }

        if ($data->description) {
            $tags[] = '<meta name="description" content="' . e($this->truncate($data->description, 320)) . '">';
        }

        if (config('laravel-seo.output_keywords_meta') && ! empty($data->keywords)) {
            $keywords = is_array($data->keywords) ? implode(', ', $data->keywords) : $data->keywords;
            $tags[] = '<meta name="keywords" content="' . e($keywords) . '">';
        }

        $canonical = $this->buildCanonical($data);
        if ($canonical) {
            $tags[] = '<link rel="canonical" href="' . e($canonical) . '">';
        }

        $robots = $this->buildRobots($data);
        if ($robots) {
            $tags[] = '<meta name="robots" content="' . e($robots) . '">';
        }

        if ($data->author) {
            $tags[] = '<meta name="author" content="' . e($data->author) . '">';
        }

        if ($data->publisher) {
            $tags[] = '<meta name="publisher" content="' . e($data->publisher) . '">';
        }

        $language = $data->language ?? config('laravel-seo.defaults.language');
        if ($language) {
            $tags[] = '<meta http-equiv="content-language" content="' . e($language) . '">';
        }

        return implode("\n", $tags);
    }

    public function buildTitle(SeoData $data): string
    {
        $pageTitle = $data->title ?? config('laravel-seo.defaults.title', '');
        $siteName = config('laravel-seo.site.name', config('app.name', ''));
        $separator = config('laravel-seo.title.separator', '|');
        $template = config('laravel-seo.title.template', '{title} {separator} {site_name}');

        if (empty($pageTitle)) {
            return $siteName;
        }

        // Avoid duplicating site name if already present
        if ($siteName && Str::contains($pageTitle, $siteName)) {
            return $pageTitle;
        }

        return str_replace(
            ['{title}', '{site_name}', '{separator}'],
            [$pageTitle, $siteName, $separator],
            $template
        );
    }

    public function buildCanonical(SeoData $data): string
    {
        if ($data->canonicalUrl) {
            return $this->normalizeUrl($data->canonicalUrl);
        }

        $url = url()->current();

        return $this->normalizeUrl($url);
    }

    public function buildRobots(SeoData $data): string
    {
        $index = $data->robotsIndex ?? config('laravel-seo.defaults.robots_index', true);
        $follow = $data->robotsFollow ?? config('laravel-seo.defaults.robots_follow', true);

        $parts = [];
        $parts[] = $index ? 'index' : 'noindex';
        $parts[] = $follow ? 'follow' : 'nofollow';

        return implode(', ', $parts);
    }

    protected function normalizeUrl(string $url): string
    {
        $forceTrailingSlash = config('laravel-seo.canonical.force_trailing_slash', false);
        $removeQuery = config('laravel-seo.canonical.remove_query_parameters', true);
        $allowedParams = config('laravel-seo.canonical.allowed_query_parameters', []);

        $parsed = parse_url($url);

        if ($removeQuery && isset($parsed['query'])) {
            parse_str($parsed['query'], $params);
            if (! empty($allowedParams)) {
                $params = array_intersect_key($params, array_flip($allowedParams));
                $parsed['query'] = http_build_query($params);
            } else {
                unset($parsed['query']);
            }
        }

        $path = $parsed['path'] ?? '/';

        if ($forceTrailingSlash && ! str_ends_with($path, '/') && ! pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= '/';
        } elseif (! $forceTrailingSlash && $path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        return $scheme . '://' . $host . $port . $path . $query . $fragment;
    }

    protected function truncate(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 3) . '...';
    }
}
