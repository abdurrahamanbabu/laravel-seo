<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->json('keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('schema_type')->nullable();
            $table->json('schema_data')->nullable();
            $table->boolean('exclude_from_sitemap')->default(false);
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('language', 10)->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id'], 'seo_meta_seoable_unique');
            $table->index('focus_keyword');
            $table->index('robots_index');
            $table->index('exclude_from_sitemap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
