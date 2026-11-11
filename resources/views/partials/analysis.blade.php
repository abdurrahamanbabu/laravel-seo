@php
    $score = $analysis['score'] ?? 0;
    $status = $analysis['status'] ?? 'poor';
    $checks = $analysis['checks'] ?? [];
@endphp

<div class="seo-analysis-overview mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="seo-score-circle seo-status-{{ $status }}">
            <span>{{ $score }}</span>
        </div>
        <div>
            <h5 class="mb-1">Overall SEO Score</h5>
            <p class="text-muted mb-0 small">
                Heuristic analysis based on common on-page SEO best practices.
                These scores are guidelines, not guaranteed ranking factors.
            </p>
        </div>
    </div>
</div>

@if(empty($checks))
    <div class="alert alert-secondary">
        Enter a focus keyword and content, then save or trigger analysis to see results.
    </div>
@else
    <div class="seo-checks-list">
        @foreach($checks as $check)
            <div class="seo-check-item seo-check-{{ $check['status'] }} d-flex align-items-start gap-2 mb-2 p-2 rounded">
                <span class="seo-check-icon">
                    @if($check['status'] === 'good')
                        <i class="fas fa-check-circle text-success"></i>
                    @elseif($check['status'] === 'ok')
                        <i class="fas fa-exclamation-circle text-warning"></i>
                    @else
                        <i class="fas fa-times-circle text-danger"></i>
                    @endif
                </span>
                <div>
                    <strong class="small text-uppercase text-muted">{{ str_replace('_', ' ', $check['key']) }}</strong>
                    <div>{{ $check['message'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif
