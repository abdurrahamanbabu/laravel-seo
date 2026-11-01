<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Host application should apply policies as needed
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'focus_keyword' => ['nullable', 'string', 'max:100'],
            'keywords' => ['nullable'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:500'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:500'],
            'twitter_image' => ['nullable', 'string', 'max:500'],
            'robots_index' => ['nullable', 'boolean'],
            'robots_follow' => ['nullable', 'boolean'],
            'schema_type' => ['nullable', 'string', 'max:50'],
            'schema_data' => ['nullable'],
            'exclude_from_sitemap' => ['nullable', 'boolean'],
            'author' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $keywords = $this->input('keywords');

        if (is_string($keywords)) {
            $keywords = array_values(array_filter(array_map('trim', explode(',', $keywords))));
            $this->merge(['keywords' => $keywords]);
        }

        $schemaData = $this->input('schema_data');
        if (is_string($schemaData) && $schemaData !== '') {
            $decoded = json_decode($schemaData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['schema_data' => $decoded]);
            }
        }

        // Normalize checkboxes
        $this->merge([
            'robots_index' => $this->boolean('robots_index'),
            'robots_follow' => $this->boolean('robots_follow'),
            'exclude_from_sitemap' => $this->boolean('exclude_from_sitemap'),
        ]);
    }
}
