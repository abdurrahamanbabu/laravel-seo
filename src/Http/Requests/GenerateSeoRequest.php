<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Host apps can wrap the route with auth middleware via config.
        return true;
    }

    public function rules(): array
    {
        $max = (int) config('laravel-seo.ai.max_content_length', 8000);

        return [
            'content' => ['required', 'string', 'min:20', 'max:' . max($max + 2000, 10000)],
            'title' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Page content is required to generate SEO fields.',
            'content.min' => 'Page content is too short to generate meaningful SEO data.',
        ];
    }
}
