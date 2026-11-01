<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Services;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;

class SocialMetaGenerator
{
    public function generate(SeoData $data): string
    {
        $tags = [];

        // Open Graph
        $ogTitle = $data->ogTitle ?: $data->title;
        $ogDescription = $data->ogDescription ?: $data->description;
        $ogImage = $data->ogImage ?: config('laravel-seo.defaults.image');
        $ogUrl = $data->canonicalUrl ?: url()->current();
        $siteName = config('laravel-seo.site.name', config('app.name'));

        $tags[] = '<meta property="og:type" content="website">';
        $tags[] = '<meta property="og:site_name" content="' . e($siteName) . '">';

        if ($ogTitle) {
            $tags[] = '<meta property="og:title" content="' . e($ogTitle) . '">';
        }

        if ($ogDescription) {
            $tags[] = '<meta property="og:description" content="' . e($this->truncate($ogDescription, 300)) . '">';
        }

        if ($ogUrl) {
            $tags[] = '<meta property="og:url" content="' . e($ogUrl) . '">';
        }

        if ($ogImage) {
            $tags[] = '<meta property="og:image" content="' . e($this->absoluteUrl($ogImage)) . '">';
            $width = config('laravel-seo.social.image.width', 1200);
            $height = config('laravel-seo.social.image.height', 630);
            $tags[] = '<meta property="og:image:width" content="' . e((string) $width) . '">';
            $tags[] = '<meta property="og:image:height" content="' . e((string) $height) . '">';
        }

        $locale = $data->language ?? config('laravel-seo.defaults.language', 'en');
        $tags[] = '<meta property="og:locale" content="' . e(str_replace('-', '_', $locale)) . '">';

        $fbAppId = config('laravel-seo.social.facebook.app_id');
        if ($fbAppId) {
            $tags[] = '<meta property="fb:app_id" content="' . e($fbAppId) . '">';
        }

        // Twitter / X Cards
        $twitterTitle = $data->twitterTitle ?: $ogTitle;
        $twitterDescription = $data->twitterDescription ?: $ogDescription;
        $twitterImage = $data->twitterImage ?: $ogImage;
        $card = config('laravel-seo.social.twitter.card', 'summary_large_image');

        $tags[] = '<meta name="twitter:card" content="' . e($card) . '">';

        $twitterSite = config('laravel-seo.social.twitter.site');
        if ($twitterSite) {
            $tags[] = '<meta name="twitter:site" content="' . e($twitterSite) . '">';
        }

        $twitterCreator = config('laravel-seo.social.twitter.creator');
        if ($twitterCreator) {
            $tags[] = '<meta name="twitter:creator" content="' . e($twitterCreator) . '">';
        }

        if ($twitterTitle) {
            $tags[] = '<meta name="twitter:title" content="' . e($twitterTitle) . '">';
        }

        if ($twitterDescription) {
            $tags[] = '<meta name="twitter:description" content="' . e($this->truncate($twitterDescription, 200)) . '">';
        }

        if ($twitterImage) {
            $tags[] = '<meta name="twitter:image" content="' . e($this->absoluteUrl($twitterImage)) . '">';
        }

        return implode("\n", $tags);
    }

    protected function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    protected function truncate(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 3) . '...';
    }
}
