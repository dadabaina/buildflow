<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Dépôts</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Dépôts de stockage</h3>
        @can('warehouses.create')
        <a href="{{ route('warehouses.create') }}" id="tour-warehouses-new" class="btn btn-primary shadow-app">
            <i class="bi bi-plus-lg me-1"></i>Nouveau dépôt
        </a>
        @endcan
    </div>

    <div class="row g-3">
        @forelse($warehouses as $wh)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm-app rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="bg-primary-subtle rounded-3 p-3"><i class="bi bi-building text-primary fs-4"></i></div>
                        @if(!$wh->is_active)
                        <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-1">{{ $wh->name }}</h5>
                    @if($wh->project)
                    <p class="mb-2"><span class="badge bg-info-subtle text-info border border-info-subtle small fw-medium">Chantier : {{ $wh->project->name }}</span></p>
                    @endif
                    @if($wh->location)
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $wh->location }}</p>
                    @endif
                    <p class="text-muted small mb-0">{{ $wh->stock_movements_count }} mouvement(s)</p>
                </div>
                <div class="card-footer bg-transparent border-top-0 px-4 pb-3 d-flex gap-2">
                    <a href="{{ route('stock.dashboard') }}?warehouse_id={{ $wh->id }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-boxes me-1"></i>Voir stock
                    </a>
                    @can('warehouses.edit')
                    <a href="{{ route('warehouses.edit', $wh) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    @endcan
                    @can('warehouses.delete')
                    <form method="POST" action="{{ route('warehouses.destroy', $wh) }}" class="ms-auto"
                          onsubmit="return confirm('Supprimer ce dépôt ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-building display-5 d-block mb-2 opacity-25"></i>
            Aucun dépôt configuré.
        </div>
        @endforelse
    </div>
</x-layouts.app>
