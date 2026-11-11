<div class="mb-3">
    <label for="{{ $prefix }}_focus_keyword" class="form-label fw-semibold">
        Focus Keyword
        <span class="text-muted small">(primary keyword for this page)</span>
    </label>
    <input type="text"
           class="form-control seo-focus-keyword"
           id="{{ $prefix }}_focus_keyword"
           name="{{ $prefix }}[focus_keyword]"
           value="{{ $focusKeyword }}"
           placeholder="e.g. laravel seo package"
           autocomplete="off">
</div>

<div class="mb-3">
    <label for="{{ $prefix }}_title" class="form-label fw-semibold">
        SEO Title
        <span class="seo-char-count" data-target="{{ $prefix }}_title" data-min="30" data-max="60">
            <span class="count">0</span>/60
        </span>
    </label>
    <input type="text"
           class="form-control seo-title-input"
           id="{{ $prefix }}_title"
           name="{{ $prefix }}[title]"
           value="{{ $title }}"
           placeholder="Page title for search engines"
           maxlength="120"
           autocomplete="off">
    <div class="form-text">Recommended: 30–60 characters. Include your focus keyword.</div>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}_description" class="form-label fw-semibold">
        Meta Description
        <span class="seo-char-count" data-target="{{ $prefix }}_description" data-min="70" data-max="160">
            <span class="count">0</span>/160
        </span>
    </label>
    <textarea class="form-control seo-description-input"
              id="{{ $prefix }}_description"
              name="{{ $prefix }}[description]"
              rows="3"
              placeholder="Compelling summary that appears in search results"
              maxlength="320">{{ $description }}</textarea>
    <div class="form-text">Recommended: 70–160 characters. Include your focus keyword.</div>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}_keywords" class="form-label fw-semibold">Additional Keywords</label>
    <div class="seo-keywords-input">
        <input type="text"
               class="form-control seo-keywords-field"
               id="{{ $prefix }}_keywords_input"
               placeholder="Type a keyword and press Enter"
               autocomplete="off">
        <input type="hidden" name="{{ $prefix }}[keywords]" class="seo-keywords-hidden" value="{{ is_array($keywords) ? implode(',', $keywords) : $keywords }}">
        <div class="seo-keywords-tags mt-2">
            @foreach((array) $keywords as $kw)
                <span class="badge bg-secondary seo-keyword-tag me-1 mb-1" data-keyword="{{ $kw }}">
                    {{ $kw }}
                    <button type="button" class="btn-close btn-close-white ms-1" aria-label="Remove"></button>
                </span>
            @endforeach
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}_canonical_url" class="form-label fw-semibold">Canonical URL</label>
    <input type="url"
           class="form-control"
           id="{{ $prefix }}_canonical_url"
           name="{{ $prefix }}[canonical_url]"
           value="{{ $canonical }}"
           placeholder="Leave empty for auto-generated">
    <div class="form-text">Optional. Defaults to the current page URL.</div>
</div>

<div class="seo-google-preview mt-4">
    <label class="form-label fw-semibold">Google Search Preview</label>
    <div class="google-preview-box border rounded p-3 bg-white">
        <div class="gp-url text-success small">
            <span class="gp-favicon">🌐</span>
            <span class="gp-domain">{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'example.com' }}</span>
        </div>
        <div class="gp-title text-primary fw-medium mt-1 seo-preview-title">
            {{ $title ?: 'Page Title – ' . config('app.name') }}
        </div>
        <div class="gp-description text-muted small mt-1 seo-preview-description">
            {{ $description ?: 'Meta description will appear here. Write a compelling summary to improve click-through rates.' }}
        </div>
    </div>
</div>
