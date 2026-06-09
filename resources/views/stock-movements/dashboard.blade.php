<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Dashboard stock</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Dashboard Stock</h3>
            <p class="text-secondary small mt-1">Vue d'ensemble par dépôt et niveaux de stock.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-list me-1"></i>Tous les mouvements
            </a>
            @can('stock.create')
            <a href="{{ route('stock-movements.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nouveau mouvement
            </a>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        {{-- Liste des stocks groupés par dépôt --}}
        <div class="col-lg-8">
            @forelse($stockByItem as $warehouseId => $items)
                @php $warehouse = $items->first()->warehouse; @endphp
                <x-card class="mb-4 shadow-sm border-0 rounded-4">
                    <x-slot name="title">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-building small"></i>
                            </div>
                            <span class="fs-5 fw-bold text-dark">{{ $warehouse->name }}</span>
                            @if($warehouse->project)
                                <span class="badge bg-info-subtle text-info border border-info-subtle ms-2">Chantier : {{ $warehouse->project->name }}</span>
                            @else
                                <span class="badge bg-light text-muted border ms-2">Dépôt Central</span>
                            @endif
                        </div>
                    </x-slot>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 small text-uppercase fw-bold text-muted">Article</th>
                                    <th class="border-0 text-center small text-uppercase fw-bold text-muted">Unité</th>
                                    <th class="border-0 text-end small text-uppercase fw-bold text-muted">Quantité disponible</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border px-2 py-1">{{ $item->unit }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fs-5 fw-bold {{ $item->balance < 0 ? 'text-danger' : 'text-primary' }}">
                                            {{ number_format($item->balance, 2, ',', ' ') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @empty
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-boxes display-1 text-light"></i>
                    <p class="mt-3 text-muted">Aucun stock disponible dans les dépôts.</p>
                </div>
            @endforelse
        </div>

        {{-- Barre latérale : Mouvements récents --}}
        <div class="col-lg-4">
            <x-card title="Mouvements récents" icon="bi bi-clock-history">
                <div class="list-group list-group-flush">
                    @forelse($recent as $m)
                    <div class="list-group-item px-0 py-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark small">{{ $m->item_name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $m->movement_date->format('d/m/Y') }}
                                    <span class="mx-1">·</span>
                                    <i class="bi bi-building me-1"></i>{{ $m->warehouse->name }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $m->typeColor() }} small">
                                    {{ $m->typeLabel() }} {{ number_format($m->quantity, 1, ',', ' ') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted py-3">Aucun mouvement récent.</p>
                    @endforelse
                </div>
                <div class="mt-3 text-center">
                    <a href="{{ route('stock-movements.index') }}" class="small text-decoration-none">Voir tout l'historique <i class="bi bi-arrow-right"></i></a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
