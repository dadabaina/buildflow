<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Stocks</li>
        <li class="breadcrumb-item active fw-bold text-dark">Mouvements</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Mouvements de stock</h3>
            <p class="text-secondary small mt-1">Toutes les entrées, sorties et transferts.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock.export') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download me-1"></i>Exporter CSV
            </a>
            <a href="{{ route('stock.dashboard') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-boxes me-1"></i>Dashboard stock
            </a>
            @can('stock.create')
            <a href="{{ route('stock-movements.create') }}" id="tour-stock-new" class="btn btn-primary shadow-app">
                <i class="bi bi-plus-lg me-1"></i>Nouveau mouvement
            </a>
            @endcan
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les dépôts</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">Tous types</option>
                <option value="entree" {{ request('type') === 'entree' ? 'selected' : '' }}>Entrée</option>
                <option value="sortie" {{ request('type') === 'sortie' ? 'selected' : '' }}>Sortie</option>
                <option value="transfert" {{ request('type') === 'transfert' ? 'selected' : '' }}>Transfert</option>
                <option value="ajustement" {{ request('type') === 'ajustement' ? 'selected' : '' }}>Ajustement</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Rechercher un article..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <x-card>
        <div class="table-responsive">
            <table id="tour-stock-table" class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Article</th>
                        <th>Unité</th>
                        <th class="text-end">Qté</th>
                        <th class="text-end">PU (Ar)</th>
                        <th class="text-end">Total (Ar)</th>
                        <th>Dépôt</th>
                        <th>Chantier</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                    <tr>
                        <td>{{ $m->movement_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $m->typeColor() }}">{{ $m->typeLabel() }}</span>
                        </td>
                        <td class="fw-semibold">{{ $m->item_name }}</td>
                        <td class="text-muted">{{ $m->unit }}</td>
                        <td class="text-end">{{ number_format($m->quantity, 3, ',', ' ') }}</td>
                        <td class="text-end">{{ $m->unit_cost > 0 ? number_format($m->unit_cost, 0, ',', ' ') : '—' }}</td>
                        <td class="text-end">{{ $m->total > 0 ? number_format($m->total, 0, ',', ' ') : '—' }}</td>
                        <td class="text-muted small">{{ $m->warehouse->name ?? '—' }}</td>
                        <td class="text-muted small">{{ $m->project->name ?? '—' }}</td>
                        <td class="text-end">
                            @can('stock.delete')
                            <form method="POST" action="{{ route('stock-movements.destroy', $m) }}"
                                  onsubmit="return confirm('Supprimer ce mouvement ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="bi bi-arrow-left-right display-6 d-block mb-2 opacity-25"></i>
                            Aucun mouvement enregistré.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="mt-3">{{ $movements->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
