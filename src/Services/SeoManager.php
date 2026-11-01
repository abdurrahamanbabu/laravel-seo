<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Services;

use Abdurrahaman\LaravelSeo\Contracts\SeoRepositoryInterface;
use Abdurrahaman\LaravelSeo\DTOs\SeoData;
use Abdurrahaman\LaravelSeo\Models\SeoMeta;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class SeoManager implements Htmlable
{
    protected ?SeoData $current = null;

    protected ?Model $model = null;

    protected array $extra = [];

    public function __construct(
        protected SeoRepositoryInterface $repository,
        protected MetaTagGenerator $metaTagGenerator,
        protected SocialMetaGenerator $socialMetaGenerator,
        protected SchemaGenerator $schemaGenerator,
    ) {}

    public function for(Model $model): self
    {
        $this->model = $model;
        $seoMeta = $this->repository->findForModel($model);

        if ($seoMeta) {
            $this->current = SeoData::fromArray($seoMeta->toArray());
        } else {
            $this->current = new SeoData();
        }

        return $this;
    }

    public function set(SeoData $data): self
    {
        if ($this->current) {
            $this->current = $this->current->merge($data);
        } else {
            $this->current = $data;
        }

        return $this;
    }

    public function setTitle(?string $title): self
    {
        return $this->set(new SeoData(title: $title));
    }

    public function setDescription(?string $description): self
    {
        return $this->set(new SeoData(description: $description));
    }

    public function setKeyword(?string $keyword): self
    {
        return $this->set(new SeoData(focusKeyword: $keyword));
    }

    public function setKeywords(?array $keywords): self
    {
        return $this->set(new SeoData(keywords: $keywords));
    }

    public function setCanonical(?string $url): self
    {
        return $this->set(new SeoData(canonicalUrl: $url));
    }

    public function setImage(?string $image): self
    {
        return $this->set(new SeoData(ogImage: $image, twitterImage: $image));
    }

    public function setRobots(?bool $index = null, ?bool $follow = null): self
    {
        return $this->set(new SeoData(robotsIndex: $index, robotsFollow: $follow));
    }

    public function setSchema(?string $type, ?array $data = null): self
    {
        return $this->set(new SeoData(schemaType: $type, schemaData: $data));
    }

    public function setOgTitle(?string $title): self
    {
        return $this->set(new SeoData(ogTitle: $title));
    }

    public function setOgDescription(?string $description): self
    {
        return $this->set(new SeoData(ogDescription: $description));
    }

    public function setOgImage(?string $image): self
    {
        return $this->set(new SeoData(ogImage: $image));
    }

    public function setTwitterTitle(?string $title): self
    {
        return $this->set(new SeoData(twitterTitle: $title));
    }

    public function setTwitterDescription(?string $description): self
    {
        return $this->set(new SeoData(twitterDescription: $description));
    }

    public function setTwitterImage(?string $image): self
    {
        return $this->set(new SeoData(twitterImage: $image));
    }

    public function getCurrent(): ?SeoData
    {
        return $this->current;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function save(): ?SeoMeta
    {
        if (! $this->model || ! $this->current) {
            return null;
        }

        return $this->repository->updateOrCreate($this->model, $this->current);
    }

    public function render(): HtmlString
    {
        $data = $this->resolveData();

        $html = [];

        $html[] = $this->metaTagGenerator->generate($data);
        $html[] = $this->socialMetaGenerator->generate($data);
        $html[] = $this->schemaGenerator->generate($data, $this->model);

        return new HtmlString(implode("\n", array_filter($html)));
    }

    public function resolveData(): SeoData
    {
        $defaults = new SeoData(
            title: config('laravel-seo.defaults.title'),
            description: config('laravel-seo.defaults.description'),
            robotsIndex: config('laravel-seo.defaults.robots_index', true),
            robotsFollow: config('laravel-seo.defaults.robots_follow', true),
            author: config('laravel-seo.defaults.author'),
            publisher: config('laravel-seo.defaults.publisher'),
            language: config('laravel-seo.defaults.language'),
            ogImage: config('laravel-seo.defaults.image'),
            twitterImage: config('laravel-seo.defaults.image'),
        );

        if ($this->current) {
            return $defaults->merge($this->current);
        }

        return $defaults;
    }

    public function title(?string $title = null): self|string
    {
        if ($title !== null) {
            return $this->setTitle($title);
        }

        return $this->metaTagGenerator->buildTitle($this->resolveData());
    }

    public function description(?string $description = null): self|string|null
    {
        if ($description !== null) {
            return $this->setDescription($description);
        }

        return $this->resolveData()->description;
    }

    public function keyword(?string $keyword = null): self|string|null
    {
        if ($keyword !== null) {
            return $this->setKeyword($keyword);
        }

        return $this->resolveData()->focusKeyword;
    }

    public function canonical(?string $url = null): self|string
    {
        if ($url !== null) {
            return $this->setCanonical($url);
        }

        return $this->metaTagGenerator->buildCanonical($this->resolveData());
    }

    public function image(?string $image = null): self|string|null
    {
        if ($image !== null) {
            return $this->setImage($image);
        }

        return $this->resolveData()->ogImage;
    }

    public function reset(): self
    {
        $this->current = null;
        $this->model = null;
        $this->extra = [];

        return $this;
    }

    /**
     * Allow {{ laravel_seo() }} in Blade without htmlspecialchars errors.
     * Htmlable is treated as safe HTML by Laravel's e() helper.
     */
    public function toHtml(): string
    {
        return (string) $this->render();
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
