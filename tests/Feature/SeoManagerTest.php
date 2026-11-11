<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Tests\Feature;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Facades\SEO;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Abdurrahaman\LaravelSeo\Services\SeoManager;
use Abdurrahaman\LaravelSeo\Support\HasSeo;
use Abdurrahaman\LaravelSeo\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeoManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
    }

    public function test_can_set_and_render_title(): void
    {
        SEO::setTitle('Hello World');
        $html = (string) SEO::render();

        $this->assertStringContainsString('<title>', $html);
        $this->assertStringContainsString('Hello World', $html);
    }

    public function test_can_save_seo_for_model(): void
    {
        $post = Post::create(['title' => 'My Post']);

        $meta = SEO::for($post)
            ->setTitle('SEO Title')
            ->setDescription('SEO Description')
            ->setKeyword('laravel')
            ->save();

        $this->assertInstanceOf(SeoMeta::class, $meta);
        $this->assertSame('SEO Title', $meta->title);
        $this->assertSame('SEO Description', $meta->description);
        $this->assertSame('laravel', $meta->focus_keyword);
        $this->assertDatabaseHas('seo_meta', [
            'seoable_type' => $post->getMorphClass(),
            'seoable_id' => $post->id,
            'title' => 'SEO Title',
        ]);
    }

    public function test_helper_function_returns_manager(): void
    {
        $manager = laravel_seo();
        $this->assertInstanceOf(SeoManager::class, $manager);
    }

    public function test_has_seo_trait(): void
    {
        $post = Post::create(['title' => 'Trait Post']);
        $this->assertFalse($post->hasSeo());

        $post->updateSeo(['title' => 'From Trait', 'description' => 'Desc']);
        $this->assertTrue($post->fresh()->hasSeo());
        $this->assertSame('From Trait', $post->fresh()->seo->title);
    }

    public function test_social_meta_rendered(): void
    {
        SEO::setTitle('Social Title')
            ->setDescription('Social Desc')
            ->setImage('https://example.com/image.jpg');

        $html = (string) SEO::render();

        $this->assertStringContainsString('og:title', $html);
        $this->assertStringContainsString('twitter:card', $html);
        $this->assertStringContainsString('https://example.com/image.jpg', $html);
    }

    public function test_schema_json_ld_rendered(): void
    {
        SEO::setTitle('Schema Page')->setSchema('Article', ['headline' => 'My Article']);

        $html = (string) SEO::render();

        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('Article', $html);
    }
}

class Post extends Model
{
    use HasSeo;

    protected $guarded = [];
}
