<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Tests\Unit;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Services\SeoAnalyzer;
use Abdurrahaman\LaravelSeo\Tests\TestCase;

class SeoAnalyzerTest extends TestCase
{
    private SeoAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new SeoAnalyzer();
    }

    public function test_good_score_with_optimized_content(): void
    {
        $keyword = 'laravel seo';
        $data = new SeoData(
            title: 'Laravel SEO Package Guide',
            description: 'Learn how to use the Laravel SEO package for better rankings with focus on laravel seo best practices.',
            focusKeyword: $keyword,
            canonicalUrl: 'https://example.com/laravel-seo'
        );

        $content = '<h1>Laravel SEO Package</h1>'
            . '<p>This guide covers laravel seo optimization techniques for your application.</p>'
            . '<h2>Why Laravel SEO Matters</h2>'
            . '<p>' . str_repeat('Content about laravel seo and related topics. ', 80) . '</p>'
            . '<a href="/docs">Internal link</a>'
            . '<img src="/img.jpg" alt="Laravel SEO diagram">';

        $result = $this->analyzer->analyze($data, $content, 'https://example.com/laravel-seo', 'laravel-seo');

        $this->assertGreaterThanOrEqual(50, $result['score']);
        $this->assertContains($result['status'], ['good', 'ok']);
        $this->assertNotEmpty($result['checks']);
    }

    public function test_poor_score_without_keyword(): void
    {
        $data = new SeoData(title: 'Something', description: '');
        $result = $this->analyzer->analyze($data, '', '', '');

        $this->assertLessThan(40, $result['score']);
        $this->assertSame(SeoAnalyzer::STATUS_POOR, $result['status']);
    }

    public function test_detects_keyword_in_title(): void
    {
        $data = new SeoData(title: 'Best Laravel SEO Tips', focusKeyword: 'laravel seo');
        $result = $this->analyzer->analyze($data, 'Some content', '', '');

        $titleCheck = collect($result['checks'])->firstWhere('key', 'keyword_in_title');
        $this->assertSame(SeoAnalyzer::STATUS_GOOD, $titleCheck['status']);
    }
}
