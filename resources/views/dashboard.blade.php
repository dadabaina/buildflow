<x-layouts.app title="Tableau de bord">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item active fw-bold text-dark">Tableau de bord</li>
    </x-slot>

    <!-- Welcome & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden" style="min-height: 180px;">
                <div class="card-body p-4 p-md-5 d-flex align-items-center">
                    <div class="w-100 w-md-75">
                        <h3 class="fw-bold text-white mb-2">Bon retour, {{ auth()->user()->name }} ! 👋</h3>
                        <p class="mb-4 opacity-75">Pilotez votre activité en temps réel. Vous avez {{ $activeProjectsCount }} chantiers actifs et {{ $pendingQuotesCount }} devis en attente.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('projects.create') }}" class="btn btn-white text-primary fw-bold shadow-sm">
                                <i class="bx bx-plus me-1"></i>Nouveau Projet
                            </a>
                            <a href="{{ route('expenses.index', ['status' => 'saisie']) }}" class="btn btn-outline-white fw-bold">
                                <i class="bx bx-check-shield me-1"></i>Valider Dépenses
                            </a>
                        </div>
                    </div>
                    <div class="d-none d-md-block ms-auto">
                        <img src="https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template-free/demo/assets/img/illustrations/man-with-laptop.png" 
                             height="140" alt="Dashboard Illustration" style="filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Alerts Row -->
    @if($allStockAlerts->count() > 0 || $overdueInvoicesCount > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-3">
                @if($overdueInvoicesCount > 0)
                <div class="alert alert-danger border-0 shadow-sm mb-0 flex-grow-1 d-flex align-items-center py-2 px-3">
                    <i class="bx bx-error-alt fs-4 me-2"></i>
                    <div>
                        <span class="fw-bold">{{ $overdueInvoicesCount }} factures impayées</span> ({{ number_format($totalOverdueAmount, 0, ',', ' ') }} Ar)
                        <a href="{{ route('reports.index') }}" class="ms-2 text-danger text-decoration-underline small">Relancer</a>
                    </div>
                </div>
                @endif
                @if($allStockAlerts->count() > 0)
                <div class="alert alert-warning border-0 shadow-sm mb-0 flex-grow-1 d-flex align-items-center py-2 px-3">
                    <i class="bx bx-package fs-4 me-2"></i>
                    <div>
                        <span class="fw-bold">{{ $allStockAlerts->count() }} alertes stock</span> sur vos chantiers.
                        <a href="{{ route('warehouse.index') }}" class="ms-2 text-warning text-decoration-underline small">Voir</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Health Score & KPIs Row -->
    <div class="row g-4 mb-4">
        <!-- Project Health Monitor -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-pulse me-2 text-success"></i>Santé des chantiers actifs</h6>
                    <span class="badge bg-label-success">{{ $activeProjectsCount }} en cours</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Chantier</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Avancement Physique</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Budget Consommé</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Statut Financier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeProjectsHealth as $health)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $health['name'] }}</div>
                                        <small class="text-muted">{{ $health['reference'] }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $health['progress_percent'] }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $health['progress_percent'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress w-100 me-2" style="height: 6px;">
                                                <div class="progress-bar {{ $health['budget_consumption_percent'] > 90 ? 'bg-danger' : ($health['budget_consumption_percent'] > $health['progress_percent'] ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ min(100, $health['budget_consumption_percent']) }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $health['budget_consumption_percent'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if($health['drift_alert'])
                                            <span class="badge bg-label-danger animate-pulse" title="Dépenses > Avancement">⚠️ Dérive</span>
                                        @else
                                            <span class="badge bg-label-success">Sain</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted small">Aucun chantier actif.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Forecast -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm h-100 bg-light-gradient">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-trending-up me-2 text-primary"></i>Trésorerie à 30 jours</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Entrées prévues</span>
                            <span class="text-success fw-bold">+ {{ number_format($cashFlow['expected_incomes'], 0, ',', ' ') }} Ar</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Sorties prévues</span>
                            <span class="text-danger fw-bold">- {{ number_format($cashFlow['expected_outcomes'], 0, ',', ' ') }} Ar</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Solde prévisionnel</span>
                            <span class="fw-bold {{ $cashFlow['net_forecast'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($cashFlow['net_forecast'], 0, ',', ' ') }} Ar
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-3 rounded bg-white shadow-sm border-start border-4 {{ $cashFlow['net_forecast'] >= 0 ? 'border-primary' : 'border-danger' }}">
                        @if($cashFlow['net_forecast'] >= 0)
                            <p class="small mb-0 text-muted"><i class="bx bx-check-circle text-success me-1"></i>Votre trésorerie prévisionnelle est positive. Vous pouvez envisager de nouveaux achats matériels.</p>
                        @else
                            <p class="small mb-0 text-muted"><i class="bx bx-error text-danger me-1"></i>Attention, vos sorties dépassent vos entrées prévues. Relancez vos impayés.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Quick Stats Cards -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md bg-label-primary rounded p-2">
                            <i class="bx bx-calendar fs-3"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">CA Mois</p>
                            <h5 class="mb-0 fw-bold mt-1 text-primary">{{ number_format($monthlyPayments, 0, ',', ' ') }} Ar</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md bg-label-warning rounded p-2">
                            <i class="bx bx-wallet fs-3"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Dépenses Mois</p>
                            <h5 class="mb-0 fw-bold mt-1 text-warning">{{ number_format($monthlyExpenses, 0, ',', ' ') }} Ar</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md bg-label-success rounded p-2">
                            <i class="bx bx-line-chart fs-3"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Résultat Mois</p>
                            <h5 class="mb-0 fw-bold mt-1 text-success">{{ number_format($monthlyPayments - $monthlyExpenses, 0, ',', ' ') }} Ar</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md bg-label-info rounded p-2">
                            <i class="bx bx-group fs-3"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Clients</p>
                            <h5 class="mb-0 fw-bold mt-1 text-info">{{ $totalClients }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Recent Projects Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom bg-transparent py-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-list-ul me-2 text-primary"></i>Derniers chantiers lancés</h6>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-label-primary">Tout voir</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Chantier</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Client</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Statut</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $project)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-label-primary me-3">
                                                <i class="bx bx-building fs-4"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('projects.show', $project) }}" class="fw-bold text-dark text-decoration-none d-block">
                                                    {{ $project->name }}
                                                </a>
                                                <small class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $project->reference }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark small fw-medium">{{ $project->client?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $project->status_badge_class }} badge-sm text-uppercase">
                                            {{ $project->status_libelle }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-icon btn-sm btn-label-primary">
                                            <i class="bx bx-chevron-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 text-muted small">Aucun projet enregistré.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Expenses List -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom bg-transparent py-4">
                    <h6 class="mb-0 fw-bold text-warning"><i class="bx bx-error-circle me-2"></i>Dépenses à valider</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pendingExpenses as $expense)
                            <a href="{{ route('expenses.show', $expense) }}" class="list-group-item list-group-item-action border-0 border-bottom border-light px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">{{ Str::limit($expense->description, 25) }}</span>
                                    <span class="fw-bold text-danger small">{{ number_format($expense->total_amount, 0, ',', ' ') }} Ar</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-label-secondary badge-xs text-uppercase" style="font-size: 0.6rem;">{{ $expense->category?->name ?? 'Dépense' }}</span>
                                    <small class="text-muted" style="font-size: 0.65rem;">{{ $expense->expense_date?->format('d/m/Y') }}</small>
                                </div>
                            </a>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <i class="bx bx-check-double fs-1 opacity-25 d-block mb-3"></i>
                                <h6 class="text-muted">Tout est validé !</h6>
                                <p class="small text-muted mb-0">Aucune dépense en attente de traitement.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @if($pendingExpenses->count() > 0)
                <div class="card-footer bg-transparent border-0 p-4">
                    <a href="{{ route('expenses.index', ['status' => 'saisie']) }}" class="btn btn-warning text-dark fw-bold w-100 shadow-sm">
                        Tout traiter
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .btn-white { background-color: #fff; color: #696cff; border: none; }
        .btn-white:hover { background-color: #f1f3f5; color: #696cff; }
        .btn-outline-white { border: 1.5px solid rgba(255,255,255,0.5); color: #fff; background: transparent; }
        .btn-outline-white:hover { background: rgba(255,255,255,0.1); color: #fff; border-color: #fff; }
        .bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
        .bg-label-warning { background-color: #fff2e2 !important; color: #ffab00 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
        .bg-label-secondary { background-color: #ebeef0 !important; color: #8592a3 !important; }
        .badge-xs { padding: 0.2rem 0.4rem; font-size: 0.6rem; }
        .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
        .bg-light-gradient { background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%); }
    </style>
    @endpush
</x-layouts.app>
