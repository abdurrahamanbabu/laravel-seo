<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Console\Commands;

use Abdurrahaman\LaravelSeo\Services\SitemapGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'seo:generate-sitemap
                            {--output= : Custom output path relative to public path}
                            {--no-cache : Skip caching the generated sitemap}';

    protected $description = 'Generate the XML sitemap';

    public function handle(SitemapGenerator $generator): int
    {
        if (! config('laravel-seo.sitemap.enabled', true)) {
            $this->warn('Sitemap generation is disabled in config.');

            return self::FAILURE;
        }

        $this->info('Generating sitemap...');

        $xml = $this->option('no-cache')
            ? $generator->generate()
            : $generator->generateAndCache();

        $path = $this->option('output') ?: config('laravel-seo.sitemap.path', 'sitemap.xml');
        $fullPath = public_path($path);

        File::ensureDirectoryExists(dirname($fullPath));
        File::put($fullPath, $xml);

        $this->info("Sitemap written to: {$fullPath}");
        $this->line('URLs included: ' . substr_count($xml, '<url>'));

        return self::SUCCESS;
    }
}
