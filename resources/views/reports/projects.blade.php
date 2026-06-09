<x-layouts.app title="Suivi des Chantiers">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none opacity-50 text-dark">Rapports</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Suivi Chantiers</li>
    </x-slot>

    <!-- Header & Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h4 class="mb-1 fw-bold">Suivi Global des Chantiers</h4>
                            <p class="text-muted small mb-0">Analyse de la rentabilité, état d'avancement et comparaison budget/réel.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="GET" class="d-flex gap-2">
                                <select name="status" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                    <option value="">Tous les statuts</option>
                                    <option value="planifie" @selected($status == 'planifie')>Planifiés</option>
                                    <option value="en_cours" @selected($status == 'en_cours')>En cours</option>
                                    <option value="termine" @selected($status == 'termine')>Terminés</option>
                                    <option value="suspendu" @selected($status == 'suspendu')>Suspendus</option>
                                </select>
                            </form>
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-outline-danger btn-icon shadow-sm" title="Exporter en PDF">
                                <i class="bx bxs-file-pdf"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Chantier</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Client</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Statut</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-end">Dépenses</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-end">Facturé TTC</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Marge (Cash)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $p)
                        @php
                            $marge = ($p->invoiced_total ?? 0) - ($p->expenses_total ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-primary me-3">
                                        <i class="bx bx-building fs-4"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('projects.show', $p) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $p->name }}
                                        </a>
                                        <small class="text-muted small">{{ $p->reference }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-medium">{{ $p->client->name ?? '—' }}</span>
                                    <small class="text-muted small">{{ $p->region->name ?? '—' }}</small>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($p->status) {
                                        'planifie' => 'bg-label-secondary',
                                        'en_cours' => 'bg-label-primary',
                                        'termine' => 'bg-label-success',
                                        'suspendu' => 'bg-label-warning',
                                        'annule' => 'bg-label-danger',
                                        default => 'bg-label-secondary'
                                    };
                                    $statusLabel = match($p->status) {
                                        'planifie' => 'Planifié',
                                        'en_cours' => 'En cours',
                                        'termine' => 'Terminé',
                                        'suspendu' => 'Suspendu',
                                        'annule' => 'Annulé',
                                        default => ucfirst($p->status)
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 0.7rem;">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($p->expenses_total ?? 0, 0, ',', ' ') }} <small class="fw-normal">Ar</small>
                            </td>
                            <td class="text-end fw-bold text-primary">
                                {{ number_format($p->invoiced_total ?? 0, 0, ',', ' ') }} <small class="fw-normal">Ar</small>
                            </td>
                            <td class="pe-4 text-end">
                                <span class="fw-bold {{ $marge >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($marge, 0, ',', ' ') }} <small class="fw-normal">Ar</small>
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-buildings fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun chantier trouvé.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($projects->count() > 0)
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="3" class="ps-4 py-3">TOTAL GÉNÉRAL</td>
                            <td class="text-end text-danger py-3">{{ number_format($projects->sum('expenses_total'), 0, ',', ' ') }} Ar</td>
                            <td class="text-end text-primary py-3">{{ number_format($projects->sum('invoiced_total'), 0, ',', ' ') }} Ar</td>
                            <td class="pe-4 text-end py-3">
                                @php $totalMarge = $projects->sum('invoiced_total') - $projects->sum('expenses_total'); @endphp
                                <span class="{{ $totalMarge >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($totalMarge, 0, ',', ' ') }} Ar
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
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
