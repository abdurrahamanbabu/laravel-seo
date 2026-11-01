# Changelog

All notable changes to `abdur-rahaman/laravel-seo` are documented in this file.

## [2.0.0] — 2026-11-01

### Added

- **AI SEO Generator (ChatGPT / OpenAI)**
  - Generate SEO title, meta description, focus keyword, and keywords from page/blog content
  - “Generate with AI” button on the General SEO form tab
  - AJAX endpoint: `POST /laravel-seo/generate`
  - New service: `AiSeoGenerator`
  - Config section: `laravel-seo.ai` (`SEO_AI_ENABLED`, `SEO_AI_API_KEY`, `SEO_AI_MODEL`, content selectors, middleware)
  - Supports reading content from `#body`, `textarea[name=body|content]`, TinyMCE, and CKEditor
  - Fills SEO input fields in the admin form after a successful response

- **Laravel 13 support**
  - Illuminate package constraints updated to `^11.0|^12.0|^13.0`
  - Testbench / PHPUnit constraints widened for modern Laravel

- **Blade-safe SEO rendering**
  - `SeoManager` implements `Htmlable` and `__toString()` so `{{ laravel_seo() }}` works without `htmlspecialchars()` errors

- **Safer SEO meta caching**
  - Repository caches attribute arrays instead of Eloquent models
  - Automatically discards corrupt / `__PHP_Incomplete_Class` cache entries

- **Documentation**
  - Focused README: Install, Initial setup, Form fields, Model setup, Front-end meta tags, AI generator

### Changed

- Service provider registers Blade directives and components after the Blade compiler is resolved (avoids “Please provide a valid cache path” during `package:discover`)
- `SeoRepository::findForModel()` no longer stores full model instances in the cache store
- Frontend assets (`laravel-seo.js` / `laravel-seo.css`) updated for AI generate UI and loading states

### Fixed

- `htmlspecialchars(): Argument #1 ($string) must be of type string, SeoManager given` when using `{{ laravel_seo() }}`
- `SeoRepository::findForModel(): ... __PHP_Incomplete_Class returned` after cache unserialize
- Composer conflict with Laravel 13 (`illuminate/support` version constraint)
- View compiler errors during Composer `package:discover` on fresh installs

### Upgrade guide (1.x → 2.0)

1. Update the package:

```bash
composer require abdur-rahaman/laravel-seo:^2.0
```

2. Publish config and assets:

```bash
php artisan vendor:publish --tag=laravel-seo-config --force
php artisan seo:publish-assets --force
php artisan optimize:clear
```

3. (Optional) Enable AI generation in `.env`:

```env
SEO_AI_ENABLED=true
SEO_AI_API_KEY=sk-your-openai-api-key
SEO_AI_MODEL=gpt-4o-mini
```

4. Ensure your layout still uses:

```blade
{{ laravel_seo() }}
```

5. Clear application cache after upgrade:

```bash
php artisan cache:clear
php artisan seo:clear-cache
```

### Breaking changes

- Minimum recommended stack remains **PHP 8.3+** and **Laravel 11+** (now including Laravel 13).
- Cache payload format for SEO meta changed (attribute arrays). Old cached model entries are ignored and rebuilt automatically; run `php artisan cache:clear` once after upgrade.
- AI generate route is only registered when `SEO_AI_ENABLED=true`.

---

## [1.0.0] — Initial release

- SEO metadata management (title, description, keywords, canonical, robots)
- Open Graph & X (Twitter) Cards
- JSON-LD structured data
- XML sitemap & optional robots.txt
- Yoast-style SEO analysis meter
- Polymorphic `seo_meta` + `HasSeo` trait
- Admin form component (`<x-laravel-seo::form />`)
- Facade, helpers, Artisan commands
