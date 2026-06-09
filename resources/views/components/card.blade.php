@props(['title' => null, 'subtitle' => null, 'icon' => null, 'footer' => null, 'bodyClass' => '', 'headerClass' => ''])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title)
        <div class="card-header {{ $headerClass }}">
            <div class="card-title mb-0">
                @if($title)
                    <h5 class="m-0 me-2 text-dark font-weight-bold">
                        @if($icon) <i class="{{ $icon }} me-2 text-primary"></i> @endif
                        {{ $title }}
                    </h5>
                @endif
                @if($subtitle)
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
            </div>
            @if(isset($headerActions))
                <div class="card-action-element">
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    @endif

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="card-footer border-top p-3">
            {{ $footer }}
        </div>
    @endif
</div>
