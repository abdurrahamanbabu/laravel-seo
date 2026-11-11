<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'seo:install
                            {--force : Overwrite existing published files}
                            {--config : Publish config only}
                            {--migrations : Publish migrations only}
                            {--views : Publish views only}
                            {--assets : Publish assets only}
                            {--lang : Publish translations only}';

    protected $description = 'Install the Laravel SEO package (publish config, migrations, views, assets, lang)';

    public function handle(): int
    {
        $force = $this->option('force');
        $only = array_filter([
            'config' => $this->option('config'),
            'migrations' => $this->option('migrations'),
            'views' => $this->option('views'),
            'assets' => $this->option('assets'),
            'lang' => $this->option('lang'),
        ]);

        $publishAll = empty($only);

        if ($publishAll || isset($only['config'])) {
            $this->call('vendor:publish', [
                '--tag' => 'laravel-seo-config',
                '--force' => $force,
            ]);
        }

        if ($publishAll || isset($only['migrations'])) {
            $this->call('vendor:publish', [
                '--tag' => 'laravel-seo-migrations',
                '--force' => $force,
            ]);
        }

        if ($publishAll || isset($only['views'])) {
            $this->call('vendor:publish', [
                '--tag' => 'laravel-seo-views',
                '--force' => $force,
            ]);
        }

        if ($publishAll || isset($only['assets'])) {
            $this->call('vendor:publish', [
                '--tag' => 'laravel-seo-assets',
                '--force' => $force,
            ]);
        }

        if ($publishAll || isset($only['lang'])) {
            $this->call('vendor:publish', [
                '--tag' => 'laravel-seo-lang',
                '--force' => $force,
            ]);
        }

        if ($publishAll) {
            $this->info('Laravel SEO package installed successfully.');
            $this->line('Next steps:');
            $this->line('  1. Run: php artisan migrate');
            $this->line('  2. Add {{ laravel_seo() }} or @seo to your layout <head>');
            $this->line('  3. Configure config/laravel-seo.php as needed');
        } else {
            $this->info('Selected Laravel SEO assets published.');
        }

        return self::SUCCESS;
    }
}
