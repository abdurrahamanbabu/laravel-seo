# Laravel SEO

SEO management package for Laravel (meta tags, social cards, sitemap, schema, analysis form).

**Package:** `abdur-rahaman/laravel-seo`  
**Requirements:** PHP 8.3+, Laravel 11 / 12 / 13

---

## Install

```bash
composer require abdur-rahaman/laravel-seo
```

```bash
php artisan seo:install
php artisan migrate
```

Publish assets only (CSS/JS for the admin form):

```bash
php artisan seo:publish-assets
```

---

## Initial

### 1. Config (optional)

```bash
php artisan vendor:publish --tag=laravel-seo-config
```

Set defaults in `config/laravel-seo.php` or `.env`:

```env
SEO_DEFAULT_TITLE="${APP_NAME}"
SEO_DEFAULT_DESCRIPTION="Your default meta description"
SEO_SITE_NAME="${APP_NAME}"
SEO_SITE_URL="${APP_URL}"
```

### 2. Admin layout assets

The SEO form needs **Bootstrap 5**, **Font Awesome**, and **jQuery** in your admin layout. Package CSS/JS:

```blade
<link rel="stylesheet" href="{{ asset('vendor/laravel-seo/css/laravel-seo.css') }}">
{{-- after jQuery + Bootstrap --}}
<script src="{{ asset('vendor/laravel-seo/js/laravel-seo.js') }}"></script>
```

Or rely on `@stack('styles')` / `@stack('scripts')` if your layout already yields them (the form component pushes these stacks).

---

## Load input fields and plugins in form

Add the SEO form component to any create/edit view:

```blade
<form method="POST" action="{{ route('posts.update', $post) }}">
    @csrf
    @method('PUT')

    {{-- your normal fields (title, body, etc.) --}}

    <x-laravel-seo::form
        :model="$post"
        :content="$post->body ?? ''"
        name-prefix="seo"
    />

    <button type="submit">Save</button>
</form>
```

### Form tabs & fields

| Tab | Fields |
|-----|--------|
| **General SEO** | Focus keyword, SEO title, meta description, additional keywords, canonical URL, Google preview |
| **Social Media** | OG title / description / image, Twitter title / description / image |
| **Advanced** | Robots index/follow, exclude from sitemap, canonical |
| **Schema** | Schema type, custom JSON-LD data |
| **Analysis** | SEO score + keyword / content checks |

### Save SEO from the controller

```php
use Abdurrahaman\LaravelSeo\DTOs\SeoData;

public function update(Request $request, Post $post)
{
    $data = $request->validate([
        'title' => ['required', 'string'],
        'body'  => ['nullable', 'string'],
        'seo'   => ['nullable', 'array'],
    ]);

    $post->update(collect($data)->except('seo')->all());

    if (! empty($data['seo'])) {
        $post->updateSeo(SeoData::fromArray($data['seo']));
    }

    return back()->with('success', 'Saved.');
}
```

Input names use the prefix: `seo[title]`, `seo[description]`, `seo[focus_keyword]`, etc.

For a new model without SEO yet:

```blade
<x-laravel-seo::form
    :content="old('body', '')"
    name-prefix="seo"
/>
```

---

## Setup in model

Use the `HasSeo` trait on any Eloquent model:

```php
<?php

namespace App\Models;

use Abdurrahaman\LaravelSeo\Support\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSeo;

    protected $fillable = ['title', 'slug', 'body'];
}
```

### API on the model

```php
// Relation
$post->seo;

// Create / update SEO
$post->updateSeo([
    'title'          => 'My Post Title',
    'description'    => 'Meta description here.',
    'focus_keyword'  => 'laravel seo',
    'keywords'       => ['laravel', 'seo'],
    'canonical_url'  => url()->current(),
    'og_title'       => 'Share title',
    'og_description' => 'Share description',
    'og_image'       => asset('images/og.jpg'),
    'twitter_title'  => 'Twitter title',
    'twitter_image'  => asset('images/twitter.jpg'),
    'robots_index'   => true,
    'robots_follow'  => true,
    'schema_type'    => 'Article',
]);

// Helpers
$post->hasSeo();   // bool
$post->deleteSeo();
```

### Load model SEO for the current page

```php
use Abdurrahaman\LaravelSeo\Facades\SEO;

public function show(Post $post)
{
    SEO::for($post);

    return view('posts.show', compact('post'));
}
```

If the model has no SEO row yet, set fallbacks:

```php
SEO::for($post);

if (! $post->hasSeo()) {
    SEO::setTitle($post->title)
        ->setDescription(str($post->body)->limit(150)->toString());
}
```

---

## SEO tags in meta (front-end)

Put the helper once in your main layout `<head>`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{ laravel_seo() }}

    {{-- or: @seo --}}
</head>
<body>
    @yield('content')
</body>
</html>
```

### What gets rendered

- `<title>...</title>`
- `<meta name="description" ...>`
- `<link rel="canonical" ...>`
- `<meta name="robots" ...>`
- Open Graph (`og:title`, `og:description`, `og:image`, …)
- Twitter / X cards (`twitter:card`, `twitter:title`, …)
- JSON-LD (`<script type="application/ld+json">`)

### Set tags without a model

```php
use Abdurrahaman\LaravelSeo\Facades\SEO;

SEO::setTitle('About Us')
    ->setDescription('About our company.')
    ->setKeyword('about us')
    ->setImage(asset('images/about.jpg'))
    ->setCanonical(url('/about'));
```

Or:

```php
laravel_seo()
    ->title('About Us')
    ->description('About our company.')
    ->keyword('about us')
    ->image(asset('images/about.jpg'));
```

Defaults from `config/laravel-seo.php` are used when a field is empty.

---

## AI SEO Generator (ChatGPT)

Generate **SEO title**, **meta description**, **focus keyword**, and **keywords** from page content via OpenAI.

### 1. Enable in `.env`

```env
SEO_AI_ENABLED=true
SEO_AI_API_KEY=sk-your-openai-api-key
SEO_AI_MODEL=gpt-4o-mini
# optional:
# SEO_AI_BASE_URL=https://api.openai.com/v1
# SEO_AI_LANGUAGE=en
```

Or use `OPENAI_API_KEY` instead of `SEO_AI_API_KEY`.

Publish config if you have not already:

```bash
php artisan vendor:publish --tag=laravel-seo-config
```

### 2. Use the form

On the **General SEO** tab, click **Generate with AI**.

The button reads content from common fields (`#body`, `textarea[name="body"]`, `textarea[name="content"]`, TinyMCE/CKEditor) and fills:

- Focus keyword  
- SEO title  
- Meta description  
- Additional keywords  

Then review and save the form as usual.

### 3. Protect the endpoint (recommended)

In `config/laravel-seo.php`:

```php
'ai' => [
    'enabled' => true,
    'middleware' => ['web', 'auth'], // require login
    // ...
],
```

### 4. Republish assets after upgrade

```bash
php artisan seo:publish-assets --force
```
