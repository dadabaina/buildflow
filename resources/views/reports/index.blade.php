<x-layouts.app title="Analyses & Rapports">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Rapports</li>
    </x-slot>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-bar-chart-square fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Centre d'Analyses & Rapports</h4>
                                <p class="text-muted small mb-0">Consultez vos indicateurs de performance et exportez vos données stratégiques.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-label-success p-2 rounded"><i class="bx bx-trending-up fs-3"></i></span>
                    </div>
                    <p class="mb-0 text-muted small text-uppercase fw-semibold">Chiffre d'Affaires</p>
                    <h4 class="mb-0 fw-bold mt-1 text-nowrap">{{ number_format($stats['total_invoiced'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-label-info p-2 rounded"><i class="bx bx-wallet fs-3"></i></span>
                    </div>
                    <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Encaissé</p>
                    <h4 class="mb-0 fw-bold mt-1 text-nowrap text-info">{{ number_format($stats['total_paid'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-label-danger p-2 rounded"><i class="bx bx-money-withdraw fs-3"></i></span>
                    </div>
                    <p class="mb-0 text-muted small text-uppercase fw-semibold">Dépenses Validées</p>
                    <h4 class="mb-0 fw-bold mt-1 text-nowrap text-danger">{{ number_format($stats['total_expenses'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-label-{{ $stats['balance'] >= 0 ? 'success' : 'danger' }} p-2 rounded"><i class="bx bx-line-chart fs-3"></i></span>
                    </div>
                    <p class="mb-0 text-muted small text-uppercase fw-semibold">Marge Brute (Cash)</p>
                    <h4 class="mb-0 fw-bold mt-1 text-nowrap {{ $stats['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($stats['balance'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modules -->
    <div class="row g-4" id="tour-reports-list">
        <!-- 0. Analyse Prévu vs Réel -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow bg-label-success bg-opacity-10 border border-success border-dashed">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg bg-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white">
                        <i class="bx bx-analyse fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Prévu vs Réel</h5>
                    <p class="text-muted small mb-4">Analyse chirurgicale de la rentabilité basée sur les dosages et DBE.</p>
                    <a href="{{ route('reports.planned-vs-real') }}" class="btn btn-success w-100 shadow-none">
                        Lancer l'analyse
                    </a>
                </div>
            </div>
        </div>

        <!-- 1. Financier -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg bg-label-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-pie-chart-alt fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Rapport Financier</h5>
                    <p class="text-muted small mb-4">Analyse du CA, des marges et du flux de trésorerie mensuel.</p>
                    <a href="{{ route('reports.financial') }}" class="btn btn-label-success w-100 shadow-none">
                        Ouvrir le rapport
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Chantiers -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg bg-label-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-buildings fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Suivi Chantiers</h5>
                    <p class="text-muted small mb-4">État d'avancement, rentabilité par projet et budget vs réel.</p>
                    <a href="{{ route('reports.projects') }}" class="btn btn-label-primary w-100 shadow-none">
                        Ouvrir le rapport
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. Dépenses -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg bg-label-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-receipt fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Journal Dépenses</h5>
                    <p class="text-muted small mb-4">Journal détaillé des frais, achats matériaux et sous-traitance.</p>
                    <a href="{{ route('reports.expenses') }}" class="btn btn-label-warning w-100 shadow-none">
                        Ouvrir le rapport
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. RH / Pointage -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg bg-label-info rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-user-check fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Récap. Pointage</h5>
                    <p class="text-muted small mb-4">Analyse de la main d'œuvre, heures travaillées et présences.</p>
                    <a href="{{ route('reports.attendance') }}" class="btn btn-label-info w-100 shadow-none">
                        Ouvrir le rapport
                    </a>
                </div>
            </div>
        </div>

        <!-- 5. CR Chantier -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg bg-label-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-file-blank fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Comptes-rendus</h5>
                    <p class="text-muted small mb-4">Historique des réunions de chantier et décisions prises.</p>
                    <a href="{{ route('site-reports.index') }}" class="btn btn-label-secondary w-100 shadow-none">
                        Voir les CR
                    </a>
                </div>
            </div>
        </div>

        <!-- 6. Réceptions -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 transition-all hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg bg-label-dark rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-check-double fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">PV de Réception</h5>
                    <p class="text-muted small mb-4">Gestion des procès-verbaux et levées de réserves.</p>
                    <a href="{{ route('reception-reports.index') }}" class="btn btn-label-dark w-100 shadow-none">
                        Voir les PV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Indicators Breakdown -->
    <div class="row g-4 mt-2">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-stats me-2"></i>Points d'attention & Indicateurs</h6>
                </div>
                <div class="card-body mt-2">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border border-dashed h-100">
                                <small class="text-muted d-block mb-1">Chantiers Actifs</small>
                                <div class="d-flex align-items-center gap-2">
                                    <h3 class="mb-0 fw-bold">{{ $stats['active_projects'] }}</h3>
                                    <span class="text-muted">/ {{ $stats['projects_count'] }}</span>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $stats['projects_count'] > 0 ? ($stats['active_projects'] / $stats['projects_count'] * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border border-dashed h-100">
                                <small class="text-muted d-block mb-1">Effectif Total</small>
                                <div class="d-flex align-items-center gap-2">
                                    <h3 class="mb-0 fw-bold">{{ $stats['employees_count'] }}</h3>
                                    <span class="text-muted small">Collaborateurs</span>
                                </div>
                                <small class="text-muted mt-2 d-block">Inscrits dans l'entreprise</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border {{ $stats['overdue_invoices_count'] > 0 ? 'border-danger bg-label-danger' : '' }} h-100">
                                <small class="text-muted d-block mb-1">Factures en retard</small>
                                <div class="d-flex align-items-center gap-2">
                                    <h3 class="mb-0 fw-bold {{ $stats['overdue_invoices_count'] > 0 ? 'text-danger' : '' }}">{{ $stats['overdue_invoices_count'] }}</h3>
                                    <i class="bx bx-error-circle {{ $stats['overdue_invoices_count'] > 0 ? 'text-danger' : 'text-muted' }}"></i>
                                </div>
                                <small class="text-muted d-block mt-1">Échéance dépassée</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border {{ $stats['pending_expenses_count'] > 0 ? 'border-warning bg-label-warning' : '' }} h-100">
                                <small class="text-muted d-block mb-1">Dépenses à valider</small>
                                <div class="d-flex align-items-center gap-2">
                                    <h3 class="mb-0 fw-bold {{ $stats['pending_expenses_count'] > 0 ? 'text-warning' : '' }}">{{ $stats['pending_expenses_count'] }}</h3>
                                    <i class="bx bx-time-five {{ $stats['pending_expenses_count'] > 0 ? 'text-warning' : 'text-muted' }}"></i>
                                </div>
                                <small class="text-muted d-block mt-1">En attente de traitement</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted d-block mb-1">Reste à recouvrer (Factures non soldées)</small>
                                        <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['outstanding'], 0, ',', ' ') }} Ar</h3>
                                    </div>
                                    <i class="bx bx-money text-danger fs-1 opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm bg-label-primary">
                <div class="card-body p-4 text-center">
                    <i class="bx bx-export fs-1 mb-2"></i>
                    <h5 class="fw-bold mb-2">Exportation Globale</h5>
                    <p class="small text-muted mb-4">Besoin d'un récapitulatif complet pour votre comptabilité ? Exportez toutes vos données de l'année en cours.</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary shadow-sm">
                            <i class="bx bx-download me-1"></i>Journal Général (Excel)
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bx bx-file me-1"></i>Bilan Annuel (PDF)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
        .bg-label-warning { background-color: #fff2e2 !important; color: #ffab00 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
        .transition-all { transition: all 0.2s ease-in-out; }
    </style>
    @endpush
</x-layouts.app>
