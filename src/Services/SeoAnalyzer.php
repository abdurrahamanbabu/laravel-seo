<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Services;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Illuminate\Support\Str;

class SeoAnalyzer
{
    public const STATUS_GOOD = 'good';
    public const STATUS_OK = 'ok';
    public const STATUS_POOR = 'poor';

    /**
     * Analyze content against focus keyword and SEO data.
     *
     * @return array{
     *     score: int,
     *     status: string,
     *     checks: array<int, array{key: string, status: string, message: string, score: int}>
     * }
     */
    public function analyze(SeoData $data, string $content = '', string $url = '', string $slug = ''): array
    {
        $checks = [];
        $focusKeyword = $data->focusKeyword ? mb_strtolower(trim($data->focusKeyword)) : null;
        $plainContent = $this->stripHtml($content);
        $title = $data->title ?? '';
        $description = $data->description ?? '';

        // 1. Focus keyword in SEO title
        $checks[] = $this->checkKeywordInTitle($title, $focusKeyword);

        // 2. Focus keyword in meta description
        $checks[] = $this->checkKeywordInDescription($description, $focusKeyword);

        // 3. Focus keyword in URL/slug
        $checks[] = $this->checkKeywordInSlug($slug ?: $url, $focusKeyword);

        // 4. Focus keyword in first paragraph
        $checks[] = $this->checkKeywordInFirstParagraph($content, $focusKeyword);

        // 5. Focus keyword in headings
        $checks[] = $this->checkKeywordInHeadings($content, $focusKeyword);

        // 6. Focus keyword in content
        $checks[] = $this->checkKeywordInContent($plainContent, $focusKeyword);

        // 7. Keyword density
        $checks[] = $this->checkKeywordDensity($plainContent, $focusKeyword);

        // 8. Content length
        $checks[] = $this->checkContentLength($plainContent);

        // 9. SEO title length
        $checks[] = $this->checkTitleLength($title);

        // 10. Meta description length
        $checks[] = $this->checkDescriptionLength($description);

        // 11. Internal links
        $checks[] = $this->checkInternalLinks($content);

        // 12. External links
        $checks[] = $this->checkExternalLinks($content);

        // 13. Image alt attributes
        $checks[] = $this->checkImageAlts($content);

        // 14. Multiple H1 detection
        $checks[] = $this->checkMultipleH1($content);

        // 15. Missing meta description
        $checks[] = $this->checkMissingDescription($description);

        // 16. Missing canonical
        $checks[] = $this->checkMissingCanonical($data->canonicalUrl);

        // 17. Readability (simple sentence length heuristic)
        $checks[] = $this->checkReadability($plainContent);

        $totalScore = 0;
        $maxScore = 0;

        foreach ($checks as $check) {
            $totalScore += $check['score'];
            $maxScore += 10; // each check max 10
        }

        $percentage = $maxScore > 0 ? (int) round(($totalScore / $maxScore) * 100) : 0;

        $status = match (true) {
            $percentage >= 70 => self::STATUS_GOOD,
            $percentage >= 40 => self::STATUS_OK,
            default => self::STATUS_POOR,
        };

        return [
            'score' => $percentage,
            'status' => $status,
            'checks' => $checks,
        ];
    }

    protected function checkKeywordInTitle(string $title, ?string $keyword): array
    {
        if (! $keyword) {
            return $this->result('keyword_in_title', self::STATUS_POOR, 'No focus keyword set.', 0);
        }

        if (empty($title)) {
            return $this->result('keyword_in_title', self::STATUS_POOR, 'SEO title is empty.', 0);
        }

        if (Str::contains(mb_strtolower($title), $keyword)) {
            return $this->result('keyword_in_title', self::STATUS_GOOD, 'Focus keyword appears in the SEO title.', 10);
        }

        return $this->result('keyword_in_title', self::STATUS_POOR, 'Focus keyword does not appear in the SEO title.', 0);
    }

    protected function checkKeywordInDescription(string $description, ?string $keyword): array
    {
        if (! $keyword) {
            return $this->result('keyword_in_description', self::STATUS_POOR, 'No focus keyword set.', 0);
        }

        if (empty($description)) {
            return $this->result('keyword_in_description', self::STATUS_POOR, 'Meta description is empty.', 0);
        }

        if (Str::contains(mb_strtolower($description), $keyword)) {
            return $this->result('keyword_in_description', self::STATUS_GOOD, 'Focus keyword appears in the meta description.', 10);
        }

        return $this->result('keyword_in_description', self::STATUS_OK, 'Focus keyword does not appear in the meta description.', 3);
    }

