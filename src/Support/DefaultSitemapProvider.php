<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Support;

use Abdurrahaman\LaravelSeo\Contracts\SitemapProviderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class DefaultSitemapProvider implements SitemapProviderInterface
{
    /**
     * @return array<int, array{loc: string, lastmod?: string|null, changefreq?: string|null, priority?: float|null}>
     */
    public function getUrls(): array
    {
        $urls = [];
        $models = config('laravel-seo.sitemap.models', []);

        foreach ($models as $modelClass => $options) {
            if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            $query = $modelClass::query();

            // Optional scope
            if (! empty($options['scope']) && method_exists($modelClass, $options['scope'])) {
                $query->{$options['scope']}();
            }

            $route = $options['route'] ?? null;
            $routeParams = $options['route_params'] ?? ['id' => 'id'];
            $lastmodField = $options['lastmod'] ?? 'updated_at';
            $changefreq = $options['changefreq'] ?? config('laravel-seo.sitemap.changefreq', 'weekly');
            $priority = $options['priority'] ?? config('laravel-seo.sitemap.priority', 0.5);

            $query->orderBy($lastmodField, 'desc')->chunk(200, function ($items) use (&$urls, $route, $routeParams, $lastmodField, $changefreq, $priority) {
                foreach ($items as $item) {
                    $loc = $this->resolveUrl($item, $route, $routeParams);
                    if (! $loc) {
                        continue;
                    }

                    $lastmod = null;
                    if ($lastmodField && isset($item->{$lastmodField})) {
                        $lastmod = $item->{$lastmodField}->toAtomString();
                    }

                    $urls[] = [
                        'loc' => $loc,
                        'lastmod' => $lastmod,
                        'changefreq' => $changefreq,
                        'priority' => (float) $priority,
                    ];
                }
            });
        }

        return $urls;
    }

    protected function resolveUrl(Model $model, ?string $route, array $routeParams): ?string
    {
        if ($route && Route::has($route)) {
            $params = [];
            foreach ($routeParams as $paramName => $attribute) {
                $params[$paramName] = $model->{$attribute} ?? $model->getKey();
            }

            try {
                return route($route, $params, true);
            } catch (\Throwable) {
                return null;
            }
        }

        // Fallback: try getUrl / url / path attributes or methods
        if (method_exists($model, 'getSeoUrl')) {
            return $model->getSeoUrl();
        }

        if (isset($model->url) && is_string($model->url)) {
            return $this->absolute($model->url);
        }

        if (isset($model->slug)) {
            return $this->absolute('/' . ltrim($model->slug, '/'));
        }

        return null;
    }

    protected function absolute(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim(config('laravel-seo.site.url', config('app.url')), '/') . '/' . ltrim($url, '/');
    }
}
