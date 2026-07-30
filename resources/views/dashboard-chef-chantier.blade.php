<x-layouts.app title="Tableau de bord">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item active fw-bold text-dark">Tableau de bord</li>
    </x-slot>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden" style="min-height: 140px;">
                <div class="card-body p-4 p-md-5 d-flex align-items-center">
                    <div>
                        <h3 class="fw-bold text-white mb-2">Bon retour, {{ auth()->user()->name }} ! 👋</h3>
                        <p class="mb-0 opacity-75">
                            Vous gérez {{ $activeProjectsCount }} chantier{{ $activeProjectsCount > 1 ? 's' : '' }} actif{{ $activeProjectsCount > 1 ? 's' : '' }}
                            sur {{ $projectsHealth->count() }} chantier{{ $projectsHealth->count() > 1 ? 's' : '' }} assigné{{ $projectsHealth->count() > 1 ? 's' : '' }}.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-building-house me-2 text-primary"></i>Mes chantiers assignés</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Chantier</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Client</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Statut</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Avancement Physique</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Budget Consommé</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectsHealth as $health)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $health['name'] }}</div>
                                        <small class="text-muted">{{ $health['reference'] }}</small>
                                    </td>
                                    <td>
                                        <span class="text-dark small fw-medium">{{ $health['client']?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $health['status_badge_class'] }} badge-sm text-uppercase">
                                            {{ $health['status_libelle'] }}
                                        </span>
                                    </td>
                                    <td style="min-width: 140px;">
                                        @if($health['progress_percent'] !== null)
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $health['progress_percent'] }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $health['progress_percent'] }}%</span>
                                        </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 140px;">
                                        @if($health['budget_consumption_percent'] !== null)
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2" style="height: 6px;">
                                                <div class="progress-bar {{ $health['budget_consumption_percent'] > 90 ? 'bg-danger' : ($health['drift_alert'] ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ min(100, $health['budget_consumption_percent']) }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $health['budget_consumption_percent'] }}%</span>
                                        </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('projects.show', $health['id']) }}" class="btn btn-icon btn-sm btn-label-primary">
                                            <i class="bx bx-chevron-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted small">Aucun chantier ne vous est assigné pour le moment.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
