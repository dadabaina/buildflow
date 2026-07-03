<x-layouts.app :title="$project->name">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none opacity-50 text-dark">Chantiers</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $project->name }}</li>
    </x-slot>

    @php
    $statusConfig = [
        'prospection'    => ['label' => 'Prospection',    'class' => 'badge-soft-info',      'icon' => 'bi-search'],
        'devis_en_cours' => ['label' => 'Devis en cours', 'class' => 'badge-soft-warning',   'icon' => 'bi-file-earmark-pencil'],
        'devis_envoye'   => ['label' => 'Devis envoyé',   'class' => 'badge-soft-primary',   'icon' => 'bi-send'],
        'en_cours'       => ['label' => 'En cours',       'class' => 'badge-soft-success',   'icon' => 'bi-play-fill'],
        'en_pause'       => ['label' => 'En pause',       'class' => 'badge-soft-warning',   'icon' => 'bi-pause-fill'],
        'termine'        => ['label' => 'Terminé',        'class' => 'badge-soft-primary',   'icon' => 'bi-check-all'],
        'cloture'        => ['label' => 'Clôturé',        'class' => 'badge-soft-secondary', 'icon' => 'bi-lock'],
        'annule'         => ['label' => 'Annulé',         'class' => 'badge-soft-danger',    'icon' => 'bi-x'],
    ];
    $statusLabels = array_map(fn($s) => $s['label'], $statusConfig);
    $sc = $statusConfig[$project->status] ?? ['label' => $project->status, 'class' => 'badge-soft-secondary', 'icon' => 'bi-info-circle'];
    $nextStatuses = App\Models\Project::$statusTransitions[$project->status] ?? [];
    @endphp

    {{-- Hero Section --}}
    <div class="card border-0 shadow-sm-app rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="p-4 p-md-5 position-relative" style="background: linear-gradient(135deg, #1e2a35 0%, #2c3e50 100%); color: white;">
                <div class="position-relative z-index-1">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <span class="badge rounded-pill {{ $sc['class'] }} bg-opacity-20 text-white border border-white border-opacity-25 px-3 py-2 mb-3">
                                <i class="{{ $sc['icon'] }} me-1"></i> {{ $sc['label'] }}
                            </span>
                            <h2 class="fw-bold mb-1 text-white">{{ $project->name }}</h2>
                            <p class="opacity-75 mb-0">
                                <i class="bi bi-hash me-1"></i>{{ $project->reference }} 
                                <span class="mx-2">·</span> 
                                <i class="bi bi-geo-alt me-1"></i>{{ $project->region?->name ?? 'Région non définie' }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            @can('projects.change_status')
                            @if(count($nextStatuses) > 0)
                            <div class="dropdown">
                                <button class="btn btn-warning fw-bold px-4 shadow-sm dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-arrow-right-circle me-2"></i>Changer le statut
                                </button>
                                <ul class="dropdown-menu shadow-lg border-0">
                                    @foreach($nextStatuses as $ns)
                                    <li>
                                        <form method="POST" action="{{ route('projects.status', $project) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $ns }}">
                                            <button type="submit" class="dropdown-item py-2">
                                                <i class="bi bi-arrow-right me-2 text-muted"></i>{{ $statusLabels[$ns] ?? $ns }}
                                            </button>
                                        </form>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @endcan
                            @can('projects.edit')
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-white fw-bold px-4 text-dark shadow-sm">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>Modifier
                            </a>
                            @endcan
                            <div class="dropdown">
                                <button class="btn btn-outline-light px-3 border-opacity-25" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-printer me-2 text-muted"></i> Imprimer fiche</a></li>
                                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-share me-2 text-muted"></i> Partager</a></li>
                                    @can('projects.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Supprimer ce chantier ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Supprimer</button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Background decorative element --}}
                <div class="position-absolute bottom-0 end-0 p-0 d-none d-lg-block text-white" style="pointer-events: none; opacity: 0.07; filter: blur(8px);">
                    <i class="bi bi-building-fill" style="font-size: 15rem; line-height: 1; margin-bottom: -4rem; margin-right: -2rem;"></i>
                </div>
            </div>
            
            {{-- Quick Stats Summary --}}
            <div class="bg-white p-4 border-top border-light">
                <div class="row g-4">
                    <div class="col-6 col-md border-end border-light">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium text-uppercase">Budget Global</div>
                                <div class="fw-bold text-dark fs-5">{{ number_format($project->budget, 0, ',', ' ') }} <small class="text-muted" style="font-size: 0.65rem">MGA</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md border-end border-light">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger-subtle text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-cash-stack fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium text-uppercase">Total Dépenses</div>
                                @php $totalExpenses = $project->expenses->sum('total_amount'); @endphp
                                <div class="fw-bold text-danger fs-5">{{ number_format($totalExpenses, 0, ',', ' ') }} <small class="opacity-50" style="font-size: 0.65rem">MGA</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md border-end border-light">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-graph-up-arrow fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-medium text-uppercase">Avancement</div>
                                @php
                                    $tTotal = $project->tasks->count();
                                    $tDone  = $project->tasks->where('status', 'termine')->count();
                                    $tPct   = $tTotal > 0 ? round($tDone / $tTotal * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-bold text-dark fs-5">{{ $tPct }}<small class="text-muted">%</small></div>
                                    <div class="progress flex-grow-1" style="height:6px; background-color: #f0f0f0;"><div class="progress-bar bg-success" style="width:{{ $tPct }}%"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md border-end border-light">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-piggy-bank fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium text-uppercase">
                                    Marge prév.
                                    <i class="bi bi-info-circle ms-1" style="cursor: help;"
                                       data-bs-toggle="tooltip" data-bs-placement="top"
                                       title="Part du Budget initial qui n'est pas encore consommée par les dépenses : (Budget initial − Total Dépenses) / Budget initial. Un chiffre négatif signifie que le budget prévu est dépassé."></i>
                                </div>
                                @php $marginPercent = $project->budget > 0 ? (($project->budget - $totalExpenses) / $project->budget) * 100 : 0; @endphp
                                <div class="fw-bold text-dark fs-5">{{ number_format($marginPercent, 1) }}%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="d-flex align-items-center gap-3">
                            @php $marketRatio = $project->total_market_amount > 0 ? ($totalExpenses / $project->total_market_amount) * 100 : 0; @endphp
                            <div class="bg-{{ $marketRatio > 100 ? 'danger' : 'warning' }}-subtle text-{{ $marketRatio > 100 ? 'danger' : 'warning' }} rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-percent fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium text-uppercase">
                                    Dépenses / Marché
                                    <i class="bi bi-info-circle ms-1" style="cursor: help;"
                                       data-bs-toggle="tooltip" data-bs-placement="top"
                                       title="Part du Montant Total Marché (ce que le client doit payer) déjà consommée par les dépenses réelles : Total Dépenses / Montant Total Marché. Au-delà de 100%, les dépenses dépassent le montant du marché."></i>
                                </div>
                                <div class="fw-bold fs-5 text-{{ $marketRatio > 100 ? 'danger' : 'dark' }}">{{ number_format($marketRatio, 1) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content & Tabs --}}
    <div x-data="{ activeTab: 'infos' }">
        <div class="card border-0 shadow-sm-app rounded-4 mb-4 bg-white">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-justified gap-1 flex-wrap project-nav-tabs">
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'infos' }" @click="activeTab = 'infos'">
                            <i class="bi bi-info-circle"></i> <span>Vue d'ensemble</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'team' }" @click="activeTab = 'team'">
                            <i class="bi bi-people"></i> <span>Équipe</span>
                            <span class="badge rounded-pill bg-light text-primary ms-1">{{ $project->employees->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'expenses' }" @click="activeTab = 'expenses'">
                            <i class="bi bi-receipt"></i> <span>Dépenses</span>
                            <span class="badge rounded-pill bg-light text-primary ms-1">{{ $project->expenses->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'bc' }" @click="activeTab = 'bc'">
                            <i class="bi bi-cart3"></i> <span>BCs</span>
                            <span class="badge rounded-pill bg-light text-primary ms-1">{{ $project->purchaseOrders->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'tasks' }" @click="activeTab = 'tasks'">
                            <i class="bi bi-check2-square"></i> <span>Tâches</span>
                            <span class="badge rounded-pill bg-light text-primary ms-1">{{ $project->tasks->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'pointage' }" @click="activeTab = 'pointage'">
                            <i class="bi bi-clock-history"></i> <span>Pointage</span>
                            <span class="badge rounded-pill bg-light text-primary ms-1">{{ $project->attendances->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'documents' }" @click="activeTab = 'documents'">
                            <i class="bi bi-file-earmark-zip"></i> <span>Documents</span>
                            <span class="badge rounded-pill bg-light text-primary ms-1">{{ $project->documents->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'stock' }" @click="activeTab = 'stock'">
                            <i class="bi bi-box-seam"></i> <span>Stock Site</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'equipment' }" @click="activeTab = 'equipment'">
                            <i class="bi bi-truck"></i> <span>Équipements</span>
                            @php $eqCount = $project->projectAssignments->count(); @endphp
                            <span class="badge rounded-pill bg-light text-primary ms-1">{{ $eqCount }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'quotes' }" @click="activeTab = 'quotes'">
                            <i class="bi bi-file-earmark-text"></i> <span>Devis & Factures</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-3 py-2 px-3 d-flex align-items-center justify-content-center gap-2" :class="{ 'active shadow-sm': activeTab === 'history' }" @click="activeTab = 'history'">
                            <i class="bi bi-clock-history"></i> <span>Historique</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Tab Panes --}}
        <div class="tab-content">
            {{-- Vue d'ensemble --}}
            <div x-show="activeTab === 'infos'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <x-card title="Détails du chantier" icon="bi bi-card-text">
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Client</label>
                                    @if($project->client)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <a href="{{ route('clients.show', $project->client) }}" class="text-decoration-none fw-bold text-dark">
                                                {{ $project->client->name }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Période du chantier</label>
                                    <div class="fw-bold text-dark">
                                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                                        {{ $project->start_date?->format('d M Y') ?? '—' }} 
                                        <i class="bi bi-arrow-right mx-2 text-muted"></i>
                                        {{ $project->end_date?->format('d M Y') ?? '—' }}
                                    </div>
                                </div>
                                @if($project->description)
                                <div class="col-12 mt-4">
                                    <label class="text-muted small text-uppercase fw-bold mb-2 d-block">Description / Objectifs</label>
                                    <div class="p-3 bg-light rounded-3 text-secondary border-0" style="white-space: pre-line;">{{ $project->description }}</div>
                                </div>
                                @endif
                            </div>
                        </x-card>
                    </div>
                    <div class="col-lg-5">
                        <x-card title="Récapitulatif Financier" icon="bi bi-pie-chart">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <span class="text-muted">Budget initial</span>
                                    <span class="fw-bold text-dark">{{ number_format($project->budget, 0, ',', ' ') }} MGA</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <span class="text-muted">Montant initial (TTC)</span>
                                    <span class="fw-bold text-dark">{{ number_format($project->contract_amount, 0, ',', ' ') }} MGA</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <span class="text-muted">Total Avenants</span>
                                    <span class="fw-bold text-info">+ {{ number_format($project->total_amendments, 0, ',', ' ') }} MGA</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <span class="text-muted">Montant Total Marché</span>
                                    <span class="fw-bold text-primary">{{ number_format($project->total_market_amount, 0, ',', ' ') }} MGA</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <span class="text-muted">Avance perçue</span>
                                    <span class="fw-bold text-success">{{ number_format($project->advance_received, 0, ',', ' ') }} MGA</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <span class="text-muted">Reste à facturer</span>
                                    <span class="fw-bold text-warning">{{ number_format($project->total_market_amount - $project->advance_received, 0, ',', ' ') }} MGA</span>
                                </li>
                            </ul>
                        </x-card>

                        {{-- Activité Récente --}}
                        <x-card title="Activité Récente" icon="bi bi-activity" class="mt-4 shadow-none border">
                            <div class="mt-2">
                                @forelse($project->projectLogs->take(8) as $log)
                                    <div class="d-flex mb-3">
                                        <div class="flex-shrink-0 text-center" style="width: 32px;">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; margin: 0 auto;">
                                                <i class="bi {{ match($log->action) {
                                                    'quote_accepted' => 'bi-check-all',
                                                    'status_updated' => 'bi-arrow-repeat',
                                                    'task_status_updated' => 'bi-list-check',
                                                    'stock_movement' => 'bi-box-seam',
                                                    'team_updated' => 'bi-people',
                                                    'employee_removed' => 'bi-person-dash',
                                                    'equipment_assigned' => 'bi-truck',
                                                    'equipment_removed' => 'bi-box-arrow-right',
                                                    default => 'bi-info-circle'
                                                } }} small" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <div class="fw-bold text-dark small" style="line-height: 1.2; font-size: 0.75rem;">{{ $log->description }}</div>
                                            <div class="text-muted small mt-1" style="font-size: 0.65rem;">
                                                {{ $log->created_at->diffForHumans() }} 
                                                @if($log->user) · {{ $log->user->name }} @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-3 text-muted small">Aucune activité récente.</div>
                                @endforelse
                            </div>
                            <div class="text-center mt-2 border-top pt-2">
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-bold" @click="activeTab = 'history'">Voir tout l'historique <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>

            {{-- Équipe --}}
            <div x-show="activeTab === 'team'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <div class="row g-4">
                    {{-- Plan d'effectif (Besoins) --}}
                    <div class="col-lg-5">
                        <x-card title="Plan d'effectif" icon="bi bi-clipboard-check" subtitle="Définissez les besoins pour ce chantier">
                            @can('projects.edit')
                            <form method="POST" action="{{ route('projects.requirements.store', $project) }}" class="mb-4 p-3 bg-light rounded-4">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Poste requis</label>
                                        <select name="job_type_id" class="form-select form-select-sm" required>
                                            <option value="">Choisir...</option>
                                            @foreach($jobTypes->groupBy('job_category_id') as $catId => $types)
                                                <optgroup label="{{ $types->first()->category->name ?? 'Sans catégorie' }}">
                                                    @foreach($types as $jt)
                                                        <option value="{{ $jt->id }}">{{ $jt->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Nombre</label>
                                        <input type="number" name="needed_quantity" class="form-control form-control-sm" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">Ajouter</button>
                                    </div>
                                </div>
                            </form>
                            @endcan

                            <div class="list-group list-group-flush border rounded-4 overflow-hidden shadow-sm">
                                @php 
                                    $assignedCounts = [];
                                    foreach($project->employees as $emp) {
                                        foreach($emp->jobTypes as $jt) {
                                            $assignedCounts[$jt->id] = ($assignedCounts[$jt->id] ?? 0) + 1;
                                        }
                                    }
                                @endphp
                                @forelse($project->requirements->groupBy(fn($r) => $r->jobType->job_category_id) as $catId => $catRequirements)
                                    <div class="bg-primary bg-opacity-10 px-3 py-2 small fw-bold text-primary border-bottom">
                                        <i class="bi bi-folder2-open me-2"></i>
                                        {{ $catRequirements->first()->jobType->category->name ?? 'Sans catégorie' }}
                                    </div>
                                    @foreach($catRequirements as $req)
                                        @php 
                                            $current = $assignedCounts[$req->job_type_id] ?? 0;
                                            $isMet = $current >= $req->needed_quantity;
                                        @endphp
                                        <div class="list-group-item p-3 border-bottom-0">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-dark small">{{ $req->jobType->name }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    @can('projects.edit')
                                                    <form method="POST" action="{{ route('projects.requirements.destroy', [$project, $req]) }}" onsubmit="return confirm('Supprimer ce besoin ?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-link text-danger p-0 border-0 shadow-none"><i class="bi bi-trash small"></i></button>
                                                    </form>
                                                    @endcan
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="flex-grow-1">
                                                    <div class="progress" style="height: 6px; background-color: rgba(0,0,0,0.05);">
                                                        <div class="progress-bar {{ $isMet ? 'bg-success' : 'bg-warning shadow-none' }} progress-bar-striped progress-bar-animated" 
                                                             style="width: {{ $req->needed_quantity > 0 ? min(100, ($current / $req->needed_quantity) * 100) : 0 }}%"></div>
                                                    </div>
                                                </div>
                                                <div class="small fw-bold {{ $isMet ? 'text-success' : 'text-warning' }}" style="font-size: 0.75rem; min-width: 45px; text-align: right;">
                                                    {{ $current }} / {{ $req->needed_quantity }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @empty
                                    <div class="p-5 text-center text-muted small">
                                        <i class="bi bi-clipboard-x display-6 d-block mb-3 opacity-25"></i>
                                        Aucun besoin défini pour le moment.
                                    </div>
                                @endforelse
                            </div>
                        </x-card>
                    </div>

                    {{-- Collaborateurs assignés --}}
                    <div class="col-lg-7">
                        <x-card title="Membres assignés" icon="bi bi-people-fill">
                            <x-slot name="headerActions">
                                @can('projects.edit')
                                <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm-app" data-bs-toggle="modal" data-bs-target="#assignEmployeeModal">
                                    <i class="bi bi-person-plus me-1"></i> Gérer l'équipe
                                </button>
                                @endcan
                            </x-slot>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="border-0">Collaborateur</th>
                                            <th class="border-0 text-center">Postes</th>
                                            <th class="border-0">Contact</th>
                                            <th class="border-0 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($project->employees as $emp)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-primary-subtle text-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.7rem;">
                                                        {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark small">{{ $emp->full_name }}</div>
                                                        <div class="text-muted" style="font-size: 0.65rem;">Matricule: {{ $emp->matricule ?? '—' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @foreach($emp->jobTypes as $jt)
                                                    <span class="badge bg-indigo-subtle text-indigo px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                                                        {{ $jt->name }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td><div style="font-size: 0.75rem;"><i class="bi bi-telephone me-1 text-muted"></i>{{ $emp->phone ?? '—' }}</div></td>
                                            <td class="text-end">
                                                <a href="{{ route('employees.show', $emp) }}" class="btn-action-view me-1"><i class="bi bi-eye"></i></a>
                                                @can('projects.edit')
                                                <form action="{{ route('projects.employees.detach', [$project, $emp]) }}" method="POST" class="d-inline" onsubmit="return confirm('Retirer ce collaborateur du chantier ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 border-0 shadow-none"><i class="bi bi-person-dash fs-5"></i></button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-5 text-muted">Aucun membre assigné.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>

            {{-- Dépenses --}}
            <div x-show="activeTab === 'expenses'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <x-card title="Journal des dépenses" icon="bi bi-receipt">
                    <x-slot name="headerActions">
                        @can('expenses.create')
                        <a href="{{ route('expenses.create', ['project_id' => $project->id]) }}" class="btn btn-primary shadow-app">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter une dépense
                        </a>
                        @endcan
                    </x-slot>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="border-0">Description</th>
                                    <th class="border-0 text-center">Catégorie</th>
                                    <th class="border-0 text-end">Montant</th>
                                    <th class="border-0 text-center">Date</th>
                                    <th class="border-0 text-center">Statut</th>
                                    <th class="border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->expenses as $exp)
                                <tr>
                                    <td><a href="{{ route('expenses.show', $exp) }}" class="text-decoration-none fw-bold text-dark">{{ Str::limit($exp->description, 50) }}</a></td>
                                    <td class="text-center"><span class="badge bg-light text-muted border px-2 py-1">{{ $exp->expenseCategory?->name ?? '—' }}</span></td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($exp->total_amount, 0, ',', ' ') }} <small>MGA</small></td>
                                    <td class="text-center small text-muted">{{ $exp->expense_date?->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        @php 
                                            $esc = [
                                                'pending' => 'badge-soft-warning', 
                                                'validated' => 'badge-soft-success', 
                                                'rejected' => 'badge-soft-danger',
                                                'saisie' => 'badge-soft-secondary'
                                            ]; 
                                        @endphp
                                        <span class="badge rounded-pill {{ $esc[$exp->status] ?? 'badge-soft-secondary' }} px-3 py-1">{{ ucfirst($exp->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('expenses.show', $exp) }}" class="btn-action-view"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">Aucune dépense enregistrée.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- Bons de commande --}}
            <div x-show="activeTab === 'bc'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                @php
                    $pendingBC = $project->purchaseOrders->whereIn('status', ['brouillon','envoye','partiellement_livre'])->sum('total_ttc');
                @endphp
                @if($pendingBC > 0)
                <div class="alert alert-warning d-flex align-items-center mb-3">
                    <i class="bi bi-clock me-2"></i>
                    Total commandes en attente : <strong class="ms-2">{{ number_format($pendingBC, 0, ',', ' ') }} MGA</strong>
                </div>
                @endif
                <x-card title="Bons de commande" icon="bi bi-cart3">
                    <x-slot name="headerActions">
                        @can('purchase_orders.create')
                        <a href="{{ route('purchase-orders.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary px-3 shadow-sm-app">
                            <i class="bi bi-plus-lg me-1"></i>Nouveau BC
                        </a>
                        @endcan
                    </x-slot>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Référence</th>
                                    <th class="border-0">Fournisseur</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Total TTC</th>
                                    <th class="border-0">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->purchaseOrders as $po)
                                <tr>
                                    <td><a href="{{ route('purchase-orders.show', $po) }}" class="font-monospace small text-decoration-none fw-bold text-primary">{{ $po->reference }}</a></td>
                                    <td class="text-muted small">{{ $po->supplier->name ?? '—' }}</td>
                                    <td class="text-muted small">{{ $po->order_date->format('d/m/Y') }}</td>
                                    <td class="fw-bold small">{{ number_format($po->total_ttc, 0, ',', ' ') }} <small>MGA</small></td>
                                    <td><span class="badge {{ $po->status_badge_class }} px-2 py-1 rounded-pill" style="font-size:0.7rem">{{ $po->status_libelle }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">Aucun bon de commande.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- Tâches --}}
            <div x-show="activeTab === 'tasks'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                @php
                    $tasksDone  = $project->tasks->where('status', 'termine')->count();
                    $tasksTotal = $project->tasks->count();
                    $tasksPct   = $tasksTotal > 0 ? round($tasksDone / $tasksTotal * 100) : 0;
                @endphp
                @if($tasksTotal > 0)
                <div class="card border-0 shadow-sm-app rounded-4 mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark">Avancement : {{ $tasksDone }}/{{ $tasksTotal }} tâches terminées</span>
                        <span class="fw-bold text-primary">{{ $tasksPct }}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ $tasksPct }}%"></div>
                    </div>
                </div>
                @endif
                <x-card title="Tâches du chantier" icon="bi bi-check2-square">
                    <x-slot name="headerActions">
                        @can('tasks.create')
                        <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary px-3 shadow-sm-app">
                            <i class="bi bi-plus-lg me-1"></i>Nouvelle tâche
                        </a>
                        @endcan
                        <a href="{{ route('tasks.kanban', ['project_id' => $project->id]) }}" class="btn btn-sm btn-light border ms-2">
                            <i class="bi bi-kanban me-1"></i>Kanban
                        </a>
                    </x-slot>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Tâche</th>
                                    <th class="border-0">Priorité</th>
                                    <th class="border-0">Progression</th>
                                    <th class="border-0">Échéance</th>
                                    <th class="border-0">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->tasks as $task)
                                <tr>
                                    <td>
                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none fw-bold text-dark">{{ Str::limit($task->title, 50) }}</a>
                                        @if($task->isOverdue())
                                            <span class="badge bg-danger ms-2 small">En retard</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $task->priority_badge_class }} px-2 rounded-pill small">{{ ucfirst($task->priority) }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar {{ $task->progress_percent == 100 ? 'bg-success' : 'bg-primary' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $task->progress_percent }}%"></div>
                                            </div>
                                            <span class="small fw-bold text-muted">{{ $task->progress_percent }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-muted small {{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $task->due_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td><span class="badge {{ $task->status_badge_class }} px-2 py-1 rounded-pill" style="font-size:0.7rem">{{ $task->status_libelle }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">Aucune tâche.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- Pointage --}}
            <div x-show="activeTab === 'pointage'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <x-card title="Pointages récents" icon="bi bi-clock-history">
                    <x-slot name="headerActions">
                        @can('attendances.create')
                        <a href="{{ route('attendances.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary px-3 shadow-sm-app">
                            <i class="bi bi-plus-lg me-1"></i>Saisir pointage
                        </a>
                        @endcan
                        <a href="{{ route('attendances.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-light border ms-2">
                            <i class="bi bi-list me-1"></i>Voir tout
                        </a>
                    </x-slot>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Salarié</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Entrée</th>
                                    <th class="border-0">Sortie</th>
                                    <th class="border-0">Heures</th>
                                    <th class="border-0">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->attendances as $att)
                                <tr>
                                    <td class="fw-bold small">{{ $att->employee->full_name ?? '—' }}</td>
                                    <td class="text-muted small">{{ $att->work_date->format('d/m/Y') }}</td>
                                    <td class="text-muted small">{{ $att->check_in ?? '—' }}</td>
                                    <td class="text-muted small">{{ $att->check_out ?? '—' }}</td>
                                    <td class="fw-bold small">{{ $att->hours_worked ? number_format($att->hours_worked, 1).'h' : '—' }}</td>
                                    <td><span class="badge {{ $att->status_badge_class }} px-2 py-1 rounded-pill" style="font-size:0.7rem">{{ $att->status_libelle }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">Aucun pointage.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- Documents --}}
            <div x-show="activeTab === 'documents'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <x-card title="Documents du chantier" icon="bi bi-file-earmark-zip">
                    <x-slot name="headerActions">
                        @can('documents.create')
                        <a href="{{ route('documents.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary px-3 shadow-sm-app">
                            <i class="bi bi-plus-lg me-1"></i>Ajouter
                        </a>
                        @endcan
                    </x-slot>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Document</th>
                                    <th class="border-0">Catégorie</th>
                                    <th class="border-0">Taille / Ver.</th>
                                    <th class="border-0">Ajouté par</th>
                                    <th class="border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->documents as $doc)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light-subtle border rounded p-2 me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="bi {{ $doc->isPdf() ? 'bi-file-earmark-pdf text-danger' : ($doc->isImage() ? 'bi-file-earmark-image text-success' : 'bi-file-earmark-text text-primary') }} fs-4"></i>
                                            </div>
                                            <div>
                                                <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="text-decoration-none fw-bold text-dark">
                                                    {{ $doc->original_name }}
                                                </a>
                                                @if($doc->notes)
                                                    <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $doc->notes }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-muted border px-2 py-1 small">{{ $doc->category_libelle }}</span></td>
                                    <td>
                                        <div class="small fw-medium">{{ $doc->file_size_formatted }}</div>
                                        <div class="text-muted small">v{{ $doc->version }}</div>
                                    </td>
                                    <td>
                                        <div class="small fw-medium">{{ $doc->uploadedBy->name ?? 'Système' }}</div>
                                        <div class="text-muted small">{{ $doc->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="btn btn-sm btn-icon btn-label-primary" title="Voir"><i class="bx bx-show"></i></a>
                                            <a href="{{ asset('storage/' . $doc->path) }}" download class="btn btn-sm btn-icon btn-label-info" title="Télécharger"><i class="bx bx-download"></i></a>
                                            @can('documents.delete')
                                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce document ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger" title="Supprimer"><i class="bx bx-trash"></i></button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">Aucun document pour ce chantier.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- Stock Site --}}
            <div x-show="activeTab === 'stock'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                
                @if($stockAlerts->isNotEmpty())
                <div class="alert alert-danger d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>
                        <span class="fw-bold">Alerte Stock Bas :</span> 
                        Les consommables suivants sont sous le seuil critique :
                        <div class="mt-1">
                            @foreach($stockAlerts as $alert)
                                <span class="badge bg-danger me-1">
                                    {{ $alert['material']->name }} : {{ number_format($alert['current'], 2) }} / {{ number_format($alert['min'], 2) }} {{ $alert['material']->unit }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="row g-4">
                    <div class="col-lg-8">
                        <x-card title="Inventaire actuel sur site" icon="bi bi-box-seam">
                            <x-slot name="headerActions">
                                @can('stock.create')
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#setThresholdModal">
                                        <i class="bi bi-bell me-1"></i> Configurer alertes
                                    </button>
                                    <a href="{{ route('stock-movements.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Nouveau mouvement
                                    </a>
                                </div>
                                @endcan
                            </x-slot>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">Désignation</th>
                                            <th class="border-0 text-center">Unité</th>
                                            <th class="border-0 text-center">Seuil Alerte</th>
                                            <th class="border-0 text-end">Quantité en stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($stockByItem as $item)
                                        @php 
                                            // Essayer de trouver le matériau correspondant pour afficher le seuil
                                            $material = $materials->where('name', $item->item_name)->first();
                                            $threshold = $project->materialThresholds->where('material_id', $material?->id)->first();
                                            $isLow = $threshold && $item->balance <= $threshold->min_threshold;
                                        @endphp
                                        <tr class="{{ $isLow ? 'table-danger-subtle' : '' }}">
                                            <td class="fw-bold text-dark">
                                                {{ $item->item_name }}
                                                @if($isLow) <i class="bi bi-exclamation-circle-fill text-danger ms-1" title="Stock critique"></i> @endif
                                            </td>
                                            <td class="text-center"><span class="badge bg-light text-muted border">{{ $item->unit }}</span></td>
                                            <td class="text-center">
                                                <span class="text-muted small">
                                                    {{ $threshold ? number_format($threshold->min_threshold, 2) : '—' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fs-5 fw-bold {{ $isLow ? 'text-danger' : 'text-primary' }}">{{ number_format($item->balance, 2, ',', ' ') }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-5 text-muted">Aucun matériau en stock sur ce chantier.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </x-card>
                    </div>
                    <div class="col-lg-4">
                        <x-card title="Dépôts rattachés" icon="bi bi-building">
                            @forelse($project->warehouses as $wh)
                            <div class="d-flex align-items-center mb-3 p-3 border rounded-3">
                                <div class="bg-info-subtle text-info rounded-circle p-2 me-3">
                                    <i class="bi bi-house-door fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark">{{ $wh->name }}</div>
                                    <div class="text-muted small">{{ $wh->location ?? 'Emplacement non défini' }}</div>
                                </div>
                                <a href="{{ route('stock-movements.index', ['warehouse_id' => $wh->id]) }}" class="btn btn-sm btn-light border" title="Voir l'historique">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                            </div>
                            @empty
                            <div class="text-center py-4 text-muted small">
                                Aucun dépôt spécifique n'est lié à ce chantier.<br>
                                <a href="{{ route('warehouses.create', ['project_id' => $project->id]) }}" class="mt-2 d-inline-block">Lier un dépôt maintenant</a>
                            </div>
                            @endforelse
                        </x-card>
                    </div>
                </div>
            </div>

            {{-- Équipements & Matériel --}}
            <div x-show="activeTab === 'equipment'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <x-card title="Matériel & Équipements affectés" icon="bi bi-truck">
                    <x-slot name="headerActions">
                        @can('projects.edit')
                        <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm-app" data-bs-toggle="modal" data-bs-target="#assignEquipmentModal">
                            <i class="bi bi-plus-lg me-1"></i> Affecter du matériel
                        </button>
                        @endcan
                    </x-slot>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Désignation</th>
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Période d'affectation</th>
                                    <th class="border-0">Décompte</th>
                                    <th class="border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->projectAssignments as $assign)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light border rounded p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-tools fs-5 text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small">{{ $assign->equipment->name }}</div>
                                                <div class="text-muted" style="font-size: 0.65rem;">Réf: {{ $assign->equipment->reference ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($assign->equipment->is_internal)
                                            <span class="badge bg-label-info">Interne</span>
                                        @else
                                            <span class="badge bg-label-warning">Location</span>
                                            <div class="text-muted small mt-1" style="font-size: 0.6rem;">{{ $assign->equipment->supplier->name ?? 'Loueur inconnu' }}</div>
                                        @endif
                                    </td>
                                    <td class="small">
                                        <div class="fw-medium text-dark">Du {{ $assign->start_date?->format('d/m/Y') ?? '—' }}</div>
                                        <div class="text-muted">Au {{ $assign->end_date?->format('d/m/Y') ?? 'Indéterminé' }}</div>
                                    </td>
                                    <td>
                                        @if($assign->end_date)
                                            @php $days = $assign->days_remaining; @endphp
                                            @if($days < 0)
                                                <span class="badge bg-danger">Date dépassée ({{ abs($days) }} j)</span>
                                            @elseif($days <= 2)
                                                <span class="badge bg-warning text-dark">À rendre bientôt ({{ $days }} j)</span>
                                            @else
                                                <span class="badge bg-success">{{ $days }} jours restants</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('projects.edit')
                                        <form action="{{ route('projects.equipments.detach', [$project, $assign]) }}" method="POST" class="d-inline" onsubmit="return confirm('Libérer ce matériel du chantier ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 border-0 shadow-none"><i class="bi bi-trash fs-5"></i></button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">Aucun matériel lourd affecté à ce chantier.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            {{-- Devis & Factures --}}
            <div x-show="activeTab === 'quotes'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-card title="Devis associés" icon="bi bi-file-earmark-text" class="mb-4">
                            <x-slot name="headerActions">
                                @can('quotes.create')
                                <a href="{{ route('quotes.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-light border shadow-sm-app">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                                @endcan
                            </x-slot>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead><tr><th>Réf</th><th>Total TTC</th><th>Statut</th></tr></thead>
                                    <tbody>
                                        @forelse($project->quotes as $quote)
                                        <tr>
                                            <td><a href="{{ route('quotes.show', $quote) }}" class="font-monospace small text-decoration-none fw-bold text-primary">{{ $quote->reference }}</a></td>
                                            <td class="fw-bold small">{{ number_format($quote->total_ttc, 0, ',', ' ') }}</td>
                                            <td><span class="badge-soft-secondary px-2 py-1 rounded-pill" style="font-size: 0.7rem">{{ $quote->status }}</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-3 text-muted small">Aucun devis.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </x-card>

                        <x-card title="Avenants (Marché)" icon="bi bi-file-earmark-plus">
                            <x-slot name="headerActions">
                                @can('amendments.create')
                                <a href="{{ route('amendments.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-light border shadow-sm-app">
                                    <i class="bi bi-plus-lg"></i>
                                </a>
                                @endcan
                            </x-slot>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead><tr><th>Réf</th><th>Total TTC</th><th>Statut</th></tr></thead>
                                    <tbody>
                                        @forelse($project->amendments as $amendment)
                                        <tr>
                                            <td><a href="{{ route('amendments.show', $amendment) }}" class="font-monospace small text-decoration-none fw-bold text-info">{{ $amendment->reference }}</a></td>
                                            <td class="fw-bold small">{{ number_format($amendment->total_ttc, 0, ',', ' ') }}</td>
                                            <td><span class="badge {{ $amendment->status_badge_class }} px-2 py-1 rounded-pill" style="font-size: 0.7rem">{{ $amendment->status_libelle }}</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-3 text-muted small">Aucun avenant.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </x-card>
                    </div>

                    <div class="col-md-6">
                        <x-card title="Factures émises" icon="bi bi-file-earmark-check" class="h-100">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead><tr><th>Réf</th><th>Total TTC</th><th>Statut</th></tr></thead>
                                    <tbody>
                                        @forelse($project->invoices as $invoice)
                                        <tr>
                                            <td><a href="{{ route('invoices.show', $invoice) }}" class="font-monospace small text-decoration-none fw-bold text-info">{{ $invoice->reference }}</a></td>
                                            <td class="fw-bold small">{{ number_format($invoice->total_ttc, 0, ',', ' ') }}</td>
                                            <td><span class="badge-soft-info px-2 py-1 rounded-pill" style="font-size: 0.7rem">{{ $invoice->status }}</span></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-3 text-muted small">Aucune facture.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>

            {{-- Historique --}}
            <div x-show="activeTab === 'history'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2">
                <x-card title="Journal d'activité du chantier" icon="bi bi-clock-history">
                    <div class="timeline p-2">
                        @forelse($project->projectLogs as $log)
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0 text-center" style="width: 45px;">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 35px; height: 35px;">
                                        <i class="bi {{ match($log->action) {
                                            'quote_accepted' => 'bi-check-all',
                                            'status_updated' => 'bi-arrow-repeat',
                                            'task_status_updated' => 'bi-list-check',
                                            'stock_movement' => 'bi-box-seam',
                                            'team_updated' => 'bi-people',
                                            'employee_removed' => 'bi-person-dash',
                                            'equipment_assigned' => 'bi-truck',
                                            'equipment_removed' => 'bi-box-arrow-right',
                                            default => 'bi-info-circle'
                                        } }} fs-6"></i>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="vr opacity-25 mt-2" style="height: 40px; margin: 0 auto; display: block;"></div>
                                    @endif
                                </div>
                                <div class="ms-3 flex-grow-1 border-bottom pb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div class="fw-bold text-dark">{{ $log->description }}</div>
                                        <span class="badge bg-light text-muted fw-normal" style="font-size: 0.7rem;">
                                            <i class="bi bi-calendar3 me-1"></i> {{ $log->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-person me-1"></i> {{ $log->user ? $log->user->name : 'Système' }}
                                        <span class="mx-2 text-opacity-25">|</span>
                                        <span class="text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ str_replace('_', ' ', $log->action) }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x display-4 d-block mb-3 opacity-25"></i>
                                Aucun événement enregistré pour le moment.
                            </div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    @can('projects.edit')
    {{-- Modal Gestion Équipe --}}
    <div class="modal fade" id="assignEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('projects.employees.sync', $project) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold">Gestion de l'équipe du chantier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-4">Sélectionnez les collaborateurs à affecter à ce chantier. Les besoins définis dans le plan d'effectif vous aident à équilibrer l'équipe.</p>
                        
                        <div class="row g-3">
                            @php
                                $assignedIds = $project->employees->pluck('id')->toArray();
                                $allEmployees = \App\Models\Employee::with('jobTypes.category')->orderBy('last_name')->get();
                            @endphp
                            
                            @foreach($allEmployees->groupBy(fn($e) => $e->jobTypes->first()->job_category_id ?? 0) as $catId => $emps)
                                <div class="col-12 mt-4">
                                    <h6 class="text-primary fw-bold small text-uppercase border-bottom pb-2">
                                        <i class="bi bi-folder2-open me-2"></i>
                                        {{ $emps->first()->jobTypes->first()->category->name ?? 'Sans catégorie' }}
                                    </h6>
                                </div>
                                @foreach($emps as $emp)
                                    <div class="col-md-6">
                                        <div class="card border border-light-subtle shadow-none h-100">
                                            <div class="card-body p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" id="modal_emp_{{ $emp->id }}" {{ in_array($emp->id, $assignedIds) ? 'checked' : '' }}>
                                                    <label class="form-check-label d-flex flex-column ms-2" for="modal_emp_{{ $emp->id }}">
                                                        <span class="fw-bold text-dark">{{ $emp->full_name }}</span>
                                                        <span class="text-muted small">
                                                            @foreach($emp->jobTypes as $jt)
                                                                {{ $jt->name }}{{ !$loop->last ? ', ' : '' }}
                                                            @endforeach
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Enregistrer l'équipe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
    @can('stock.create')
    {{-- Modal Seuils Alerte --}}
    <div class="modal fade" id="setThresholdModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('projects.thresholds.update', $project) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold">Configurer une alerte stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Matériau / Consommable</label>
                            <select name="material_id" class="form-select" required>
                                <option value="">Choisir un matériau...</option>
                                @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Seuil minimum d'alerte</label>
                            <div class="input-group">
                                <input type="number" name="min_threshold" class="form-control" step="0.001" min="0" placeholder="Ex: 10" required>
                                <span class="input-group-text bg-light">Quantité</span>
                            </div>
                            <div class="form-text small">Une alerte s'affichera dès que le stock sur site sera inférieur ou égal à ce seuil.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-bell me-1"></i> Activer l'alerte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
    @can('projects.edit')
    {{-- Modal Affectation Matériel --}}
    <div class="modal fade" id="assignEquipmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('projects.equipments.assign', $project) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold">Affecter un matériel au chantier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        @php
                            $allEquipments = \App\Models\Equipment::where('company_id', $project->company_id)
                                ->where('status', 'disponible')
                                ->orderBy('name')
                                ->get();
                        @endphp
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-uppercase">Matériel disponible</label>
                            <select name="equipment_id" class="form-select" required>
                                <option value="">Choisir un matériel...</option>
                                @foreach($allEquipments as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->name }} ({{ $eq->reference }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase">Date de début</label>
                                <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase">Date de fin prévue</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-bold text-uppercase">Notes / Usage</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Ex: Terrassement zone A..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check2 me-1"></i> Confirmer l'affectation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    @push('styles')
    <style>
        .project-nav-tabs .nav-item {
            flex: 1 0 160px; /* Force une largeur de base égale et permet de remplir l'espace */
            max-width: 100%;
        }
        @media (max-width: 768px) {
            .project-nav-tabs .nav-item {
                flex: 1 0 45%; /* Sur tablette/mobile, 2 par ligne */
            }
        }
        @media (max-width: 480px) {
            .project-nav-tabs .nav-item {
                flex: 1 0 100%; /* Sur petit mobile, 1 par ligne */
            }
        }
    </style>
    @endpush
</x-layouts.app>
