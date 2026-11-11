<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default SEO Settings
    |--------------------------------------------------------------------------
    |
    | These values will be used as fallbacks when page-specific SEO
    | metadata is not available.
    |
    */

    'defaults' => [
        'title' => env('SEO_DEFAULT_TITLE', config('app.name', 'Laravel')),
        'description' => env('SEO_DEFAULT_DESCRIPTION', ''),
        'image' => env('SEO_DEFAULT_IMAGE', ''),
        'author' => env('SEO_DEFAULT_AUTHOR', ''),
        'publisher' => env('SEO_DEFAULT_PUBLISHER', ''),
        'language' => env('SEO_DEFAULT_LANGUAGE', 'en'),
        'robots_index' => env('SEO_DEFAULT_ROBOTS_INDEX', true),
        'robots_follow' => env('SEO_DEFAULT_ROBOTS_FOLLOW', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Website Information
    |--------------------------------------------------------------------------
    */

    'site' => [
        'name' => env('SEO_SITE_NAME', config('app.name', 'Laravel')),
        'url' => env('SEO_SITE_URL', config('app.url', 'http://localhost')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Title Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how page titles are generated.
    | Available placeholders: {title}, {site_name}, {separator}
    |
    */

    'title' => [
        'separator' => env('SEO_TITLE_SEPARATOR', '|'),
        'template' => env('SEO_TITLE_TEMPLATE', '{title} {separator} {site_name}'),
        'max_length' => 60,
        'min_length' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Description Configuration
    |--------------------------------------------------------------------------
    */

    'description' => [
        'max_length' => 160,
        'min_length' => 70,
    ],

    /*
    |--------------------------------------------------------------------------
    | Canonical URL Configuration
    |--------------------------------------------------------------------------
    */

    'canonical' => [
        'force_trailing_slash' => false,
        'remove_query_parameters' => true,
        'allowed_query_parameters' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Media Configuration
    |--------------------------------------------------------------------------
    */

    'social' => [
        'twitter' => [
            'site' => env('SEO_TWITTER_SITE', ''),
            'creator' => env('SEO_TWITTER_CREATOR', ''),
            'card' => 'summary_large_image',
        ],
        'facebook' => [
            'app_id' => env('SEO_FACEBOOK_APP_ID', ''),
        ],
        'image' => [
            'width' => 1200,
            'height' => 630,
            'max_size_kb' => 5120,
            'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    */

    'sitemap' => [
        'enabled' => env('SEO_SITEMAP_ENABLED', true),
        'path' => 'sitemap.xml',
        'cache_ttl' => 3600,
        'changefreq' => 'weekly',
        'priority' => 0.5,
        'exclude_noindex' => true,
        'models' => [
            // Example:
            // \App\Models\Post::class => [
            //     'route' => 'posts.show',
            //     'route_params' => ['slug' => 'slug'],
            //     'lastmod' => 'updated_at',
            //     'changefreq' => 'weekly',
            //     'priority' => 0.8,
            // ],
        ],
        'custom_urls' => [
            // ['loc' => '/about', 'lastmod' => null, 'changefreq' => 'monthly', 'priority' => 0.6],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Robots.txt Configuration
    |--------------------------------------------------------------------------
    */

    'robots' => [
        'enabled' => env('SEO_ROBOTS_ENABLED', false),
        'path' => 'robots.txt',
        'content' => null, // Set to a string for custom content, or null for auto-generation
        'allow' => ['/'],
        'disallow' => ['/admin', '/dashboard'],
        'sitemap_url' => null, // Auto-generated if null
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema.org / Structured Data
    |--------------------------------------------------------------------------
    */

    'schema' => [
        'enabled' => true,
        'organization' => [
            'name' => env('SEO_ORG_NAME', config('app.name', 'Laravel')),
            'url' => env('SEO_ORG_URL', config('app.url', 'http://localhost')),
            'logo' => env('SEO_ORG_LOGO', ''),
            'type' => 'Organization',
        ],
        'website' => [
            'name' => env('SEO_SITE_NAME', config('app.name', 'Laravel')),
            'url' => env('SEO_SITE_URL', config('app.url', 'http://localhost')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Analysis Thresholds
    |--------------------------------------------------------------------------
    |
    | These are heuristic guidelines for the SEO analysis meter.
    | They are NOT guaranteed ranking factors.
    |
    */

    'analysis' => [
        'keyword_density' => [
            'min' => 0.5,
            'max' => 2.5,
        ],
        'content_length' => [
            'min' => 300,
            'good' => 600,
        ],
        'title_length' => [
            'min' => 30,
            'max' => 60,
        ],
        'description_length' => [
            'min' => 70,
            'max' => 160,
        ],
        'internal_links' => [
            'min' => 1,
        ],
        'external_links' => [
            'min' => 0,
        ],
        'images_with_alt' => [
            'min_percentage' => 80,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => env('SEO_CACHE_ENABLED', true),
        'ttl' => env('SEO_CACHE_TTL', 3600),
        'prefix' => 'laravel_seo_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */

    'routes' => [
        'enabled' => true,
        'prefix' => '',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets
    |--------------------------------------------------------------------------
    */

    'assets' => [
        'css' => true,
        'js' => true,
        'version' => '1.0.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Keywords
    |--------------------------------------------------------------------------
    |
    | Whether to output the meta keywords tag (largely ignored by search engines).
    |
    */

    'output_keywords_meta' => false,

    /*
    |--------------------------------------------------------------------------
    | AI SEO Generation (OpenAI / ChatGPT)
    |--------------------------------------------------------------------------
    |
    | Generate SEO title, meta description, focus keyword, and keywords
    | from page content via the OpenAI API.
    |
    */

    'ai' => [
        'enabled' => env('SEO_AI_ENABLED', false),
        'api_key' => env('SEO_AI_API_KEY', env('OPENAI_API_KEY', '')),
        'base_url' => env('SEO_AI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('SEO_AI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('SEO_AI_TIMEOUT', 30),
        'max_content_length' => (int) env('SEO_AI_MAX_CONTENT_LENGTH', 8000),
        'language' => env('SEO_AI_LANGUAGE', 'en'),
        // CSS selectors / field names used by the form JS to read page content
        'content_selectors' => [
            '#body',
            'textarea[name="body"]',
            'textarea[name="content"]',
            '.seo-page-content',
            '[data-seo-content]',
        ],
        // Optional middleware for the generate endpoint (in addition to web)
        'middleware' => ['web'],
        'route' => 'laravel-seo/generate',
    ],

];
