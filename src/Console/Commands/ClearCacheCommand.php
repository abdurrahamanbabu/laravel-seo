<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Console\Commands;

use Abdurrahaman\LaravelSeo\Services\SitemapGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'seo:clear-cache';

    protected $description = 'Clear Laravel SEO caches (meta + sitemap)';

    public function handle(SitemapGenerator $sitemapGenerator): int
    {
        $prefix = config('laravel-seo.cache.prefix', 'laravel_seo_');

        // Clear sitemap cache
        $sitemapGenerator->clearCache();

        // Best-effort: flush keys with our prefix when using a supporting store
        try {
            if (method_exists(Cache::getStore(), 'getRedis')) {
                $redis = Cache::getStore()->getRedis();
                $keys = $redis->keys($prefix . '*');
                if (! empty($keys)) {
                    $redis->del($keys);
                }
            }
        } catch (\Throwable) {
            // Ignore if store does not support key scanning
        }

        $this->info('Laravel SEO cache cleared.');

        return self::SUCCESS;
    }
}
