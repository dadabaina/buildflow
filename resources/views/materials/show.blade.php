<x-layouts.app :title="$material->name">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('materials.index') }}" class="text-decoration-none opacity-50 text-dark">Matériaux</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $material->name }}</li>
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
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">{{ $material->name }}</h4>
                                    <span class="badge {{ $material->is_active ? 'bg-success' : 'bg-secondary' }} badge-sm">
                                        {{ $material->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    @if($material->category)
                                        <span class="badge bg-label-primary text-uppercase small">{{ $material->category->name }}</span>
                                    @endif
                                    <span class="text-muted small"><i class="bx bx-hash me-1"></i>{{ $material->reference ?? 'Sans référence' }}</span>
                                    <span class="badge bg-light text-dark border-0 small px-2">Unité: {{ $material->unit }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('materials.edit')
                            <a href="{{ route('materials.edit', $material) }}" class="btn btn-primary shadow-sm px-4">
                                <i class="bx bx-edit-alt me-1"></i>Modifier
                            </a>
                            @endcan
                            @can('materials.delete')
                            <form method="POST" action="{{ route('materials.destroy', $material) }}" onsubmit="return confirm('Supprimer ce matériau ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-icon">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Prix moyen actuel</p>
                            <h4 class="mb-0 fw-bold mt-1">
                                @php $p = $material->currentPrice(); @endphp
                                {{ $p ? number_format($p, 0, ',', ' ') : '—' }} <small class="fs-6 fw-normal text-muted">Ar</small>
                            </h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-money fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Historique Prix</p>
                            <h4 class="mb-0 fw-bold mt-1 text-info">{{ $material->prices->count() }} <small class="fs-6 fw-normal text-muted">entrées</small></h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-history fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Utilisation Dosages</p>
                            <h4 class="mb-0 fw-bold mt-1 text-warning">{{ $material->dosageItems->count() }} <small class="fs-6 fw-normal text-muted">modèles</small></h4>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-calculator fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Price History -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-trending-up me-2 text-primary"></i>Évolution des prix unitaires</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addPriceForm">
                        <i class="bx bx-plus me-1"></i>Nouveau prix
                    </button>
                </div>

                <div class="collapse" id="addPriceForm">
                    <div class="card-body border-bottom bg-light bg-opacity-50">
                        <form method="POST" action="{{ route('materials.prices.store', $material) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Prix (MGA) *</label>
                                    <input type="number" name="unit_price" class="form-control" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Date effective *</label>
                                    <input type="date" name="effective_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Région</label>
                                    <select name="region_id" class="form-select">
                                        <option value="">Général</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Fournisseur</label>
                                    <input type="text" name="supplier_name" class="form-control" placeholder="Optionnel">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                        <i class="bx bx-check me-1"></i>Enregistrer le prix
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Date</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Région</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Source / Fournisseur</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Prix Unit. HT</th>
                                    <th class="border-0"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($material->prices as $price)
                                    <tr class="{{ $price->effective_date->isFuture() ? 'opacity-50' : '' }}">
                                        <td class="ps-4">
                                            <span class="fw-medium text-dark">{{ $price->effective_date->format('d/m/Y') }}</span>
                                            @if($price->effective_date->isFuture())
                                                <span class="badge bg-label-info badge-xs ms-1">Planifié</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($price->region)
                                                <span class="badge bg-label-secondary badge-sm">{{ $price->region->name }}</span>
                                            @else
                                                <span class="text-muted small italic">Général</span>
                                            @endif
                                        </td>
                                        <td><span class="text-muted small">{{ $price->supplier_name ?? '—' }}</span></td>
                                        <td class="pe-4 text-end fw-bold text-dark">
                                            {{ number_format($price->unit_price, 0, ',', ' ') }} <small class="text-muted">Ar</small>
                                        </td>
                                        <td class="text-end pe-3">
                                            @can('materials.edit')
                                            <form method="POST" action="{{ route('materials.prices.destroy', [$material, $price]) }}" onsubmit="return confirm('Supprimer ce prix ?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-icon btn-sm btn-label-danger"><i class="bx bx-trash"></i></button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted small">Aucun historique de prix.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Détails de l'article</h6>
                </div>
                <div class="card-body py-4">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-category fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Famille / Catégorie</small>
                                <span class="fw-medium text-dark">{{ $material->category?->name ?? 'Non classé' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-info rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-ruler fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Unité de mesure</small>
                                <span class="fw-medium text-dark">{{ $material->unit }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-start">
                            <div class="avatar avatar-sm bg-label-secondary rounded me-3 d-flex align-items-center justify-content-center mt-1">
                                <i class="bx bx-info-circle fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Description</small>
                                <p class="mb-0 small text-dark italic">{{ $material->description ?? 'Pas de description.' }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Usage in Dosage Models -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Utilisation Dosages (DBE)</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush border-0">
                        @forelse($material->dosageItems->groupBy('dosage_model_id') as $modelId => $items)
                            @php $dm = $items->first()->dosageModel; @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 border-bottom border-light">
                                <div>
                                    <a href="{{ route('dosage.show', $dm) }}" class="fw-bold text-dark text-decoration-none d-block small">
                                        {{ $dm->name }}
                                    </a>
                                    <small class="text-muted">{{ $items->first()->quantity_per_unit }} {{ $material->unit }} / {{ $dm->output_unit }}</small>
                                </div>
                                <a href="{{ route('dosage.show', $dm) }}" class="btn btn-icon btn-sm btn-label-primary">
                                    <i class="bx bx-chevron-right"></i>
                                </a>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted small border-0">
                                Ce matériau n'est utilisé dans aucun modèle de dosage.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Pricing Alert -->
            @if($material->prices->count() > 0 && $material->prices->first()->effective_date->diffInMonths(now()) > 6)
                <div class="card border-0 shadow-sm bg-label-warning">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bx bx-error text-warning fs-4"></i>
                            <h6 class="fw-bold mb-0 text-warning">Prix obsolète ?</h6>
                        </div>
                        <p class="small text-muted mb-0">La dernière cotation date de plus de 6 mois. Pensez à mettre à jour les tarifs pour vos devis.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
