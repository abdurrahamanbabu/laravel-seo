<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Providers;

use Abdurrahaman\LaravelSeo\Console\Commands\AnalyzeCommand;
use Abdurrahaman\LaravelSeo\Console\Commands\ClearCacheCommand;
use Abdurrahaman\LaravelSeo\Console\Commands\GenerateSitemapCommand;
use Abdurrahaman\LaravelSeo\Console\Commands\InstallCommand;
use Abdurrahaman\LaravelSeo\Console\Commands\PublishAssetsCommand;
use Abdurrahaman\LaravelSeo\Contracts\SeoRepositoryInterface;
use Abdurrahaman\LaravelSeo\Contracts\SitemapProviderInterface;
use Abdurrahaman\LaravelSeo\Repositories\Eloquent\SeoRepository;
use Abdurrahaman\LaravelSeo\Services\MetaTagGenerator;
use Abdurrahaman\LaravelSeo\Services\SchemaGenerator;
use Abdurrahaman\LaravelSeo\Services\AiSeoGenerator;
use Abdurrahaman\LaravelSeo\Services\SeoAnalyzer;
use Abdurrahaman\LaravelSeo\Services\SeoManager;
use Abdurrahaman\LaravelSeo\Services\SitemapGenerator;
use Abdurrahaman\LaravelSeo\Services\SocialMetaGenerator;
use Abdurrahaman\LaravelSeo\Support\DefaultSitemapProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class LaravelSeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/laravel-seo.php',
            'laravel-seo'
        );

        $this->app->singleton(SeoRepositoryInterface::class, SeoRepository::class);
        $this->app->singleton(SitemapProviderInterface::class, DefaultSitemapProvider::class);

        $this->app->singleton(SeoManager::class, function ($app) {
            return new SeoManager(
                $app->make(SeoRepositoryInterface::class),
                $app->make(MetaTagGenerator::class),
                $app->make(SocialMetaGenerator::class),
                $app->make(SchemaGenerator::class)
            );
        });

        $this->app->singleton(MetaTagGenerator::class);
        $this->app->singleton(SocialMetaGenerator::class);
        $this->app->singleton(SchemaGenerator::class);
        $this->app->singleton(SeoAnalyzer::class);
        $this->app->singleton(SitemapGenerator::class);
        $this->app->singleton(AiSeoGenerator::class);

        $this->app->alias(SeoManager::class, 'laravel-seo');
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerViews();
        $this->registerBladeDirectives();
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'laravel-seo');
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../../config/laravel-seo.php' => config_path('laravel-seo.php'),
        ], 'laravel-seo-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'laravel-seo-migrations');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/laravel-seo'),
        ], 'laravel-seo-views');

        $this->publishes([
            __DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/laravel-seo'),
        ], 'laravel-seo-lang');

        $this->publishes([
            __DIR__ . '/../../resources/assets' => public_path('vendor/laravel-seo'),
        ], 'laravel-seo-assets');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            PublishAssetsCommand::class,
            GenerateSitemapCommand::class,
            ClearCacheCommand::class,
            AnalyzeCommand::class,
        ]);
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'laravel-seo');

        // Defer until the Blade compiler is available with a valid compiled path
        $this->callAfterResolving('blade.compiler', function () {
            Blade::componentNamespace('Abdurrahaman\\LaravelSeo\\View\\Components', 'laravel-seo');
        });
    }

    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function () {
            Blade::directive('seo', function () {
                return '<?php echo app(\Abdurrahaman\LaravelSeo\Services\SeoManager::class)->render(); ?>';
            });
        });
    }

    protected function registerRoutes(): void
    {
        if (config('laravel-seo.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }
    }
}
