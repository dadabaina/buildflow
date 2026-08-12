<x-layouts.app title="Gestion des Chantiers">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Chantiers</li>
    </x-slot>

    <!-- Header & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-building-house fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Gestion des Chantiers</h4>
                                <p class="text-muted small mb-0">Pilotez vos projets, suivez les coûts et maîtrisez vos délais en temps réel.</p>
                            </div>
                        </div>
                        <div>
                            @can('projects.create')
                            <a href="{{ route('projects.create') }}" id="tour-projects-new" class="btn btn-primary shadow-sm px-4">
                                <i class="bx bx-plus me-1"></i>Nouveau chantier
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Projets</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['total_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-folder fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">En cours</p>
                            <h4 class="mb-0 fw-bold mt-1 text-success">{{ $stats['active_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-play-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Engagé total</p>
                            <h4 class="mb-0 fw-bold mt-1 text-primary">
                                {{ number_format($stats['total_contract'], 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small>
                            </h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-wallet fs-3"></i>
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
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Rechercher</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nom ou référence..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Client</label>
                    <select name="client_id" class="form-select border-0 bg-light">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Statut</label>
                    <select name="status" class="form-select border-0 bg-light">
                        <option value="">Tous les statuts</option>
                        @foreach($statuses as $status)
                            @php $tmp = new \App\Models\Project(['status' => $status]); @endphp
                            <option value="{{ $status }}" @selected(request('status') == $status)>{{ $tmp->status_libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bx bx-refresh"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tour-projects-table" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Chantier & Réf</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Client</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Statut</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Progression</th>
                            @unless(auth()->user()->hasRole('chef_chantier'))
                            <th class="py-3 border-0 small text-uppercase text-muted text-end">Montant Marché</th>
                            @endunless
                            <th class="py-3 border-0 small text-uppercase text-muted text-end">Facturé</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-center">Début</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
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
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-medium">{{ $project->client?->name ?? '—' }}</span>
                                    <small class="text-muted" style="font-size: 0.65rem;">{{ $project->region?->name ?? 'Sans région' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $project->status_badge_class }} badge-sm text-uppercase">
                                    {{ $project->status_libelle }}
                                </span>
                            </td>
                            <td style="min-width: 120px;">
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $project->progress_percent }}%"></div>
                                    </div>
                                    <small class="text-muted fw-bold">{{ $project->progress_percent }}%</small>
                                </div>
                            </td>
                            @unless(auth()->user()->hasRole('chef_chantier'))
                            <td class="text-end fw-bold text-dark">
                                {{ number_format($project->total_market_amount, 0, ',', ' ') }} <small class="text-muted fw-normal">Ar</small>
                            </td>
                            @endunless
                            <td class="text-end fw-bold text-primary">
                                {{ number_format($project->total_invoiced, 0, ',', ' ') }} <small class="text-muted fw-normal">Ar</small>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small">{{ $project->start_date?->format('d/m/Y') ?? '—' }}</span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Détails">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @can('projects.edit')
                                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-icon btn-sm btn-label-info shadow-none" title="Modifier">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    @endcan
                                    @can('projects.delete')
                                    <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Supprimer ce chantier ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-icon btn-sm btn-label-danger shadow-none" title="Supprimer"><i class="bx bx-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-building fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun chantier ne correspond à vos critères.</h6>
                                <p class="small text-muted">Ajustez vos filtres ou <a href="{{ route('projects.create') }}">commencez un nouveau projet</a>.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($projects->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $projects->links() }}
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
        .badge-xs { padding: 0.2rem 0.4rem; font-size: 0.6rem; }
    </style>
    @endpush
</x-layouts.app>
