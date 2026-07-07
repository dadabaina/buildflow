<x-layouts.app title="Modèles de dosage">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Dosages</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-calculator me-2 text-primary"></i>Modèles de dosage (DBE)
            <span class="badge bg-secondary ms-2">{{ $models->count() }}</span>
        </h5>
        @can('dosage.create')
        <a href="{{ route('dosage.create') }}" id="tour-dosage-new" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau modèle
        </a>
        @endcan
    </div>

    <div class="row g-3">
        @forelse($models as $model)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-semibold mb-0">
                            <a href="{{ route('dosage.show', $model) }}" class="text-decoration-none">
                                {{ $model->name }}
                            </a>
                        </h6>
                        <span class="badge bg-info text-dark small">{{ $model->output_unit }}</span>
                    </div>
                    @if($model->description)
                    <p class="text-muted small mb-2">{{ Str::limit($model->description, 80) }}</p>
                    @endif
                    <div class="d-flex gap-3 text-muted small mt-2">
                        <span><i class="bi bi-list-ul me-1"></i>{{ $model->items_count }} ligne(s)</span>
                        <span><i class="bi bi-boxes me-1"></i>{{ $model->output_quantity }} {{ $model->output_unit }} / application</span>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('dosage.show', $model) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye"></i>
                    </a>
                    @can('dosage.edit')
                    <a href="{{ route('dosage.edit', $model) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endcan
                    @can('dosage.delete')
                    <form method="POST" action="{{ route('dosage.destroy', $model) }}"
                          onsubmit="return confirm('Supprimer ce modèle ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="bi bi-calculator fs-1 d-block mb-2"></i>
                Aucun modèle de dosage.<br>
                <a href="{{ route('dosage.create') }}" class="btn btn-primary mt-3">Créer le premier modèle</a>
            </div>
        </div>
        @endforelse
    </div>
</x-layouts.app>
