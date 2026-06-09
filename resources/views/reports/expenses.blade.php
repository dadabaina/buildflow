<x-layouts.app title="Journal des Dépenses">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none opacity-50 text-dark">Rapports</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Journal dépenses</li>
    </x-slot>

    <!-- Header & Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h4 class="mb-1 fw-bold">Journal des Dépenses</h4>
                            <p class="text-muted small mb-0">Suivi détaillé des frais, achats matériaux et sous-traitance pour l'année {{ $year }}.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="GET" class="d-flex gap-2">
                                <select name="year" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" @selected($y == $year)>Année {{ $y }}</option>
                                    @endforeach
                                </select>
                                <select name="project_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                    <option value="">Tous les chantiers</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                                    @endforeach
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

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        @foreach($byCategory as $cat => $total)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-label-danger p-2 rounded"><i class="bx bx-receipt fs-4"></i></span>
                    </div>
                    <p class="mb-0 text-muted small text-uppercase fw-semibold">{{ $cat }}</p>
                    <h5 class="mb-0 fw-bold mt-1 text-danger">{{ number_format($total, 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h5>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Date</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Projet</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Catégorie</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Description</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-end">Montant</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $e)
                        <tr>
                            <td class="ps-4">
                                <span class="text-muted small fw-medium">{{ $e->expense_date?->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <span class="text-dark small fw-bold">{{ $e->project->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-label-secondary text-uppercase" style="font-size: 0.65rem;">{{ $e->category->name ?? 'Autres' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($e->description, 40) }}</span>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($e->total_amount, 0, ',', ' ') }} <small class="fw-normal">Ar</small>
                            </td>
                            <td class="pe-4 text-center">
                                <span class="badge {{ $e->status === 'validee' ? 'bg-label-success' : ($e->status === 'rejetee' ? 'bg-label-danger' : 'bg-label-warning') }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ $e->status === 'validee' ? 'Validée' : ($e->status === 'rejetee' ? 'Rejetée' : 'En attente') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-receipt fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucune dépense trouvée pour cette période.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($expenses->count() > 0)
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="4" class="ps-4 py-3">TOTAL</td>
                            <td class="text-end text-danger py-3">{{ number_format($expenses->sum('total_amount'), 0, ',', ' ') }} Ar</td>
                            <td class="pe-4"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
        .bg-label-warning { background-color: #fff2e2 !important; color: #ffab00 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
        .bg-label-secondary { background-color: #ebeef0 !important; color: #8592a3 !important; }
    </style>
    @endpush
</x-layouts.app>
