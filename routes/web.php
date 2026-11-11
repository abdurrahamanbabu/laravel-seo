<?php

declare(strict_types=1);

use Abdurrahaman\LaravelSeo\Http\Controllers\GenerateSeoController;
use Abdurrahaman\LaravelSeo\Http\Controllers\RobotsController;
use Abdurrahaman\LaravelSeo\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

$middleware = config('laravel-seo.routes.middleware', ['web']);
$prefix = config('laravel-seo.routes.prefix', '');

Route::middleware($middleware)->prefix($prefix)->group(function () {
    if (config('laravel-seo.sitemap.enabled', true)) {
        Route::get(config('laravel-seo.sitemap.path', 'sitemap.xml'), [SitemapController::class, 'index'])
            ->name('laravel-seo.sitemap');
    }

    if (config('laravel-seo.robots.enabled', false)) {
        Route::get(config('laravel-seo.robots.path', 'robots.txt'), [RobotsController::class, 'index'])
            ->name('laravel-seo.robots');
    }

    if (config('laravel-seo.ai.enabled', false)) {
        $aiMiddleware = config('laravel-seo.ai.middleware', ['web']);
        $aiRoute = config('laravel-seo.ai.route', 'laravel-seo/generate');

        Route::middleware($aiMiddleware)
            ->post($aiRoute, GenerateSeoController::class)
            ->name('laravel-seo.generate');
    }
});
