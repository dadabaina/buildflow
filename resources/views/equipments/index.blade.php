<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Tableau de bord</li>
        <li class="breadcrumb-item active fw-bold text-dark">Matériels & Équipements</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Matériels & Équipements</h3>
            <p class="text-secondary small mt-1">Parc matériel de l'entreprise.</p>
        </div>
        @can('equipments.create')
        <a href="{{ route('equipments.create') }}" id="tour-equipments-new" class="btn btn-primary shadow-app">
            <i class="bi bi-plus-lg me-1"></i>Ajouter un matériel
        </a>
        @endcan
    </div>

    {{-- KPI cards --}}
    @php
        $byStatus = $equipments->groupBy('status');
    @endphp
    <div class="row g-3 mb-4">
        @foreach([['disponible','Disponibles','success','check-circle'],['affecte','Affectés','primary','hammer'],['maintenance','Maintenance','warning','tools'],['hors_service','Hors service','secondary','x-circle']] as [$s,$label,$c,$icon])
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm-app rounded-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">{{ $label }}</p>
                        <h4 class="fw-bold mb-0 text-{{ $c }}">{{ $byStatus->get($s)?->count() ?? 0 }}</h4>
                    </div>
                    <div class="bg-{{ $c }}-subtle rounded-circle p-3"><i class="bi bi-{{ $icon }} text-{{ $c }} fs-5"></i></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filtres --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Tous statuts</option>
                @foreach(['disponible'=>'Disponible','affecte'=>'Affecté','maintenance'=>'Maintenance','hors_service'=>'Hors service'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">Toutes catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <x-card>
        <div class="table-responsive">
            <table id="tour-equipments-table" class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Référence</th>
                        <th>Marque / Modèle</th>
                        <th class="text-end">Coût/jour</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipments as $eq)
                    <tr>
                        <td class="fw-semibold">{{ $eq->name }}</td>
                        <td class="text-muted">{{ $eq->category ?? '—' }}</td>
                        <td class="text-muted font-monospace small">{{ $eq->reference ?? '—' }}</td>
                        <td class="text-muted">{{ implode(' ', array_filter([$eq->brand, $eq->model])) ?: '—' }}</td>
                        <td class="text-end">{{ $eq->daily_rental_cost > 0 ? number_format($eq->daily_rental_cost, 0, ',', ' ').' Ar' : '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $eq->statusColor() }}">{{ $eq->statusLabel() }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('equipments.show', $eq) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
                            @can('equipments.edit')
                            <a href="{{ route('equipments.edit', $eq) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-tools display-6 d-block mb-2 opacity-25"></i>
                            Aucun matériel enregistré.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($equipments->hasPages())
        <div class="mt-3">{{ $equipments->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
