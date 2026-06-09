<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">PV Réception</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $receptionReport->reference }}</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="fw-bold mb-1">PV de Réception — {{ $receptionReport->reference }}</h3>
            <div class="text-secondary small">
                {{ $receptionReport->reception_date->format('d/m/Y') }} · {{ $receptionReport->project->name ?? '—' }}
                <span class="ms-2 badge bg-{{ ['brouillon'=>'warning text-dark','signe'=>'primary','rg_libere'=>'success'][$receptionReport->status] ?? 'secondary' }}">
                    {{ ['brouillon'=>'Brouillon','signe'=>'Signé','rg_libere'=>'RG libérée'][$receptionReport->status] ?? $receptionReport->status }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reception-reports.export', $receptionReport) }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </a>
            @can('reception_reports.edit')
            <a href="{{ route('reception-reports.edit', $receptionReport) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            @if($receptionReport->status === 'brouillon')
            <form method="POST" action="{{ route('reception-reports.accept', $receptionReport) }}">
                @csrf
                <button class="btn btn-success btn-sm" onclick="return confirm('Marquer comme signé ?')">
                    <i class="bi bi-check-circle me-1"></i>Marquer Signé
                </button>
            </form>
            @endif
            @if($receptionReport->status === 'signe' && $receptionReport->rg_amount > 0)
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rgModal">
                <i class="bi bi-unlock me-1"></i>Libérer RG
            </button>
            @endif
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <x-card title="Détails du PV">
                <dl class="row">
                    <dt class="col-4 text-muted">Chantier</dt>
                    <dd class="col-8"><a href="{{ route('projects.show', $receptionReport->project) }}">{{ $receptionReport->project->name ?? '—' }}</a></dd>
                    <dt class="col-4 text-muted">Date réception</dt>
                    <dd class="col-8">{{ $receptionReport->reception_date->format('d/m/Y') }}</dd>
                    <dt class="col-4 text-muted">Maître d'ouvrage</dt>
                    <dd class="col-8">{{ $receptionReport->client_name ?? '—' }}</dd>
                    <dt class="col-4 text-muted">Retenue de garantie</dt>
                    <dd class="col-8 fw-semibold">{{ $receptionReport->rg_amount > 0 ? number_format($receptionReport->rg_amount, 0, ',', ' ').' Ar' : 'Aucune' }}</dd>
                    @if($receptionReport->rg_release_date)
                    <dt class="col-4 text-muted">Date libération RG</dt>
                    <dd class="col-8 text-success">{{ $receptionReport->rg_release_date->format('d/m/Y') }}</dd>
                    @endif
                </dl>
                @if($receptionReport->reserves)
                <hr>
                <h6 class="fw-semibold">Réserves</h6>
                <p style="white-space: pre-wrap;">{{ $receptionReport->reserves }}</p>
                @endif
                @if($receptionReport->notes)
                <hr>
                <h6 class="fw-semibold">Notes</h6>
                <p style="white-space: pre-wrap;">{{ $receptionReport->notes }}</p>
                @endif
            </x-card>
        </div>
        <div class="col-lg-4">
            <x-card title="Actions">
                <div class="d-grid gap-2">
                    <a href="{{ route('reception-reports.export', $receptionReport) }}" class="btn btn-outline-danger">
                        <i class="bi bi-file-pdf me-1"></i>Télécharger PDF
                    </a>
                    @can('reception_reports.delete')
                    <form method="POST" action="{{ route('reception-reports.destroy', $receptionReport) }}"
                          onsubmit="return confirm('Supprimer ce PV ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Supprimer</button>
                    </form>
                    @endcan
                </div>
            </x-card>
        </div>
    </div>

    {{-- Modal libération RG --}}
    <div class="modal fade" id="rgModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Libérer la retenue de garantie</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('reception-reports.release-rg', $receptionReport) }}">
                    @csrf
                    <div class="modal-body">
                        <p>Montant de la RG : <strong>{{ number_format($receptionReport->rg_amount, 0, ',', ' ') }} Ar</strong></p>
                        <div class="mb-3">
                            <label class="form-label">Date de libération <span class="text-danger">*</span></label>
                            <input type="date" name="rg_release_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Confirmer la libération</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
