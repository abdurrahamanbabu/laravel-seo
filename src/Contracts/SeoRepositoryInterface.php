<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Contracts;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;

interface SeoRepositoryInterface
{
    public function findForModel(Model $model): ?SeoMeta;

    public function findById(int $id): ?SeoMeta;

    public function create(Model $model, SeoData $data): SeoMeta;

    public function update(SeoMeta $seoMeta, SeoData $data): SeoMeta;

    public function updateOrCreate(Model $model, SeoData $data): SeoMeta;

    public function delete(SeoMeta $seoMeta): bool;

    public function deleteForModel(Model $model): bool;
}
