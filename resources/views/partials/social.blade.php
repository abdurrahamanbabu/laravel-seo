<div class="row">
    <div class="col-md-6">
        <h6 class="fw-semibold mb-3"><i class="fab fa-facebook text-primary"></i> Facebook / Open Graph</h6>

        <div class="mb-3">
            <label for="{{ $prefix }}_og_title" class="form-label">OG Title</label>
            <input type="text"
                   class="form-control seo-og-title"
                   id="{{ $prefix }}_og_title"
                   name="{{ $prefix }}[og_title]"
                   value="{{ $ogTitle }}"
                   placeholder="Defaults to SEO title"
                   maxlength="100">
        </div>

        <div class="mb-3">
            <label for="{{ $prefix }}_og_description" class="form-label">OG Description</label>
            <textarea class="form-control seo-og-description"
                      id="{{ $prefix }}_og_description"
                      name="{{ $prefix }}[og_description]"
                      rows="2"
                      placeholder="Defaults to meta description"
                      maxlength="300">{{ $ogDescription }}</textarea>
        </div>

        <div class="mb-3">
            <label for="{{ $prefix }}_og_image" class="form-label">OG Image URL</label>
            <input type="text"
                   class="form-control seo-og-image"
                   id="{{ $prefix }}_og_image"
                   name="{{ $prefix }}[og_image]"
                   value="{{ $ogImage }}"
                   placeholder="https://example.com/image.jpg">
            <div class="form-text">Recommended: 1200×630 px</div>
            @if($ogImage)
                <div class="mt-2">
                    <img src="{{ $ogImage }}" alt="OG preview" class="img-thumbnail seo-image-preview" style="max-height: 120px;">
                </div>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <h6 class="fw-semibold mb-3"><i class="fab fa-x-twitter"></i> X (Twitter)</h6>

        <div class="mb-3">
            <label for="{{ $prefix }}_twitter_title" class="form-label">Twitter Title</label>
            <input type="text"
                   class="form-control"
                   id="{{ $prefix }}_twitter_title"
                   name="{{ $prefix }}[twitter_title]"
                   value="{{ $twitterTitle }}"
                   placeholder="Defaults to OG / SEO title"
                   maxlength="100">
        </div>

        <div class="mb-3">
            <label for="{{ $prefix }}_twitter_description" class="form-label">Twitter Description</label>
            <textarea class="form-control"
                      id="{{ $prefix }}_twitter_description"
                      name="{{ $prefix }}[twitter_description]"
                      rows="2"
                      placeholder="Defaults to OG / meta description"
                      maxlength="200">{{ $twitterDescription }}</textarea>
        </div>

        <div class="mb-3">
            <label for="{{ $prefix }}_twitter_image" class="form-label">Twitter Image URL</label>
            <input type="text"
                   class="form-control"
                   id="{{ $prefix }}_twitter_image"
                   name="{{ $prefix }}[twitter_image]"
                   value="{{ $twitterImage }}"
                   placeholder="Defaults to OG image">
            @if($twitterImage)
                <div class="mt-2">
                    <img src="{{ $twitterImage }}" alt="Twitter preview" class="img-thumbnail" style="max-height: 120px;">
                </div>
            @endif
        </div>
    </div>
</div>

<div class="alert alert-info small mt-2 mb-0">
    <i class="fas fa-info-circle"></i>
    Empty social fields automatically fall back to the General SEO title, description, and image.
</div>
