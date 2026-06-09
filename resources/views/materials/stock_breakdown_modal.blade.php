<div class="modal-header border-0 pb-0">
    <h5 class="modal-title fw-bold">Détails du stock : {{ $material->name }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3">
        <div class="avatar bg-primary text-white p-2 rounded">
            <i class="bx bx-package fs-3"></i>
        </div>
        <div>
            <div class="text-muted small text-uppercase fw-bold">Stock Total</div>
            <div class="fs-4 fw-bold text-dark">{{ (float) $material->stock_quantity }} {{ $material->unit }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="border-0 small text-uppercase fw-bold">Dépôt / Chantier</th>
                    <th class="border-0 small text-uppercase fw-bold text-end">Quantité</th>
                </tr>
            </thead>
            <tbody>
                @forelse($breakdown as $item)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $item->warehouse->name }}</div>
                        @if($item->warehouse->project)
                            <small class="text-info"><i class="bi bi-building me-1"></i>Chantier : {{ $item->warehouse->project->name }}</small>
                        @else
                            <small class="text-muted">Dépôt Central</small>
                        @endif
                    </td>
                    <td class="text-end fw-bold fs-6 {{ $item->balance < 0 ? 'text-danger' : 'text-primary' }}">
                        {{ number_format($item->balance, 2, ',', ' ') }} <small class="text-muted fw-normal">{{ $material->unit }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center py-4 text-muted small">Aucune répartition disponible.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer border-0 pt-0">
    <a href="{{ route('stock-movements.index', ['search' => $material->name]) }}" class="btn btn-sm btn-light border w-100">
        <i class="bx bx-list-ul me-1"></i>Voir tous les mouvements
    </a>
</div>
