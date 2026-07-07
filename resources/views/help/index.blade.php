<x-layouts.app title="Aide">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Aide</li>
    </x-slot>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h4 class="mb-1 fw-bold"><i class="bi bi-life-preserver me-2 text-primary"></i>Centre d'aide</h4>
            <p class="text-muted mb-0">
                Choisis une rubrique ci-dessous. Le bouton <strong>« Lancer le guide »</strong> t'amène sur la page
                concernée et te montre, étape par étape, comment l'utiliser. Tu peux aussi relancer ce guide à tout
                moment en cliquant sur l'icône <i class="bi bi-question-circle"></i> en haut de chaque page.
            </p>
        </div>
    </div>

    @foreach($sections as $section)
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
                                <a href="{{ route($item['route']) }}?tour=1" class="btn btn-sm btn-outline-primary align-self-start">
                                    <i class="bi bi-play-circle me-1"></i> Lancer le guide
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</x-layouts.app>
