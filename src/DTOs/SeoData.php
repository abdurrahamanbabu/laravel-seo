<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\DTOs;

final class SeoData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $focusKeyword = null,
        public readonly ?array $keywords = null,
        public readonly ?string $canonicalUrl = null,
        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
        public readonly ?string $ogImage = null,
        public readonly ?string $twitterTitle = null,
        public readonly ?string $twitterDescription = null,
        public readonly ?string $twitterImage = null,
        public readonly ?bool $robotsIndex = null,
        public readonly ?bool $robotsFollow = null,
        public readonly ?string $schemaType = null,
        public readonly ?array $schemaData = null,
        public readonly ?bool $excludeFromSitemap = null,
        public readonly ?string $author = null,
        public readonly ?string $publisher = null,
        public readonly ?string $language = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            focusKeyword: $data['focus_keyword'] ?? $data['focusKeyword'] ?? null,
            keywords: isset($data['keywords']) ? (array) $data['keywords'] : null,
            canonicalUrl: $data['canonical_url'] ?? $data['canonicalUrl'] ?? null,
            ogTitle: $data['og_title'] ?? $data['ogTitle'] ?? null,
            ogDescription: $data['og_description'] ?? $data['ogDescription'] ?? null,
            ogImage: $data['og_image'] ?? $data['ogImage'] ?? null,
            twitterTitle: $data['twitter_title'] ?? $data['twitterTitle'] ?? null,
            twitterDescription: $data['twitter_description'] ?? $data['twitterDescription'] ?? null,
            twitterImage: $data['twitter_image'] ?? $data['twitterImage'] ?? null,
            robotsIndex: isset($data['robots_index']) ? (bool) $data['robots_index'] : (isset($data['robotsIndex']) ? (bool) $data['robotsIndex'] : null),
            robotsFollow: isset($data['robots_follow']) ? (bool) $data['robots_follow'] : (isset($data['robotsFollow']) ? (bool) $data['robotsFollow'] : null),
            schemaType: $data['schema_type'] ?? $data['schemaType'] ?? null,
            schemaData: isset($data['schema_data']) ? (array) $data['schema_data'] : (isset($data['schemaData']) ? (array) $data['schemaData'] : null),
            excludeFromSitemap: isset($data['exclude_from_sitemap']) ? (bool) $data['exclude_from_sitemap'] : (isset($data['excludeFromSitemap']) ? (bool) $data['excludeFromSitemap'] : null),
            author: $data['author'] ?? null,
            publisher: $data['publisher'] ?? null,
            language: $data['language'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'focus_keyword' => $this->focusKeyword,
            'keywords' => $this->keywords,
            'canonical_url' => $this->canonicalUrl,
            'og_title' => $this->ogTitle,
            'og_description' => $this->ogDescription,
            'og_image' => $this->ogImage,
            'twitter_title' => $this->twitterTitle,
            'twitter_description' => $this->twitterDescription,
            'twitter_image' => $this->twitterImage,
            'robots_index' => $this->robotsIndex,
            'robots_follow' => $this->robotsFollow,
            'schema_type' => $this->schemaType,
            'schema_data' => $this->schemaData,
            'exclude_from_sitemap' => $this->excludeFromSitemap,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'language' => $this->language,
        ], fn ($value) => $value !== null);
    }

    public function merge(self $other): self
    {
        return new self(
            title: $other->title ?? $this->title,
            description: $other->description ?? $this->description,
            focusKeyword: $other->focusKeyword ?? $this->focusKeyword,
            keywords: $other->keywords ?? $this->keywords,
            canonicalUrl: $other->canonicalUrl ?? $this->canonicalUrl,
            ogTitle: $other->ogTitle ?? $this->ogTitle,
            ogDescription: $other->ogDescription ?? $this->ogDescription,
            ogImage: $other->ogImage ?? $this->ogImage,
            twitterTitle: $other->twitterTitle ?? $this->twitterTitle,
            twitterDescription: $other->twitterDescription ?? $this->twitterDescription,
            twitterImage: $other->twitterImage ?? $this->twitterImage,
            robotsIndex: $other->robotsIndex ?? $this->robotsIndex,
            robotsFollow: $other->robotsFollow ?? $this->robotsFollow,
            schemaType: $other->schemaType ?? $this->schemaType,
            schemaData: $other->schemaData ?? $this->schemaData,
            excludeFromSitemap: $other->excludeFromSitemap ?? $this->excludeFromSitemap,
            author: $other->author ?? $this->author,
            publisher: $other->publisher ?? $this->publisher,
            language: $other->language ?? $this->language,
        );
    }
}
