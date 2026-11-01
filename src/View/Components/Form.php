<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\View\Components;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Abdurrahaman\LaravelSeo\Services\SeoAnalyzer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Illuminate\View\View;

class Form extends Component
{
    public ?SeoMeta $seoMeta;

    public array $analysis = [];

    public function __construct(
        public ?Model $model = null,
        public string $content = '',
        public string $namePrefix = 'seo',
        public bool $showAnalysis = true,
    ) {
        $this->seoMeta = $model?->seo ?? null;

        if ($this->showAnalysis && $this->seoMeta) {
            $data = SeoData::fromArray($this->seoMeta->toArray());
            $this->analysis = app(SeoAnalyzer::class)->analyze(
                $data,
                $this->content,
                $this->seoMeta->canonical_url ?? '',
                ''
            );
        }
    }

    public function render(): View
    {
        return view('laravel-seo::components.form');
    }

    public function field(string $key, mixed $default = null): mixed
    {
        if (! $this->seoMeta) {
            return old($this->namePrefix . '.' . $key, $default);
        }

        $value = $this->seoMeta->{$key} ?? $default;

        return old($this->namePrefix . '.' . $key, $value);
    }
}
