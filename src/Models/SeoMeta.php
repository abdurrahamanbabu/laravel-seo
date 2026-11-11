<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'title',
        'description',
        'focus_keyword',
        'keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'robots_index',
        'robots_follow',
        'schema_type',
        'schema_data',
        'exclude_from_sitemap',
        'author',
        'publisher',
        'language',
    ];

    protected $casts = [
        'keywords' => 'array',
        'schema_data' => 'array',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'exclude_from_sitemap' => 'boolean',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getEffectiveTitleAttribute(): ?string
    {
        return $this->title;
    }

    public function getEffectiveDescriptionAttribute(): ?string
    {
        return $this->description;
    }

    public function getEffectiveOgTitleAttribute(): ?string
    {
        return $this->og_title ?: $this->title;
    }

    public function getEffectiveOgDescriptionAttribute(): ?string
    {
        return $this->og_description ?: $this->description;
    }

    public function getEffectiveOgImageAttribute(): ?string
    {
        return $this->og_image;
    }

    public function getEffectiveTwitterTitleAttribute(): ?string
    {
        return $this->twitter_title ?: $this->og_title ?: $this->title;
    }

    public function getEffectiveTwitterDescriptionAttribute(): ?string
    {
        return $this->twitter_description ?: $this->og_description ?: $this->description;
    }

    public function getEffectiveTwitterImageAttribute(): ?string
    {
        return $this->twitter_image ?: $this->og_image;
    }
}
