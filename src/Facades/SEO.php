<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Facades;

use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Abdurrahaman\LaravelSeo\Services\SeoManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;

/**
 * @method static SeoManager for(Model $model)
 * @method static SeoManager set(SeoData $data)
 * @method static SeoManager setTitle(?string $title)
 * @method static SeoManager setDescription(?string $description)
 * @method static SeoManager setKeyword(?string $keyword)
 * @method static SeoManager setKeywords(?array $keywords)
 * @method static SeoManager setCanonical(?string $url)
 * @method static SeoManager setImage(?string $image)
 * @method static SeoManager setRobots(?bool $index = null, ?bool $follow = null)
 * @method static SeoManager setSchema(?string $type, ?array $data = null)
 * @method static SeoManager setOgTitle(?string $title)
 * @method static SeoManager setOgDescription(?string $description)
 * @method static SeoManager setOgImage(?string $image)
 * @method static SeoManager setTwitterTitle(?string $title)
 * @method static SeoManager setTwitterDescription(?string $description)
 * @method static SeoManager setTwitterImage(?string $image)
 * @method static SeoData|null getCurrent()
 * @method static Model|null getModel()
 * @method static SeoMeta|null save()
 * @method static HtmlString render()
 * @method static SeoData resolveData()
 * @method static SeoManager|string title(?string $title = null)
 * @method static SeoManager|string|null description(?string $description = null)
 * @method static SeoManager|string|null keyword(?string $keyword = null)
 * @method static SeoManager|string canonical(?string $url = null)
 * @method static SeoManager|string|null image(?string $image = null)
 * @method static SeoManager reset()
 *
 * @see SeoManager
 */
class SEO extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SeoManager::class;
    }
}
