<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Repositories\Eloquent;

use Abdurrahaman\LaravelSeo\Contracts\SeoRepositoryInterface;
use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoRepository implements SeoRepositoryInterface
{
    public function findForModel(Model $model): ?SeoMeta
    {
        $cacheKey = $this->cacheKey($model);

        if (! config('laravel-seo.cache.enabled')) {
            return $this->queryForModel($model);
        }

        $cached = Cache::get($cacheKey);

        // Drop legacy / corrupt cache (serialized models, incomplete classes)
        if ($cached instanceof \__PHP_Incomplete_Class || $cached instanceof SeoMeta) {
            Cache::forget($cacheKey);
            $cached = null;
        }

        if (is_array($cached)) {
            if ($cached === []) {
                return null;
            }

            return $this->hydrate($cached);
        }

        $seoMeta = $this->queryForModel($model);

        // Cache RAW attributes (JSON columns stay strings)
        Cache::put(
            $cacheKey,
            $seoMeta ? $seoMeta->getAttributes() : [],
            config('laravel-seo.cache.ttl', 3600)
        );

        return $seoMeta;
    }

protected function hydrate(array $attributes): SeoMeta
{
    // Ensure casted JSON columns are strings (not arrays)
    foreach (['keywords', 'schema_data'] as $jsonField) {
        if (array_key_exists($jsonField, $attributes) && is_array($attributes[$jsonField])) {
            $attributes[$jsonField] = json_encode($attributes[$jsonField]);
        }
    }

    $seoMeta = new SeoMeta;
    $seoMeta->setRawAttributes($attributes, true);
    $seoMeta->exists = true;

    return $seoMeta;
}

    public function findById(int $id): ?SeoMeta
    {
        return SeoMeta::query()->find($id);
    }

    public function create(Model $model, SeoData $data): SeoMeta
    {
        $seoMeta = new SeoMeta($data->toArray());
        $seoMeta->seoable_type = $model->getMorphClass();
        $seoMeta->seoable_id = $model->getKey();
        $seoMeta->save();

        $this->clearCache($model);

        return $seoMeta;
    }

    public function update(SeoMeta $seoMeta, SeoData $data): SeoMeta
    {
        $seoMeta->fill($data->toArray());
        $seoMeta->save();

        if ($seoMeta->seoable) {
            $this->clearCache($seoMeta->seoable);
        }

        return $seoMeta->fresh() ?? $seoMeta;
    }

    public function updateOrCreate(Model $model, SeoData $data): SeoMeta
    {
        $seoMeta = SeoMeta::query()->updateOrCreate(
            [
                'seoable_type' => $model->getMorphClass(),
                'seoable_id' => $model->getKey(),
            ],
            $data->toArray()
        );

        $this->clearCache($model);

        return $seoMeta;
    }

    public function delete(SeoMeta $seoMeta): bool
    {
        $model = $seoMeta->seoable;
        $result = (bool) $seoMeta->delete();

        if ($model) {
            $this->clearCache($model);
        }

        return $result;
    }

    public function deleteForModel(Model $model): bool
    {
        $result = (bool) SeoMeta::query()
            ->where('seoable_type', $model->getMorphClass())
            ->where('seoable_id', $model->getKey())
            ->delete();

        $this->clearCache($model);

        return $result;
    }

    protected function queryForModel(Model $model): ?SeoMeta
    {
        return SeoMeta::query()
            ->where('seoable_type', $model->getMorphClass())
            ->where('seoable_id', $model->getKey())
            ->first();
    }



    protected function cacheKey(Model $model): string
    {
        return config('laravel-seo.cache.prefix', 'laravel_seo_')
            . str_replace('\\', '_', $model->getMorphClass())
            . '_'
            . $model->getKey();
    }

    protected function clearCache(Model $model): void
    {
        if (config('laravel-seo.cache.enabled')) {
            Cache::forget($this->cacheKey($model));
        }
    }
}
