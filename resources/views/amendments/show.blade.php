<x-layouts.app :title="'Avenant ' . $amendment->reference">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('amendments.index') }}" class="text-decoration-none opacity-50 text-dark">Avenants</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $amendment->reference }}</li>
    </x-slot>

    @foreach(['success','error'] as $t)
        @if(session($t))
        <div class="alert alert-{{ $t === 'success' ? 'success' : 'danger' }} alert-dismissible fade show">
            {{ session($t) }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    @endforeach

    <div class="row g-4">
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-file-earmark-plus text-primary me-2"></i>{{ $amendment->reference }}
                        </h5>
                        <div class="mt-1">
                            <span class="badge {{ $amendment->status_badge_class }}">{{ $amendment->status_libelle }}</span>
                            <span class="text-muted small ms-2">Créé le {{ $amendment->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if($amendment->status === 'brouillon')
                            <a href="{{ route('amendments.edit', $amendment) }}" class="btn btn-outline-primary btn-sm px-3">
                                <i class="bi bi-pencil me-1"></i>Modifier
                            </a>
                        @endif
                        <a href="{{ route('amendments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Chantier</small>
                                <span class="fw-bold text-dark">{{ $amendment->project->name ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Titre de l'avenant</small>
                                <span class="fw-bold text-dark">{{ $amendment->title }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Devis Source</small>
                                @if($amendment->quote)
                                    <a href="{{ route('quotes.show', $amendment->quote) }}" class="fw-bold text-primary text-decoration-none">
                                        <i class="bi bi-file-earmark-text me-1"></i>{{ $amendment->quote->reference }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        @if($amendment->description)
                        <div class="col-12">
                            <div class="p-3 border rounded-3">
                                <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Description des travaux</small>
                                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $amendment->description }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <h6 class="fw-bold text-dark mb-3">Détail des modifications</h6>
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Description des prestations</th>
                                    <th class="text-end">Quantité</th>
                                    <th class="text-center">Unité</th>
                                    <th class="text-end">P.U. HT</th>
                                    <th class="text-end pe-3">Total HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($amendment->items as $item)
                                <tr class="{{ $item->is_deduction ? 'table-light' : '' }}">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bi {{ $item->is_deduction ? 'bi-dash-circle text-danger' : 'bi-plus-circle text-success' }} me-2"></i>
                                            {{ $item->description }}
                                        </div>
                                    </td>
                                    <td class="text-end fw-medium">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $item->unit ?? 'u' }}</span></td>
                                    <td class="text-end">{{ number_format($item->unit_price, 2, ',', ' ') }} Ar</td>
                                    <td class="text-end pe-3 fw-bold {{ $item->is_deduction ? 'text-danger' : 'text-dark' }}">
                                        {{ $item->is_deduction ? '-' : '' }}{{ number_format($item->total_ht, 2, ',', ' ') }} Ar
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4 justify-content-end">
                        <div class="col-md-5">
                            <div class="card bg-light border-0">
                                <div class="card-body p-0">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="ps-3 py-2 text-muted">Sous-total HT</td>
                                            <td class="pe-3 py-2 text-end fw-bold {{ $amendment->subtotal_ht < 0 ? 'text-danger' : 'text-dark' }}">
                                                {{ number_format($amendment->subtotal_ht, 2, ',', ' ') }} Ar
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 py-2 text-muted">TVA ({{ (int)$amendment->tva_rate }}%)</td>
                                            <td class="pe-3 py-2 text-end fw-bold text-dark">
                                                {{ number_format($amendment->tva_amount, 2, ',', ' ') }} Ar
                                            </td>
                                        </tr>
                                        <tr class="border-top border-2">
                                            <td class="ps-3 py-3 fw-bold text-dark fs-5">TOTAL TTC</td>
                                            <td class="pe-3 py-3 text-end fw-bold text-primary fs-5">
                                                {{ number_format($amendment->total_ttc, 2, ',', ' ') }} Ar
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($amendment->notes)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 fw-bold">
                    <i class="bi bi-sticky me-2"></i>Notes internes
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted fst-italic">{{ $amendment->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-xl-3">
            {{-- Status & Actions Card --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 fw-bold text-dark">
                    <i class="bi bi-lightning-charge me-2"></i>Actions de gestion
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($amendment->status === 'brouillon')
                            <form method="POST" action="{{ route('amendments.send', $amendment) }}">
                                @csrf
                                <button class="btn btn-info text-white w-100 shadow-sm mb-2">
                                    <i class="bi bi-send me-1"></i>Marquer comme Envoyé
                                </button>
                            </form>
                        @endif

                        @if($amendment->status === 'envoye')
                            <div class="alert alert-warning small mb-3">
                                <i class="bi bi-info-circle me-1"></i> L'avenant a été envoyé. Vous pouvez maintenant enregistrer la décision du client.
                            </div>
                            <form method="POST" action="{{ route('amendments.accept', $amendment) }}">
                                @csrf
                                <button class="btn btn-success w-100 shadow-sm mb-2" onclick="return confirm('Confirmer l\'acceptation de cet avenant ?')">
                                    <i class="bi bi-check-circle me-1"></i>Accepter l'avenant
                                </button>
                            </form>
                            <form method="POST" action="{{ route('amendments.refuse', $amendment) }}">
                                @csrf
                                <button class="btn btn-outline-danger w-100 shadow-sm" onclick="return confirm('Confirmer le refus de cet avenant ?')">
                                    <i class="bi bi-x-circle me-1"></i>Refuser l'avenant
                                </button>
                            </form>
                        @endif

                        @if($amendment->status === 'accepte')
                            <div class="text-center py-3">
                                <div class="display-6 text-success mb-2"><i class="bi bi-patch-check"></i></div>
                                <p class="fw-bold text-success mb-0">Avenant Validé</p>
                                <small class="text-muted">Les modifications sont effectives sur le projet.</small>
                            </div>
                        @endif
                        
                        @if($amendment->status === 'refuse')
                            <div class="text-center py-3 text-danger">
                                <div class="display-6 mb-2"><i class="bi bi-x-circle"></i></div>
                                <p class="fw-bold mb-0">Avenant Refusé</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3">Historique</h6>
                    <div class="small">
                        <div class="d-flex mb-2">
                            <span class="text-muted" style="width: 80px;">Auteur :</span>
                            <span class="text-dark fw-medium">{{ $amendment->createdBy->name ?? 'Système' }}</span>
                        </div>
                        <div class="d-flex mb-2">
                            <span class="text-muted" style="width: 80px;">Créé le :</span>
                            <span class="text-dark fw-medium">{{ $amendment->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($amendment->status === 'accepte')
                        <div class="d-flex mb-2">
                            <span class="text-muted" style="width: 80px;">Validé le :</span>
                            <span class="text-dark fw-medium text-success">{{ $amendment->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @can('delete', $amendment)
            <div class="card shadow-sm border-danger border-opacity-25 mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('amendments.destroy', $amendment) }}" onsubmit="return confirm('⚠️ Attention : La suppression est définitive. Continuer ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-link btn-sm text-danger text-decoration-none w-100">
                            <i class="bi bi-trash me-1"></i>Supprimer cet avenant
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-layouts.app>
