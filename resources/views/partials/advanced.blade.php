<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label fw-semibold">Search Engine Indexing</label>
            <div class="form-check form-switch">
                <input class="form-check-input"
                       type="checkbox"
                       role="switch"
                       id="{{ $prefix }}_robots_index"
                       name="{{ $prefix }}[robots_index]"
                       value="1"
                       @checked($robotsIndex)>
                <label class="form-check-label" for="{{ $prefix }}_robots_index">
                    Allow search engines to index this page
                </label>
            </div>
            <div class="form-text">Uncheck for <code>noindex</code>.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Link Following</label>
            <div class="form-check form-switch">
                <input class="form-check-input"
                       type="checkbox"
                       role="switch"
                       id="{{ $prefix }}_robots_follow"
                       name="{{ $prefix }}[robots_follow]"
                       value="1"
                       @checked($robotsFollow)>
                <label class="form-check-label" for="{{ $prefix }}_robots_follow">
                    Allow search engines to follow links
                </label>
            </div>
            <div class="form-text">Uncheck for <code>nofollow</code>.</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label fw-semibold">Sitemap</label>
            <div class="form-check form-switch">
                <input class="form-check-input"
                       type="checkbox"
                       role="switch"
                       id="{{ $prefix }}_exclude_from_sitemap"
                       name="{{ $prefix }}[exclude_from_sitemap]"
                       value="1"
                       @checked($excludeSitemap)>
                <label class="form-check-label" for="{{ $prefix }}_exclude_from_sitemap">
                    Exclude this page from the XML sitemap
                </label>
            </div>
        </div>

        <div class="mb-3">
            <label for="{{ $prefix }}_canonical_url_adv" class="form-label fw-semibold">Canonical URL</label>
            <input type="url"
                   class="form-control"
                   id="{{ $prefix }}_canonical_url_adv"
                   name="{{ $prefix }}[canonical_url]"
                   value="{{ $canonical }}"
                   placeholder="Leave empty for auto-generated">
        </div>
    </div>
</div>
