<?php

declare(strict_types=1);

use Abdurrahaman\LaravelSeo\Services\SeoManager;
use Illuminate\Support\HtmlString;

if (! function_exists('laravel_seo')) {
    /**
     * Get the SEO manager instance or render SEO tags.
     */
    function laravel_seo(?callable $callback = null): SeoManager|HtmlString
    {
        $manager = app(SeoManager::class);

        if ($callback) {
            $callback($manager);

            return $manager->render();
        }

        return $manager;
    }
}

if (! function_exists('seo')) {
    /**
     * Alias for laravel_seo().
     */
    function seo(?callable $callback = null): SeoManager|HtmlString
    {
        return laravel_seo($callback);
    }
}
