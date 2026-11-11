<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Services;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SchemaGenerator
{
    protected array $customSchemas = [];

    public function generate(SeoData $data, ?Model $model = null): string
    {
        if (! config('laravel-seo.schema.enabled', true)) {
            return '';
        }

        $schemas = [];

        // Organization
        $org = $this->buildOrganization();
        if ($org) {
            $schemas[] = $org;
        }

        // Website
        $website = $this->buildWebsite();
        if ($website) {
            $schemas[] = $website;
        }

        // Page-specific
        $pageSchema = $this->buildPageSchema($data, $model);
        if ($pageSchema) {
            $schemas[] = $pageSchema;
        }

        // Custom registered schemas
        foreach ($this->customSchemas as $schema) {
            $schemas[] = $schema;
        }

        if (empty($schemas)) {
            return '';
        }

        $json = json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ($json === false) {
            return '';
        }

        // Prevent script injection
        $json = str_replace('</script>', '<\/script>', $json);

        return '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>';
    }

    public function register(array $schema): self
    {
        $this->customSchemas[] = $schema;

        return $this;
    }

    public function clearCustom(): self
    {
        $this->customSchemas = [];

        return $this;
    }

    protected function buildOrganization(): ?array
    {
        $config = config('laravel-seo.schema.organization', []);

        if (empty($config['name'])) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $config['type'] ?? 'Organization',
            'name' => $config['name'],
            'url' => $config['url'] ?? config('app.url'),
        ];

        if (! empty($config['logo'])) {
            $schema['logo'] = $this->absoluteUrl($config['logo']);
        }

        return $schema;
    }

    protected function buildWebsite(): ?array
    {
        $config = config('laravel-seo.schema.website', []);

        if (empty($config['name'])) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $config['name'],
            'url' => $config['url'] ?? config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => rtrim($config['url'] ?? config('app.url'), '/') . '/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    protected function buildPageSchema(SeoData $data, ?Model $model): ?array
    {
        $type = $data->schemaType ?? 'WebPage';
        $customData = $data->schemaData ?? [];

        $title = $data->title ?? config('laravel-seo.defaults.title');
        $description = $data->description ?? config('laravel-seo.defaults.description');
        $url = $data->canonicalUrl ?? url()->current();
        $image = $data->ogImage ?? config('laravel-seo.defaults.image');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $title,
            'description' => $description,
            'url' => $url,
        ];

        if ($image) {
            $schema['image'] = $this->absoluteUrl($image);
        }

        if ($data->author) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $data->author,
            ];
        }

        if ($data->publisher) {
            $schema['publisher'] = [
                '@type' => 'Organization',
                'name' => $data->publisher,
            ];
        }

        // Merge custom data (custom takes precedence for overlapping keys except @context/@type)
        if (! empty($customData)) {
            $schema = array_merge($schema, Arr::except($customData, ['@context']));
            if (isset($customData['@type'])) {
                $schema['@type'] = $customData['@type'];
            }
        }

        return $schema;
    }

    public function breadcrumb(array $items): array
    {
        $listItems = [];
        $position = 1;

        foreach ($items as $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'] ?? $item['title'] ?? '',
                'item' => $item['url'] ?? $item['item'] ?? '',
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }

    protected function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }
}