    protected function checkKeywordInSlug(string $slugOrUrl, ?string $keyword): array
    {
        if (! $keyword) {
            return $this->result('keyword_in_slug', self::STATUS_POOR, 'No focus keyword set.', 0);
        }

        $slug = $slugOrUrl;
        if (str_contains($slugOrUrl, '://')) {
            $path = parse_url($slugOrUrl, PHP_URL_PATH) ?? '';
            $slug = basename($path) ?: $path;
        }

        $normalizedSlug = str_replace(['-', '_'], ' ', mb_strtolower($slug));
        $normalizedKeyword = str_replace(['-', '_'], ' ', $keyword);

        if (Str::contains($normalizedSlug, $normalizedKeyword) || Str::contains(mb_strtolower($slug), str_replace(' ', '-', $keyword))) {
            return $this->result('keyword_in_slug', self::STATUS_GOOD, 'Focus keyword appears in the URL slug.', 10);
        }

        return $this->result('keyword_in_slug', self::STATUS_OK, 'Focus keyword does not appear in the URL slug.', 3);
    }

    protected function checkKeywordInFirstParagraph(string $content, ?string $keyword): array
    {
        if (! $keyword) {
            return $this->result('keyword_in_first_paragraph', self::STATUS_POOR, 'No focus keyword set.', 0);
        }

        if (empty(trim(strip_tags($content)))) {
            return $this->result('keyword_in_first_paragraph', self::STATUS_POOR, 'Content is empty.', 0);
        }

        // Extract first paragraph
        if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $content, $matches)) {
            $first = mb_strtolower(strip_tags($matches[1]));
        } else {
            $plain = $this->stripHtml($content);
            $first = mb_substr($plain, 0, 300);
        }

        if (Str::contains($first, $keyword)) {
            return $this->result('keyword_in_first_paragraph', self::STATUS_GOOD, 'Focus keyword appears in the first paragraph.', 10);
        }

        return $this->result('keyword_in_first_paragraph', self::STATUS_OK, 'Focus keyword does not appear in the first paragraph.', 3);
    }

    protected function checkKeywordInHeadings(string $content, ?string $keyword): array
    {
        if (! $keyword) {
            return $this->result('keyword_in_headings', self::STATUS_POOR, 'No focus keyword set.', 0);
        }

        if (preg_match_all('/<h([1-3])[^>]*>(.*?)<\/h\1>/is', $content, $matches)) {
            foreach ($matches[2] as $heading) {
                if (Str::contains(mb_strtolower(strip_tags($heading)), $keyword)) {
                    return $this->result('keyword_in_headings', self::STATUS_GOOD, 'Focus keyword appears in at least one heading (H1–H3).', 10);
                }
            }
        }

        return $this->result('keyword_in_headings', self::STATUS_OK, 'Focus keyword does not appear in headings (H1–H3).', 3);
    }

    protected function checkKeywordInContent(string $plainContent, ?string $keyword): array
    {
        if (! $keyword) {
            return $this->result('keyword_in_content', self::STATUS_POOR, 'No focus keyword set.', 0);
        }

        if (empty($plainContent)) {
            return $this->result('keyword_in_content', self::STATUS_POOR, 'Content is empty.', 0);
        }

        if (Str::contains(mb_strtolower($plainContent), $keyword)) {
            return $this->result('keyword_in_content', self::STATUS_GOOD, 'Focus keyword appears in the content.', 10);
        }

        return $this->result('keyword_in_content', self::STATUS_POOR, 'Focus keyword does not appear in the content.', 0);
    }

    protected function checkKeywordDensity(string $plainContent, ?string $keyword): array
    {
        if (! $keyword || empty($plainContent)) {
            return $this->result('keyword_density', self::STATUS_POOR, 'Cannot calculate keyword density.', 0);
        }

        $words = preg_split('/\s+/u', mb_strtolower($plainContent), -1, PREG_SPLIT_NO_EMPTY);
        $totalWords = count($words);

        if ($totalWords === 0) {
            return $this->result('keyword_density', self::STATUS_POOR, 'Content has no words.', 0);
        }

        $keywordCount = 0;
        $keywordWords = preg_split('/\s+/u', $keyword);
        $keywordLen = count($keywordWords);

        if ($keywordLen === 1) {
            $keywordCount = count(array_filter($words, fn ($w) => $w === $keyword));
        } else {
            // Phrase density
            $contentLower = mb_strtolower($plainContent);
            $keywordCount = substr_count($contentLower, $keyword);
        }

        $density = ($keywordCount / $totalWords) * 100;
        $min = config('laravel-seo.analysis.keyword_density.min', 0.5);
        $max = config('laravel-seo.analysis.keyword_density.max', 2.5);

        if ($density >= $min && $density <= $max) {
            return $this->result('keyword_density', self::STATUS_GOOD, sprintf('Keyword density is %.1f%% (optimal range: %.1f%%–%.1f%%).', $density, $min, $max), 10);
        }

        if ($density < $min) {
            return $this->result('keyword_density', self::STATUS_OK, sprintf('Keyword density is %.1f%% (below recommended %.1f%%).', $density, $min), 5);
        }

        return $this->result('keyword_density', self::STATUS_POOR, sprintf('Keyword density is %.1f%% (above recommended %.1f%%). Possible keyword stuffing.', $density, $max), 2);
    }

    protected function checkContentLength(string $plainContent): array
    {
        $wordCount = str_word_count($plainContent);
        $min = config('laravel-seo.analysis.content_length.min', 300);
        $good = config('laravel-seo.analysis.content_length.good', 600);

        if ($wordCount >= $good) {
            return $this->result('content_length', self::STATUS_GOOD, sprintf('Content length is %d words (good).', $wordCount), 10);
        }

        if ($wordCount >= $min) {
            return $this->result('content_length', self::STATUS_OK, sprintf('Content length is %d words (acceptable, aim for %d+).', $wordCount, $good), 6);
        }

        return $this->result('content_length', self::STATUS_POOR, sprintf('Content length is %d words (too short, aim for %d+).', $wordCount, $min), 2);
    }

    protected function checkTitleLength(string $title): array
    {
        $len = mb_strlen($title);
        $min = config('laravel-seo.analysis.title_length.min', 30);
        $max = config('laravel-seo.analysis.title_length.max', 60);

        if ($len === 0) {
            return $this->result('title_length', self::STATUS_POOR, 'SEO title is empty.', 0);
        }

        if ($len >= $min && $len <= $max) {
            return $this->result('title_length', self::STATUS_GOOD, sprintf('SEO title length is %d characters (optimal).', $len), 10);
        }

        if ($len < $min) {
            return $this->result('title_length', self::STATUS_OK, sprintf('SEO title is too short (%d chars). Aim for %d–%d.', $len, $min, $max), 5);
        }

        return $this->result('title_length', self::STATUS_OK, sprintf('SEO title is too long (%d chars). Aim for %d–%d.', $len, $min, $max), 5);
    }

    protected function checkDescriptionLength(string $description): array
    {
        $len = mb_strlen($description);
        $min = config('laravel-seo.analysis.description_length.min', 70);
        $max = config('laravel-seo.analysis.description_length.max', 160);

        if ($len === 0) {
            return $this->result('description_length', self::STATUS_POOR, 'Meta description is empty.', 0);
        }

        if ($len >= $min && $len <= $max) {
            return $this->result('description_length', self::STATUS_GOOD, sprintf('Meta description length is %d characters (optimal).', $len), 10);
        }

        if ($len < $min) {
            return $this->result('description_length', self::STATUS_OK, sprintf('Meta description is too short (%d chars). Aim for %d–%d.', $len, $min, $max), 5);
        }

        return $this->result('description_length', self::STATUS_OK, sprintf('Meta description is too long (%d chars). Aim for %d–%d.', $len, $min, $max), 5);
    }

    protected function checkInternalLinks(string $content): array
    {
        $count = preg_match_all('/<a\s[^>]*href=["\'](?!https?:\/\/|mailto:|tel:|#)[^"\']+["\']/i', $content);
        $min = config('laravel-seo.analysis.internal_links.min', 1);

        if ($count >= $min) {
            return $this->result('internal_links', self::STATUS_GOOD, sprintf('Found %d internal link(s).', $count), 10);
        }

        return $this->result('internal_links', self::STATUS_OK, 'No internal links found. Consider adding some.', 4);
    }

    protected function checkExternalLinks(string $content): array
    {
        $count = preg_match_all('/<a\s[^>]*href=["\']https?:\/\/[^"\']+["\']/i', $content);

        if ($count > 0) {
            return $this->result('external_links', self::STATUS_GOOD, sprintf('Found %d external link(s).', $count), 8);
        }

        return $this->result('external_links', self::STATUS_OK, 'No external links found.', 5);
    }

    protected function checkImageAlts(string $content): array
    {
        preg_match_all('/<img\s[^>]*>/i', $content, $images);
        $total = count($images[0]);

        if ($total === 0) {
            return $this->result('image_alts', self::STATUS_OK, 'No images found in content.', 5);
        }

        $withAlt = 0;
        foreach ($images[0] as $img) {
            if (preg_match('/alt\s*=\s*["\'][^"\']+["\']/i', $img)) {
                $withAlt++;
            }
        }

        $percentage = ($withAlt / $total) * 100;
        $minPct = config('laravel-seo.analysis.images_with_alt.min_percentage', 80);

        if ($percentage >= $minPct) {
            return $this->result('image_alts', self::STATUS_GOOD, sprintf('%d of %d images have alt attributes (%.0f%%).', $withAlt, $total, $percentage), 10);
        }

        return $this->result('image_alts', self::STATUS_OK, sprintf('Only %d of %d images have alt attributes (%.0f%%). Aim for %d%%+.', $withAlt, $total, $percentage, $minPct), 4);
    }

    protected function checkMultipleH1(string $content): array
    {
        $count = preg_match_all('/<h1[^>]*>/i', $content);

        if ($count === 0) {
            return $this->result('multiple_h1', self::STATUS_OK, 'No H1 heading found. Consider adding one.', 5);
        }

        if ($count === 1) {
            return $this->result('multiple_h1', self::STATUS_GOOD, 'Exactly one H1 heading found.', 10);
        }

        return $this->result('multiple_h1', self::STATUS_POOR, sprintf('Found %d H1 headings. Prefer a single H1 per page.', $count), 2);
    }

    protected function checkMissingDescription(string $description): array
    {
        if (! empty(trim($description))) {
            return $this->result('missing_description', self::STATUS_GOOD, 'Meta description is present.', 10);
        }

        return $this->result('missing_description', self::STATUS_POOR, 'Meta description is missing.', 0);
    }

    protected function checkMissingCanonical(?string $canonical): array
    {
        // Canonical is auto-generated, so this is informational
        if ($canonical) {
            return $this->result('missing_canonical', self::STATUS_GOOD, 'Custom canonical URL is set.', 10);
        }

        return $this->result('missing_canonical', self::STATUS_GOOD, 'Canonical URL will be auto-generated.', 8);
    }

    protected function checkReadability(string $plainContent): array
    {
        if (empty($plainContent)) {
            return $this->result('readability', self::STATUS_POOR, 'No content to analyze for readability.', 0);
        }

        $sentences = preg_split('/[.!?]+/u', $plainContent, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count(array_filter($sentences, fn ($s) => mb_strlen(trim($s)) > 0));

        if ($sentenceCount === 0) {
            return $this->result('readability', self::STATUS_OK, 'Unable to analyze readability.', 5);
        }

        $words = str_word_count($plainContent);
        $avgWordsPerSentence = $words / $sentenceCount;

        if ($avgWordsPerSentence <= 20) {
            return $this->result('readability', self::STATUS_GOOD, sprintf('Average sentence length is %.1f words (easy to read).', $avgWordsPerSentence), 10);
        }

        if ($avgWordsPerSentence <= 25) {
            return $this->result('readability', self::STATUS_OK, sprintf('Average sentence length is %.1f words (acceptable).', $avgWordsPerSentence), 6);
        }

        return $this->result('readability', self::STATUS_OK, sprintf('Average sentence length is %.1f words (consider shorter sentences).', $avgWordsPerSentence), 4);
    }

    protected function result(string $key, string $status, string $message, int $score): array
    {
        return [
            'key' => $key,
            'status' => $status,
            'message' => $message,
            'score' => $score,
        ];
    }

    protected function stripHtml(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
