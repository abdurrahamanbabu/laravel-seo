<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Tests;

use Abdurrahaman\LaravelSeo\Providers\LaravelSeoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelSeoServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'SEO' => \Abdurrahaman\LaravelSeo\Facades\SEO::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('laravel-seo.site.name', 'Test Site');
        $app['config']->set('laravel-seo.site.url', 'https://example.com');
        $app['config']->set('laravel-seo.defaults.title', 'Default Title');
        $app['config']->set('laravel-seo.defaults.description', 'Default description');
        $app['config']->set('laravel-seo.cache.enabled', false);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
