<x-layouts.app :title="'BC ' . $purchaseOrder->reference">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}" class="text-decoration-none opacity-50 text-dark">Bons de commande</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $purchaseOrder->reference }}</li>
    </x-slot>

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-file-blank fs-1"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1" id="tour-po-status">
                                    <h4 class="mb-0 fw-bold">{{ $purchaseOrder->reference }}</h4>
                                    <span class="badge {{ $purchaseOrder->status_badge_class }} badge-sm text-uppercase">
                                        {{ $purchaseOrder->status_libelle }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span class="text-muted small"><i class="bx bx-calendar me-1"></i>{{ $purchaseOrder->order_date->format('d/m/Y') }}</span>
                                    <span class="text-muted small"><i class="bx bx-building me-1"></i>{{ $purchaseOrder->project->name ?? 'Sans projet' }}</span>
                                    <span class="text-muted small"><i class="bx bx-store me-1"></i>{{ $purchaseOrder->supplier->name ?? 'Sans fournisseur' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @if(in_array($purchaseOrder->status, ['brouillon', 'valide']))
                                @can('purchase_orders.edit')
                                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-primary">
                                    <i class="bx bx-edit-alt me-1"></i>Modifier
                                </a>
                                @endcan
                            @endif
                            <button class="btn btn-outline-secondary btn-icon" onclick="window.print()">
                                <i class="bx bx-printer"></i>
                            </button>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Hors Taxe</p>
                            <h4 class="mb-0 fw-bold mt-1 text-nowrap text-amount">{{ number_format($purchaseOrder->total_ht, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-secondary rounded p-2">
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">TVA ({{ $purchaseOrder->tva_rate }}%)</p>
                            <h4 class="mb-0 fw-bold mt-1 text-nowrap text-amount">{{ number_format($purchaseOrder->total_ttc - $purchaseOrder->total_ht, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-calculator fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Toutes Taxes Comprises</p>
                            <h4 class="mb-0 fw-bold mt-1 text-primary text-nowrap text-amount">{{ number_format($purchaseOrder->total_ttc, 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-receipt fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content: Order Items -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-list-ul me-2"></i>Détails des articles</h6>
                    <span class="badge bg-label-primary rounded-pill">{{ $purchaseOrder->items->count() }} articles</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">Description</th>
                                    <th class="py-3 border-0 text-end">Qté</th>
                                    <th class="py-3 border-0">Unité</th>
                                    <th class="py-3 border-0 text-end">P.U HT</th>
                                    <th class="pe-4 py-3 border-0 text-end">Total HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-medium text-dark">{{ $item->description }}</span>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                                        <td><span class="badge bg-label-secondary badge-sm">{{ $item->unit ?? '-' }}</span></td>
                                        <td class="text-end text-muted text-amount">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                        <td class="pe-4 text-end fw-bold text-dark text-amount">{{ number_format($item->total, 0, ',', ' ') }} <small class="text-muted">Ar</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent py-4 border-top">
                    <div class="row justify-content-end">
                        <div class="col-md-8 col-lg-7 col-xl-6 pe-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total HT :</span>
                                <span class="fw-bold text-dark text-amount">{{ number_format($purchaseOrder->total_ht, 0, ',', ' ') }} Ar</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">TVA ({{ $purchaseOrder->tva_rate }}%) :</span>
                                <span class="fw-bold text-dark text-amount">{{ number_format($purchaseOrder->total_ttc - $purchaseOrder->total_ht, 0, ',', ' ') }} Ar</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold text-dark">Total TTC :</span>
                                <span class="fw-bold text-primary fs-5 text-amount">{{ number_format($purchaseOrder->total_ttc, 0, ',', ' ') }} Ar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Conditions -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom bg-transparent py-3">
                            <h6 class="mb-0 fw-bold"><i class="bx bx-info-circle me-2"></i>Conditions de livraison</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">
                                {{ $purchaseOrder->delivery_conditions ?? 'Aucune condition spécifique renseignée.' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom bg-transparent py-3">
                            <h6 class="mb-0 fw-bold"><i class="bx bx-note me-2"></i>Notes internes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">
                                {{ $purchaseOrder->notes ?? 'Aucune note interne.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Info & Actions -->
        <div class="col-lg-4">
            <!-- Order Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Détails de l'ordre</h6>
                </div>
                <div class="card-body py-4">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-calendar fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Date de commande</small>
                                <span class="fw-medium text-dark">{{ $purchaseOrder->order_date->format('d/m/Y') }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-info rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-time fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Livraison prévue</small>
                                <span class="fw-medium text-dark">{{ $purchaseOrder->delivery_date ? $purchaseOrder->delivery_date->format('d/m/Y') : 'Non définie' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-3 border-top pt-3">
                            <div class="avatar avatar-sm bg-label-secondary rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-user fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Créé par</small>
                                <span class="fw-medium text-dark">{{ $purchaseOrder->createdBy->name ?? '-' }}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Status Actions -->
            @php $transitions = \App\Models\PurchaseOrder::$statusTransitions[$purchaseOrder->status] ?? []; @endphp
            @if(count($transitions))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom bg-transparent py-3">
                        <h6 class="mb-0 fw-bold">Gestion du statut</h6>
                    </div>
                    <div class="card-body py-4">
                        @foreach($transitions as $s)
                            <form method="POST" action="{{ route('purchase-orders.status', $purchaseOrder) }}" class="mb-2 last-child-mb-0">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $s }}">
                                <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center">
                                    <span>Marquer comme <strong>{{ match($s) {
                                        'envoye' => 'Envoyé',
                                        'partiellement_livre' => 'Partiellement livré',
                                        'livre' => 'Livré',
                                        'annule' => 'Annulé',
                                        default => $s
                                    } }}</strong></span>
                                    <i class="bx bx-chevron-right"></i>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Conversion to Expense -->
            @if(in_array($purchaseOrder->status, ['livre', 'partiellement_livre']))
                <div class="card border-0 shadow-sm mb-4 bg-label-success" id="tour-po-convert">
                    <div class="card-body p-4 text-center">
                        <i class="bx bx-repost fs-1 mb-2"></i>
                        <h6 class="fw-bold mb-2">Facturation Fournisseur</h6>
                        <p class="small mb-3">Convertissez les lignes de ce bon de commande en dépenses réelles pour votre comptabilité de chantier.</p>
                        <form method="POST" action="{{ route('purchase-orders.convert-expense', $purchaseOrder) }}"
                              onsubmit="return confirm('Convertir ce BC en dépenses ?')">
                            @csrf
                            <button class="btn btn-success w-100 shadow-sm">
                                <i class="bx bx-plus-circle me-1"></i>Convertir en dépenses
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Danger Zone -->
            @can('purchase_orders.delete')
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 border-danger border-opacity-10">
                <div class="card-body p-4 text-center">
                    <p class="text-danger small mb-3 fw-medium">Attention: La suppression est définitive.</p>
                    <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}"
                          onsubmit="return confirm('Supprimer définitivement ce BC ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 shadow-sm">
                            <i class="bx bx-trash me-2"></i>Supprimer le BC
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>

    @push('styles')
    <style>
        .last-child-mb-0:last-child {
            margin-bottom: 0 !important;
        }
    </style>
    @endpush
</x-layouts.app>
