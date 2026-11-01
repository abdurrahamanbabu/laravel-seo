<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Contracts;

interface SitemapProviderInterface
{
    /**
     * @return array<int, array{loc: string, lastmod?: string|null, changefreq?: string|null, priority?: float|null}>
     */
    public function getUrls(): array;
}
