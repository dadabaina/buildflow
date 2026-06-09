<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Mouvements de stock</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $movement->reference }}</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Mouvement — {{ $movement->reference }}</h3>
        <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <x-card title="Détails du mouvement">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Référence</dt>
                    <dd class="col-7 fw-semibold">{{ $movement->reference }}</dd>

                    <dt class="col-5 text-muted">Type</dt>
                    <dd class="col-7">
                        <span class="badge bg-{{ $movement->typeColor() }}">{{ $movement->typeLabel() }}</span>
                    </dd>

                    <dt class="col-5 text-muted">Date</dt>
                    <dd class="col-7">{{ $movement->movement_date?->format('d/m/Y') }}</dd>

                    <dt class="col-5 text-muted">Article</dt>
                    <dd class="col-7">{{ $movement->item_name }}</dd>

                    <dt class="col-5 text-muted">Quantité</dt>
                    <dd class="col-7 fw-bold">{{ $movement->quantity }} {{ $movement->unit }}</dd>

                    <dt class="col-5 text-muted">Coût unitaire</dt>
                    <dd class="col-7">{{ number_format($movement->unit_cost ?? 0, 0, ',', ' ') }} Ar</dd>

                    <dt class="col-5 text-muted">Total</dt>
                    <dd class="col-7 fw-bold text-primary">{{ number_format($movement->total, 0, ',', ' ') }} Ar</dd>

                    <dt class="col-5 text-muted">Dépôt</dt>
                    <dd class="col-7">{{ $movement->warehouse->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Chantier</dt>
                    <dd class="col-7">{{ $movement->project->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Matériau</dt>
                    <dd class="col-7">{{ $movement->material->name ?? '—' }}</dd>

                    @if($movement->notes)
                    <dt class="col-5 text-muted">Notes</dt>
                    <dd class="col-7">{{ $movement->notes }}</dd>
                    @endif

                    <dt class="col-5 text-muted">Créé par</dt>
                    <dd class="col-7">{{ $movement->creator->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Créé le</dt>
                    <dd class="col-7 text-muted small">{{ $movement->created_at->format('d/m/Y H:i') }}</dd>
                </dl>
            </x-card>
        </div>
    </div>
</x-layouts.app>
