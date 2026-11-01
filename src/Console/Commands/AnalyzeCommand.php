<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Console\Commands;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Services\SeoAnalyzer;
use Illuminate\Console\Command;

class AnalyzeCommand extends Command
{
    protected $signature = 'seo:analyze
                            {--title= : SEO title}
                            {--description= : Meta description}
                            {--keyword= : Focus keyword}
                            {--content= : Content to analyze (or path to a file)}
                            {--url= : Page URL}
                            {--slug= : URL slug}';

    protected $description = 'Run SEO analysis on provided content and metadata';

    public function handle(SeoAnalyzer $analyzer): int
    {
        $title = $this->option('title') ?? '';
        $description = $this->option('description') ?? '';
        $keyword = $this->option('keyword') ?? '';
        $content = $this->option('content') ?? '';
        $url = $this->option('url') ?? '';
        $slug = $this->option('slug') ?? '';

        if ($content && is_file($content)) {
            $content = file_get_contents($content) ?: '';
        }

        $data = new SeoData(
            title: $title,
            description: $description,
            focusKeyword: $keyword,
        );

        $result = $analyzer->analyze($data, $content, $url, $slug);

        $this->newLine();
        $this->info('SEO Analysis Results');
        $this->line(str_repeat('-', 40));
        $this->line("Score: {$result['score']}%");
        $this->line('Status: ' . strtoupper($result['status']));
        $this->newLine();

        $headers = ['Check', 'Status', 'Message'];
        $rows = [];

        foreach ($result['checks'] as $check) {
            $rows[] = [
                $check['key'],
                strtoupper($check['status']),
                $check['message'],
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->comment('Note: These scores are heuristic guidelines, not guaranteed ranking factors.');

        return self::SUCCESS;
    }
}
