<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Support;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Abdurrahaman\LaravelSeo\Services\SeoManager;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function getSeoMeta(): ?SeoMeta
    {
        return $this->seo;
    }

    public function updateSeo(array|SeoData $data): SeoMeta
    {
        $dto = $data instanceof SeoData ? $data : SeoData::fromArray($data);

        return app(SeoManager::class)->for($this)->set($dto)->save();
    }

    public function deleteSeo(): bool
    {
        return (bool) $this->seo()?->delete();
    }

    public function hasSeo(): bool
    {
        return $this->seo()->exists();
    }
}
