<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AiSeoGenerator
{
    /**
     * Generate SEO fields from page content using OpenAI Chat Completions.
     *
     * @return array{title: string, description: string, focus_keyword: string, keywords: array<int, string>}
     */
    public function generate(string $content, ?string $currentTitle = null, ?string $language = null): array
    {
        if (! config('laravel-seo.ai.enabled')) {
            throw new RuntimeException('AI SEO generation is disabled. Set SEO_AI_ENABLED=true in your .env file.');
        }

        $apiKey = (string) config('laravel-seo.ai.api_key', '');
        if ($apiKey === '') {
            throw new RuntimeException('OpenAI API key is missing. Set SEO_AI_API_KEY or OPENAI_API_KEY in your .env file.');
        }

        $content = $this->prepareContent($content);
        if ($content === '') {
            throw new RuntimeException('Page content is empty. Add content to the body/content field first.');
        }

        $language = $language ?: (string) config('laravel-seo.ai.language', 'en');
        $model = (string) config('laravel-seo.ai.model', 'gpt-4o-mini');
        $baseUrl = rtrim((string) config('laravel-seo.ai.base_url', 'https://api.openai.com/v1'), '/');
        $timeout = (int) config('laravel-seo.ai.timeout', 30);

        $system = <<<'PROMPT'
You are an expert SEO copywriter. Given page content, produce optimized SEO metadata.
Return ONLY valid JSON with this exact shape (no markdown, no code fences):
{
  "title": "SEO title, 30-60 characters, include the primary keyword naturally",
  "description": "Meta description, 70-160 characters, compelling and keyword-aware",
  "focus_keyword": "primary focus keyword or short phrase",
  "keywords": ["keyword1", "keyword2", "keyword3", "keyword4", "keyword5"]
}
Rules:
- title max 60 characters preferred
- description max 160 characters preferred
- focus_keyword should appear in title and description when natural
- keywords: 3 to 8 short phrases, no duplicates
- write in the language requested by the user
- do not invent brand claims not supported by the content
PROMPT;

        $user = "Language: {$language}\n";
        if ($currentTitle) {
            $user .= "Current page title (optional context): {$currentTitle}\n";
        }
        $user .= "Page content:\n{$content}";

        $response = Http::baseUrl($baseUrl)
            ->withToken($apiKey)
            ->timeout($timeout)
            ->acceptJson()
            ->post('/chat/completions', [
                'model' => $model,
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new RuntimeException('OpenAI API error: ' . Str::limit((string) $message, 300));
        }

        $raw = (string) data_get($response->json(), 'choices.0.message.content', '');
        $data = json_decode($raw, true);

        if (! is_array($data)) {
            throw new RuntimeException('Failed to parse AI response as JSON.');
        }

        return $this->normalize($data);
    }

    protected function prepareContent(string $content): string
    {
        $content = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = preg_replace('/\s+/u', ' ', $content) ?? $content;
        $content = trim($content);

        $max = (int) config('laravel-seo.ai.max_content_length', 8000);
        if (mb_strlen($content) > $max) {
            $content = mb_substr($content, 0, $max);
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{title: string, description: string, focus_keyword: string, keywords: array<int, string>}
     */
    protected function normalize(array $data): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $focus = trim((string) ($data['focus_keyword'] ?? $data['focusKeyword'] ?? ''));

        $keywords = $data['keywords'] ?? [];
        if (is_string($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }
        if (! is_array($keywords)) {
            $keywords = [];
        }

        $keywords = array_values(array_unique(array_filter(array_map(
            static fn ($k) => trim((string) $k),
            $keywords
        ), static fn ($k) => $k !== '')));

        return [
            'title' => Str::limit($title, 120, ''),
            'description' => Str::limit($description, 320, ''),
            'focus_keyword' => Str::limit($focus, 100, ''),
            'keywords' => array_slice($keywords, 0, 10),
        ];
    }
}
