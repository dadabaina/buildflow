<x-layouts.app title="Comptes-rendus de Chantier">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Comptes-rendus</li>
    </x-slot>

    <!-- Header & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-label-secondary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-file-blank fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Comptes-rendus de Chantier</h4>
                                <p class="text-muted small mb-0">Historique des réunions, décisions et suivi de l'avancement technique.</p>
                            </div>
                        </div>
                        <div>
                            @can('site_reports.create')
                            <a href="{{ route('site-reports.create') }}" class="btn btn-primary shadow-sm px-4">
                                <i class="bx bx-plus me-1"></i>Nouveau CR
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
                        <option value="finalise" @selected(request('status') === 'finalise')>Finalisé</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('site-reports.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
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
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Référence & Titre</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Chantier</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Date</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Rédacteur</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Statut</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siteReports as $cr)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-secondary me-3">
                                        <i class="bx bx-file fs-4"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('site-reports.show', $cr) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $cr->title }}
                                        </a>
                                        <small class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $cr->reference ?? 'SANS-REF' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-dark small fw-medium">{{ $cr->project->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $cr->report_date->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $cr->author->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $cr->status === 'finalise' ? 'bg-label-success' : 'bg-label-warning' }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ $cr->status === 'finalise' ? 'Finalisé' : 'Brouillon' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('site-reports.show', $cr) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Détails">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if($cr->status !== 'finalise')
                                    <a href="{{ route('site-reports.edit', $cr) }}" class="btn btn-icon btn-sm btn-label-info shadow-none" title="Modifier">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('site-reports.export', $cr) }}" class="btn btn-icon btn-sm btn-label-danger shadow-none" title="Exporter PDF">
                                        <i class="bx bxs-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-file fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun compte-rendu trouvé.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($siteReports->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $siteReports->links() }}
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
    </style>
    @endpush
</x-layouts.app>
