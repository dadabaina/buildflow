<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Matériels</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $equipment->name }}</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ $equipment->name }}</h3>
            <div class="text-secondary small">
                {{ $equipment->category ?? '' }} {{ $equipment->brand ? '· '.$equipment->brand : '' }} {{ $equipment->model ? $equipment->model : '' }}
                <span class="ms-2 badge bg-{{ $equipment->statusColor() }}">{{ $equipment->statusLabel() }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            @can('equipments.edit')
            <a href="{{ route('equipments.edit', $equipment) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            @endcan
            @can('equipments.delete')
            <form method="POST" action="{{ route('equipments.destroy', $equipment) }}"
                  onsubmit="return confirm('Supprimer ce matériel ?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
            </form>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        {{-- Fiche technique --}}
        <div class="col-lg-4">
            <x-card title="Fiche technique">
                <dl class="row small mb-0">
                    <dt class="col-6 text-muted">Référence</dt>
                    <dd class="col-6 font-monospace">{{ $equipment->reference ?? '—' }}</dd>
                    <dt class="col-6 text-muted">N° de série</dt>
                    <dd class="col-6">{{ $equipment->serial_number ?? '—' }}</dd>
                    <dt class="col-6 text-muted">Acquisition</dt>
                    <dd class="col-6">{{ $equipment->acquisition_date ? $equipment->acquisition_date->format('d/m/Y') : '—' }}</dd>
                    <dt class="col-6 text-muted">Coût acquisition</dt>
                    <dd class="col-6">{{ $equipment->acquisition_cost > 0 ? number_format($equipment->acquisition_cost, 0, ',', ' ').' Ar' : '—' }}</dd>
                    <dt class="col-6 text-muted">Coût/jour</dt>
                    <dd class="col-6 fw-semibold">{{ $equipment->daily_rental_cost > 0 ? number_format($equipment->daily_rental_cost, 0, ',', ' ').' Ar' : '—' }}</dd>
                </dl>
                @if($equipment->notes)
                <hr class="my-2">
                <p class="small text-muted mb-0">{{ $equipment->notes }}</p>
                @endif
            </x-card>
        </div>

        {{-- Maintenances --}}
        <div class="col-lg-8">
            <x-card title="Historique des maintenances">
                @can('equipments.edit')
                <form method="POST" action="{{ route('equipments.maintenances.store', $equipment) }}" class="row g-2 mb-4 p-3 bg-light rounded-3">
                    @csrf
                    <div class="col-md-2">
                        <select name="type" class="form-select form-select-sm">
                            <option value="preventive">Préventive</option>
                            <option value="corrective">Corrective</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="maintenance_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="Description des travaux">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="cost" class="form-control form-control-sm" step="0.01" min="0" placeholder="Coût (Ar)">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Ajouter</button>
                    </div>
                </form>
                @endcan

                @forelse($equipment->maintenances as $m)
                <div class="d-flex justify-content-between align-items-start p-3 border-bottom">
                    <div>
                        <div class="d-flex gap-2 align-items-center mb-1">
                            <span class="badge bg-{{ $m->type === 'preventive' ? 'info' : 'warning text-dark' }}">{{ $m->type === 'preventive' ? 'Préventive' : 'Corrective' }}</span>
                            <span class="fw-semibold">{{ $m->maintenance_date->format('d/m/Y') }}</span>
                            @if($m->cost > 0)
                            <span class="text-muted small">· {{ number_format($m->cost, 0, ',', ' ') }} Ar</span>
                            @endif
                        </div>
                        @if($m->description)
                        <p class="mb-0 small text-muted">{{ $m->description }}</p>
                        @endif
                        @if($m->next_maintenance_date)
                        <p class="mb-0 small text-info"><i class="bi bi-calendar3 me-1"></i>Prochaine: {{ $m->next_maintenance_date->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    @can('equipments.edit')
                    <form method="POST" action="{{ route('equipments.maintenances.destroy', [$equipment, $m]) }}"
                          onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endcan
                </div>
                @empty
                <p class="text-center text-muted py-4">Aucune maintenance enregistrée.</p>
                @endforelse
            </x-card>
        </div>
    </div>
</x-layouts.app>
