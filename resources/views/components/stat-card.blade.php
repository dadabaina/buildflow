@props(['label', 'value', 'icon', 'color' => 'primary', 'trend' => null, 'trendUp' => true])

<div {{ $attributes->merge(['class' => 'card h-100']) }}>
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
                <span class="text-muted d-block mb-1">{{ $label }}</span>
                <div class="d-flex align-items-baseline">
                    <h3 class="mb-0 me-2 fw-bold text-dark">{{ $value }}</h3>
                    @if($trend)
                        <small class="text-{{ $trendUp ? 'success' : 'danger' }} fw-medium">
                            <i class="bi bi-chevron-{{ $trendUp ? 'up' : 'down' }}"></i> {{ $trend }}
                        </small>
                    @endif
                </div>
                <small class="text-muted">Total actuel</small>
            </div>
            <div class="avatar">
                <span class="avatar-initial rounded bg-label-{{ $color }} p-2">
                    <i class="bi {{ $icon }} fs-4"></i>
                </span>
            </div>
        </div>
    </div>
</div>
