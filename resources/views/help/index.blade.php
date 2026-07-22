<x-layouts.app title="Aide">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Aide</li>
    </x-slot>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h4 class="mb-1 fw-bold"><i class="bi bi-life-preserver me-2 text-primary"></i>Centre d'aide</h4>
            <p class="text-muted mb-0">
                Nouveau sur BuildFlow ? Commencez par le parcours de démarrage ci-dessous. Le bouton
                <strong>« Lancer le guide »</strong> d'une rubrique ouvre les étapes à suivre et l'explication de
                chaque champ à remplir. Vous pouvez aussi relancer une visite guidée interactive à tout moment via
                l'icône <i class="bi bi-question-circle"></i> en haut de chaque page.
            </p>
        </div>
    </div>

    {{-- Parcours de démarrage --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="fw-bold mb-0"><i class="bi bi-signpost-split me-2 text-primary"></i>Premiers pas</h5>
                <span class="badge bg-label-primary">{{ $onboardingDone }}/{{ count($onboarding) }}</span>
            </div>
            <p class="text-muted small mb-3">Les toutes premières actions à faire pour prendre en main l'application, dans l'ordre.</p>
            <div class="progress mb-4" style="height: 6px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ count($onboarding) ? round($onboardingDone / count($onboarding) * 100) : 0 }}%"></div>
            </div>

            <div class="d-flex flex-column gap-2">
                @foreach($onboarding as $i => $step)
                    <div class="d-flex align-items-start gap-3 p-3 rounded-4 {{ $step['done'] ? 'bg-light' : 'border' }}">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 {{ $step['done'] ? 'bg-success text-white' : 'bg-label-primary' }}"
                             style="width: 32px; height: 32px;">
                            @if($step['done'])
                                <i class="bi bi-check-lg"></i>
                            @else
                                <span class="fw-bold small">{{ $i + 1 }}</span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark {{ $step['done'] ? 'text-decoration-line-through text-muted' : '' }}">{{ $step['title'] }}</div>
                            <p class="text-muted small mb-0">{{ $step['text'] }}</p>
                        </div>
                        @unless($step['done'])
                            <a href="{{ route($step['route']) }}" class="btn btn-sm btn-primary flex-shrink-0 align-self-center">{{ $step['cta'] }}</a>
                        @endunless
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Schéma du cycle central --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-diagram-3 me-2 text-primary"></i>Le cycle de BuildFlow</h5>
            <p class="text-muted small mb-4">Tout tourne autour de cet enchaînement. Comprendre ce schéma, c'est comprendre 80% de l'application.</p>
            <div class="d-flex flex-wrap align-items-stretch justify-content-center gap-2">
                @foreach($cycle as $step)
                    <div class="d-flex align-items-center">
                        <div class="text-center px-2" style="width: 130px;">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-label-primary mx-auto mb-2" style="width: 56px; height: 56px;">
                                <i class="bi {{ $step['icon'] }} fs-4 text-primary"></i>
                            </div>
                            <div class="fw-bold text-dark small">{{ $step['label'] }}</div>
                            <p class="text-muted mb-0" style="font-size: 0.72rem;">{{ $step['text'] }}</p>
                        </div>
                    </div>
                    @if(!$loop->last)
                        <div class="d-none d-md-flex align-items-center text-muted" style="margin-top: -32px;">
                            <i class="bi bi-arrow-right fs-4"></i>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @if(!empty($setupSections))
        <h6 class="fw-bold text-uppercase text-muted mb-3 mt-5" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            <i class="bi bi-gear me-1"></i> Configuration initiale — à faire une fois
        </h6>
        @include('help._catalog', ['catalogSections' => $setupSections])
    @endif

    @if(!empty($dailySections))
        <h6 class="fw-bold text-uppercase text-muted mb-3 mt-4" style="font-size: 0.75rem; letter-spacing: 0.05em;">
            <i class="bi bi-arrow-repeat me-1"></i> Utilisation quotidienne
        </h6>
        @include('help._catalog', ['catalogSections' => $dailySections])
    @endif

    {{-- FAQ --}}
    <div class="card border-0 shadow-sm mb-4 mt-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-question-circle me-2 text-primary"></i>Questions fréquentes</h5>
            <div class="accordion" id="help-faq">
                @foreach($faq as $i => $item)
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faq-{{ $i }}">
                                {{ $item['q'] }}
                            </button>
                        </h2>
                        <div id="faq-{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#help-faq">
                            <div class="accordion-body text-muted small">{{ $item['a'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Modales des guides détaillés (étapes + explication des champs) --}}
    @foreach($sections as $section)
        @foreach($section['items'] as $item)
            @continue(empty($item['guide']))
            @php $guide = $item['guide']; @endphp
            <div class="modal fade" id="guide-{{ str_replace('.', '-', $item['route']) }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="bi bi-life-preserver me-2 text-primary"></i>{{ $item['label'] }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            @if(!empty($guide['intro']))
                                <p class="text-muted">{{ $guide['intro'] }}</p>
                            @endif

                            <h6 class="fw-bold text-uppercase text-muted mt-4 mb-3" style="font-size: 0.75rem;">Étapes à suivre</h6>
                            <div class="d-flex flex-column gap-3 mb-4">
                                @foreach($guide['steps'] as $step)
                                    <div class="border rounded-4 p-3">
                                        <div class="fw-bold text-dark mb-1">{{ $step['title'] }}</div>
                                        <p class="text-muted small mb-0">{{ $step['text'] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <h6 class="fw-bold text-uppercase text-muted mt-4 mb-3" style="font-size: 0.75rem;">Explication des champs</h6>
                            <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                                @foreach($guide['fields'] as $field)
                                    <div class="list-group-item p-3">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-bold text-dark">{{ $field['label'] }}</span>
                                            @if($field['required'])
                                                <span class="badge bg-label-danger" style="font-size: 0.65rem;">Obligatoire</span>
                                            @else
                                                <span class="badge bg-label-secondary" style="font-size: 0.65rem;">Optionnel</span>
                                            @endif
                                        </div>
                                        <p class="text-muted small mb-0">{{ $field['text'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                            <a href="{{ route($item['route']) }}" class="btn btn-primary">
                                Aller à « {{ $item['label'] }} » <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
</x-layouts.app>
