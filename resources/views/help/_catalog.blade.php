@foreach($catalogSections as $section)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h6 class="fw-bold text-uppercase text-muted mb-0" style="font-size: 0.8rem;">{{ $section['title'] }}</h6>
        </div>
        <div class="card-body px-4 pb-4 pt-0">
            <div class="row g-3">
                @foreach($section['items'] as $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded-4 p-3 h-100 d-flex flex-column">
                            <div class="fw-bold text-dark mb-1">{{ $item['label'] }}</div>
                            <p class="text-muted small flex-grow-1 mb-3">{{ $item['description'] }}</p>
                            @if(!empty($item['guide']))
                                <button type="button" class="btn btn-sm btn-outline-primary align-self-start"
                                        data-bs-toggle="modal" data-bs-target="#guide-{{ str_replace('.', '-', $item['route']) }}">
                                    <i class="bi bi-play-circle me-1"></i> Lancer le guide
                                </button>
                            @else
                                <a href="{{ route($item['route']) }}?tour=1" class="btn btn-sm btn-outline-primary align-self-start">
                                    <i class="bi bi-play-circle me-1"></i> Lancer le guide
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
