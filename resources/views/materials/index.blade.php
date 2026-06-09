<x-layouts.app title="Catalogue Matériaux">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Matériaux</li>
    </x-slot>

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-package fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Bibliothèque des Matériaux</h4>
                                <p class="text-muted small mb-0">Gérez vos ressources, tarifs et unités pour vos calculs de prix de revient.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('materials.create')
                            <a href="{{ route('materials.export') }}" class="btn btn-outline-secondary shadow-sm">
                                <i class="bx bx-download me-1"></i>Exporter
                            </a>
                            <a href="{{ route('materials.create') }}" class="btn btn-primary shadow-sm px-4">
                                <i class="bx bx-plus me-1"></i>Nouveau matériau
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Matériaux</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['total_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-list-ul fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Articles Actifs</p>
                            <h4 class="mb-0 fw-bold mt-1 text-success">{{ $stats['active_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-check-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">En rupture / Bas</p>
                            <h4 class="mb-0 fw-bold mt-1 text-danger">
                                {{ $materials->filter(fn($m) => $m->isLowStock())->count() }}
                            </h4>
                        </div>
                        <div class="avatar bg-label-danger rounded p-2">
                            <i class="bx bx-error fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Catégories</p>
                            <h4 class="mb-0 fw-bold mt-1 text-info">{{ $stats['category_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-category fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom bg-transparent py-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Rechercher</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nom ou référence..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Catégorie</label>
                    <select name="category_id" class="form-select border-0 bg-light">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-uppercase">Unité</label>
                    <select name="unit" class="form-select border-0 bg-light">
                        <option value="">Toutes</option>
                        @foreach($unitTypes as $ut)
                            <option value="{{ $ut->symbol }}" @selected(request('unit') == $ut->symbol)>{{ $ut->symbol }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
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
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Désignation</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Catégorie</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-center">Unité</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-center">Stock Actuel</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-end">Prix moyen HT</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-center">Statut</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $mat)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex flex-column">
                                    <a href="{{ route('materials.show', $mat) }}" class="fw-bold text-dark text-decoration-none">{{ $mat->name }}</a>
                                    @if($mat->reference)
                                        <small class="text-muted font-monospace" style="font-size: 0.7rem;">REF: {{ $mat->reference }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($mat->category)
                                    <span class="badge bg-label-secondary badge-sm">{{ $mat->category->name }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border-0 small px-2">{{ $mat->unit }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center cursor-pointer" 
                                     onclick="loadStockBreakdown({{ $mat->id }})"
                                     title="Cliquer pour voir la répartition par dépôt">
                                    <span class="fw-bold {{ $mat->isLowStock() ? 'text-danger' : 'text-primary' }} text-decoration-underline">
                                        {{ (float) $mat->stock_quantity }}
                                    </span>
                                    @if($mat->isLowStock())
                                        <span class="badge bg-label-danger badge-xs" style="font-size: 0.6rem;">STOCK BAS</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                @php $price = $mat->currentPrice(); @endphp
                                @if($price !== null)
                                    {{ number_format($price, 0, ',', ' ') }} <small class="text-muted fw-normal">Ar</small>
                                @else
                                    <span class="text-warning small italic">Non coté</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $mat->is_active ? 'bg-label-success' : 'bg-label-secondary' }} badge-xs rounded-pill">
                                    {{ $mat->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('materials.show', $mat) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Voir">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @can('materials.edit')
                                    <a href="{{ route('materials.edit', $mat) }}" class="btn btn-icon btn-sm btn-label-info shadow-none" title="Modifier">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    @endcan
                                    @can('materials.delete')
                                    <form method="POST" action="{{ route('materials.destroy', $mat) }}" onsubmit="return confirm('Supprimer ce matériau ?')">
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
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-package fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun matériau trouvé.</h6>
                                <p class="small text-muted">Ajustez vos filtres ou <a href="{{ route('materials.create') }}">ajoutez une nouvelle ressource</a>.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($materials->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $materials->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- Modal Stock Breakdown -->
    <div class="modal fade" id="stockBreakdownModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content shadow-lg border-0" id="stockBreakdownContent">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2 text-muted small">Récupération des données...</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function loadStockBreakdown(materialId) {
            const modal = new bootstrap.Modal(document.getElementById('stockBreakdownModal'));
            const content = document.getElementById('stockBreakdownContent');
            
            // Reset content to loader
            content.innerHTML = `
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2 text-muted small">Récupération des données...</p>
                </div>
            `;
            
            modal.show();

            fetch(`/materials/${materialId}/stock-breakdown`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="modal-body text-center py-4">
                            <i class="bx bx-error-circle text-danger display-4"></i>
                            <p class="mt-2">Erreur lors du chargement des données.</p>
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    `;
                });
        }
    </script>
    @endpush
</x-layouts.app>
