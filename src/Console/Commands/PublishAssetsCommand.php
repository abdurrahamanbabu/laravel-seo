<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Console\Commands;

use Illuminate\Console\Command;

class PublishAssetsCommand extends Command
{
    protected $signature = 'seo:publish-assets {--force : Overwrite existing assets}';

    protected $description = 'Publish Laravel SEO frontend assets (CSS, JS, images)';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'laravel-seo-assets',
            '--force' => $this->option('force'),
        ]);

        $this->info('Laravel SEO assets published to public/vendor/laravel-seo');

        return self::SUCCESS;
    }
}
