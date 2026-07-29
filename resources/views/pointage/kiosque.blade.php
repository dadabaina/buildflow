<x-layouts.app title="Pointage — Kiosque">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Pointage</li>
    </x-slot>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bx bx-camera me-2"></i>Pointage — Entrée / Sortie</h4>
            <p class="text-muted small mb-0">{{ today()->translatedFormat('l d F Y') }}</p>
        </div>
        <form method="GET" class="d-flex gap-2">
            <select name="project_id" class="form-select border-0 shadow-sm" style="min-width: 220px;" onchange="this.form.submit()">
                @forelse($projects as $p)
                    <option value="{{ $p->id }}" @selected($project?->id == $p->id)>{{ $p->name }}</option>
                @empty
                    <option value="">Aucun chantier assigné</option>
                @endforelse
            </select>
        </form>
    </div>

    @if(!$project)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                Aucun chantier géré n'est assigné à votre compte. Contactez un administrateur.
            </div>
        </div>
    @else
        <div id="pointage-offline-banner" class="alert alert-warning d-none d-flex align-items-center gap-2 mb-3">
            <i class="bx bx-wifi-off fs-5"></i>
            <div>Mode hors-ligne : les pointages sont enregistrés sur l'appareil et seront envoyés automatiquement au retour du réseau.</div>
        </div>

        <div class="row g-3">
            @forelse($employees as $emp)
                @php $att = $todayByEmployee->get($emp->id); @endphp
                <div class="col-md-6 col-lg-4" data-row="{{ $emp->id }}">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="avatar rounded-circle overflow-hidden {{ $emp->photo_url ? '' : 'bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold' }}" style="width: 48px; height: 48px;">
                                    @if($emp->photo_url)
                                        <img src="{{ $emp->photo_url }}" alt="{{ $emp->full_name }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $emp->full_name }}</div>
                                    @if(!$att)
                                        <span class="badge bg-label-secondary pointage-status">Pas encore pointé</span>
                                    @elseif($att->check_in && !$att->check_out)
                                        <span class="badge bg-label-success pointage-status">Entré à {{ substr($att->check_in, 0, 5) }}</span>
                                    @elseif($att->check_in && $att->check_out)
                                        <span class="badge bg-label-info pointage-status">Sorti à {{ substr($att->check_out, 0, 5) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                @if(!$att || !$att->check_in)
                                    <form method="POST" action="{{ route('pointage.entree', [$project, $emp]) }}" enctype="multipart/form-data"
                                          class="flex-fill pointage-form" data-employee-id="{{ $emp->id }}" data-action="entree">
                                        @csrf
                                        <input type="file" name="photo" accept="image/*" capture="camera" class="d-none"
                                               id="entree-{{ $emp->id }}">
                                        <button type="button" class="btn btn-success w-100"
                                                onclick="document.getElementById('entree-{{ $emp->id }}').click()">
                                            <i class="bx bx-log-in-circle me-1"></i>Entrée
                                        </button>
                                    </form>
                                @elseif(!$att->check_out)
                                    <form method="POST" action="{{ route('pointage.sortie', [$project, $emp]) }}" enctype="multipart/form-data"
                                          class="flex-fill pointage-form" data-employee-id="{{ $emp->id }}" data-action="sortie">
                                        @csrf
                                        <input type="file" name="photo" accept="image/*" capture="camera" class="d-none"
                                               id="sortie-{{ $emp->id }}">
                                        <button type="button" class="btn btn-warning w-100"
                                                onclick="document.getElementById('sortie-{{ $emp->id }}').click()">
                                            <i class="bx bx-log-out-circle me-1"></i>Sortie
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                        <i class="bx bx-check-double me-1"></i>Journée terminée
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center text-muted py-5">Aucun employé actif sur ce chantier.</div>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    @push('scripts')
        @vite(['resources/js/pointage-kiosque.js'])
    @endpush
</x-layouts.app>
