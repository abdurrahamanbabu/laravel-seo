@php
    $prefix = $namePrefix;
    $title = $field('title');
    $description = $field('description');
    $focusKeyword = $field('focus_keyword');
    $keywords = $field('keywords', []);
    if (is_string($keywords)) {
        $keywords = array_filter(array_map('trim', explode(',', $keywords)));
    }
    $canonical = $field('canonical_url');
    $ogTitle = $field('og_title');
    $ogDescription = $field('og_description');
    $ogImage = $field('og_image');
    $twitterTitle = $field('twitter_title');
    $twitterDescription = $field('twitter_description');
    $twitterImage = $field('twitter_image');
    $robotsIndex = $field('robots_index', true);
    $robotsFollow = $field('robots_follow', true);
    $excludeSitemap = $field('exclude_from_sitemap', false);
    $schemaType = $field('schema_type', 'WebPage');
    $score = $analysis['score'] ?? 0;
    $status = $analysis['status'] ?? 'poor';

    $aiEnabled = (bool) config('laravel-seo.ai.enabled', false);
    $aiHasKey = filled(config('laravel-seo.ai.api_key'));
    $generateUrl = \Illuminate\Support\Facades\Route::has('laravel-seo.generate')
        ? route('laravel-seo.generate')
        : url(config('laravel-seo.ai.route', 'laravel-seo/generate'));
    $contentSelectors = config('laravel-seo.ai.content_selectors', ['#body', 'textarea[name="body"]', 'textarea[name="content"]']);
@endphp

<div class="laravel-seo-form" id="laravel-seo-form" data-prefix="{{ $prefix }}">
    {{-- Score badge --}}
    @if($showAnalysis)
    <div class="seo-score-badge seo-status-{{ $status }} mb-3">
        <span class="seo-score-value">{{ $score }}</span>
        <span class="seo-score-label">SEO Score</span>
    </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs seo-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="seo-general-tab" data-bs-toggle="tab" data-bs-target="#seo-general" type="button" role="tab">
                <i class="fas fa-search"></i> General SEO
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seo-social-tab" data-bs-toggle="tab" data-bs-target="#seo-social" type="button" role="tab">
                <i class="fas fa-share-alt"></i> Social Media
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seo-advanced-tab" data-bs-toggle="tab" data-bs-target="#seo-advanced" type="button" role="tab">
                <i class="fas fa-cogs"></i> Advanced
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seo-schema-tab" data-bs-toggle="tab" data-bs-target="#seo-schema" type="button" role="tab">
                <i class="fas fa-code"></i> Schema
            </button>
        </li>
        @if($showAnalysis)
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="seo-analysis-tab" data-bs-toggle="tab" data-bs-target="#seo-analysis" type="button" role="tab">
                <i class="fas fa-chart-line"></i> Analysis
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content seo-tab-content border border-top-0 p-3 rounded-bottom">
        {{-- General SEO --}}
        <div class="tab-pane fade show active" id="seo-general" role="tabpanel">

            {{-- AI Generate (in form so it is never missed if only form is published) --}}
            <div class="seo-ai-generate mb-3 p-3 border rounded bg-light">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <strong class="d-block"><i class="fas fa-magic"></i> AI SEO Generator</strong>
                        <span class="text-muted small">Generate title, description, and keywords from page content (ChatGPT).</span>
                    </div>
                    @if($aiEnabled)
                        <button type="button"
                                class="btn btn-primary btn-sm seo-ai-generate-btn"
                                data-url="{{ $generateUrl }}"
                                data-selectors="{{ e(json_encode(array_values($contentSelectors))) }}">
                            <span class="seo-ai-btn-label">Generate with AI</span>
                            <span class="seo-ai-btn-loading d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Generating…
                            </span>
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                            Generate with AI (disabled)
                        </button>
                    @endif
                </div>

                @if(! $aiEnabled)
                    <div class="alert alert-warning small mb-0 mt-2 py-2">
                        AI is <strong>off</strong>. Add to <code>.env</code> then run <code>php artisan config:clear</code>:
                        <pre class="mb-0 mt-1 small">SEO_AI_ENABLED=true
SEO_AI_API_KEY=sk-your-openai-api-key
SEO_AI_MODEL=gpt-4o-mini</pre>
                    </div>
                @elseif(! $aiHasKey)
                    <div class="alert alert-warning small mb-0 mt-2 py-2">
                        AI is enabled but <code>SEO_AI_API_KEY</code> (or <code>OPENAI_API_KEY</code>) is empty.
                    </div>
                @endif

                <div class="seo-ai-message small mt-2 d-none"></div>
            </div>

            @include('laravel-seo::partials.general', compact('prefix', 'title', 'description', 'focusKeyword', 'keywords', 'canonical'))
        </div>

        {{-- Social Media --}}
        <div class="tab-pane fade" id="seo-social" role="tabpanel">
            @include('laravel-seo::partials.social', compact('prefix', 'ogTitle', 'ogDescription', 'ogImage', 'twitterTitle', 'twitterDescription', 'twitterImage'))
        </div>

        {{-- Advanced --}}
        <div class="tab-pane fade" id="seo-advanced" role="tabpanel">
            @include('laravel-seo::partials.advanced', compact('prefix', 'robotsIndex', 'robotsFollow', 'excludeSitemap', 'canonical'))
        </div>

        {{-- Schema --}}
        <div class="tab-pane fade" id="seo-schema" role="tabpanel">
            @include('laravel-seo::partials.schema', ['prefix' => $prefix, 'schemaType' => $schemaType, 'seoMeta' => $seoMeta])
        </div>

        {{-- Analysis --}}
        @if($showAnalysis)
        <div class="tab-pane fade" id="seo-analysis" role="tabpanel">
            @include('laravel-seo::partials.analysis', ['analysis' => $analysis])
        </div>
        @endif
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/laravel-seo/css/laravel-seo.css') }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('vendor/laravel-seo/js/laravel-seo.js') }}"></script>
    @endpush
@endonce
