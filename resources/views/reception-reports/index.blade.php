<x-layouts.app title="PV de Réception">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Réceptions</li>
    </x-slot>

    <!-- Header & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-label-dark rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-check-double fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Procès-verbaux de Réception</h4>
                                <p class="text-muted small mb-0">Gestion des réceptions de chantiers, levées de réserves et libération des garanties.</p>
                            </div>
                        </div>
                        <div>
                            @can('reception_reports.create')
                            <a href="{{ route('reception-reports.create') }}" class="btn btn-primary shadow-sm px-4">
                                <i class="bx bx-plus me-1"></i>Nouveau PV
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom bg-transparent py-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-uppercase">Chantier</label>
                    <select name="project_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                        <option value="">Tous les chantiers</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Statut</label>
                    <select name="status" class="form-select border-0 bg-light" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="brouillon" @selected(request('status') === 'brouillon')>Brouillon</option>
                        <option value="signe" @selected(request('status') === 'signe')>Signé</option>
                        <option value="rg_libere" @selected(request('status') === 'rg_libere')>RG libérée</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('reception-reports.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bx bx-refresh"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Référence</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Chantier</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Date Réception</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-end">Montant RG</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Statut</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receptionReports as $pv)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-dark me-3">
                                        <i class="bx bx-clipboard fs-4"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('reception-reports.show', $pv) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $pv->reference ?? 'PV-AUTO' }}
                                        </a>
                                        <small class="text-muted small">{{ $pv->client_name ?? 'Client inconnu' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-dark small fw-medium">{{ $pv->project->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $pv->reception_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $pv->rg_amount > 0 ? 'text-primary' : 'text-muted' }}">
                                    {{ $pv->rg_amount > 0 ? number_format($pv->rg_amount, 0, ',', ' ') : '—' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($pv->status) {
                                        'brouillon' => 'bg-label-warning',
                                        'signe' => 'bg-label-primary',
                                        'rg_libere' => 'bg-label-success',
                                        default => 'bg-label-secondary'
                                    };
                                    $statusLabel = match($pv->status) {
                                        'brouillon' => 'Brouillon',
                                        'signe' => 'Signé',
                                        'rg_libere' => 'RG Libérée',
                                        default => ucfirst($pv->status)
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 0.7rem;">{{ $statusLabel }}</span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('reception-reports.show', $pv) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Détails">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('reception-reports.export', $pv) }}" class="btn btn-icon btn-sm btn-label-danger shadow-none" title="Exporter PDF">
                                        <i class="bx bxs-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-check-shield fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun PV de réception trouvé.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($receptionReports->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $receptionReports->links() }}
            </div>
        </div>
        @endif
    </div>

    @push('styles')
    <style>
        .bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
        .bg-label-warning { background-color: #fff2e2 !important; color: #ffab00 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
        .bg-label-secondary { background-color: #ebeef0 !important; color: #8592a3 !important; }
        .bg-label-dark { background-color: #d4d8dd !important; color: #435971 !important; }
    </style>
    @endpush
</x-layouts.app>
