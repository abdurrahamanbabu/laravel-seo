<div class="mb-3">
    <label for="{{ $prefix }}_schema_type" class="form-label fw-semibold">Schema Type</label>
    <select class="form-select" id="{{ $prefix }}_schema_type" name="{{ $prefix }}[schema_type]">
        @php
            $types = [
                'WebPage' => 'Web Page',
                'Article' => 'Article',
                'BlogPosting' => 'Blog Posting',
                'Product' => 'Product',
                'LocalBusiness' => 'Local Business',
                'Organization' => 'Organization',
                'BreadcrumbList' => 'Breadcrumb List',
            ];
        @endphp
        @foreach($types as $value => $label)
            <option value="{{ $value }}" @selected($schemaType === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="form-text">Structured data type for JSON-LD output.</div>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}_schema_data" class="form-label fw-semibold">Custom Schema Data (JSON)</label>
    @php
        $schemaDataValue = old($prefix . '.schema_data');
        if ($schemaDataValue === null && isset($seoMeta) && $seoMeta) {
            $schemaDataValue = $seoMeta->schema_data;
        }
        if (is_array($schemaDataValue)) {
            $schemaDataValue = json_encode($schemaDataValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    @endphp
    <textarea class="form-control font-monospace"
              id="{{ $prefix }}_schema_data"
              name="{{ $prefix }}[schema_data]"
              rows="6"
              placeholder='{"headline": "...", "datePublished": "2024-01-01"}'>{{ $schemaDataValue }}</textarea>
    <div class="form-text">Optional. Merged into the generated schema. Must be valid JSON.</div>
</div>
