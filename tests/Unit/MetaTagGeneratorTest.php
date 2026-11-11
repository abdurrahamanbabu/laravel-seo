<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Tests\Unit;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Services\MetaTagGenerator;
use Abdurrahaman\LaravelSeo\Tests\TestCase;

class MetaTagGeneratorTest extends TestCase
{
    private MetaTagGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new MetaTagGenerator();
    }

    public function test_builds_title_with_template(): void
    {
        $data = new SeoData(title: 'About Us');
        $title = $this->generator->buildTitle($data);

        $this->assertStringContainsString('About Us', $title);
        $this->assertStringContainsString('Test Site', $title);
    }

    public function test_generates_meta_description(): void
    {
        $data = new SeoData(
            title: 'Test',
            description: 'A great meta description for testing purposes.'
        );

        $html = $this->generator->generate($data);

        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('A great meta description', $html);
    }

    public function test_generates_robots_meta(): void
    {
        $data = new SeoData(robotsIndex: false, robotsFollow: true);
        $html = $this->generator->generate($data);

        $this->assertStringContainsString('noindex', $html);
        $this->assertStringContainsString('follow', $html);
    }

    public function test_generates_canonical(): void
    {
        $data = new SeoData(canonicalUrl: 'https://example.com/about');
        $html = $this->generator->generate($data);

        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('https://example.com/about', $html);
    }
}
