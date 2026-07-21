<x-layouts.app :title="$invoice->reference">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-decoration-none opacity-50 text-dark">Factures</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $invoice->reference }}</li>
    </x-slot>

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-receipt fs-1"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">{{ $invoice->reference }}</h4>
                                    <span class="badge {{ $invoice->status_badge_class }} badge-sm text-uppercase">
                                        {{ $invoice->status_libelle }}
                                    </span>
                                    @if($invoice->isOverdue())
                                        <span class="badge bg-danger badge-sm animate__animated animate__pulse animate__infinite">En retard</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span class="text-muted small"><i class="bx bx-calendar me-1"></i>{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                                    <span class="text-muted small"><i class="bx bx-user me-1"></i>{{ $invoice->client->name }}</span>
                                    <span class="badge bg-label-secondary text-uppercase" style="font-size: 0.65rem;">{{ $invoice->type }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('invoices.edit')
                                @if($invoice->status === 'brouillon')
                                    <form method="POST" action="{{ route('invoices.send', $invoice) }}">
                                        @csrf
                                        <button class="btn btn-info text-white shadow-sm px-3">
                                            <i class="bx bx-send me-1"></i>Envoyer
                                        </button>
                                    </form>
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary btn-icon shadow-sm" title="Modifier">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                @endif
                                @if(in_array($invoice->status, ['envoye', 'partiellement_payee']))
                                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success shadow-sm px-3">
                                        <i class="bx bx-money me-1"></i>Enregistrer Paiement
                                    </a>
                                @endif
                            @endcan
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-icon" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item py-2" href="#"><i class="bx bx-printer me-2 text-muted"></i> Imprimer</a></li>
                                    <li><a class="dropdown-item py-2" href="#"><i class="bx bx-file me-2 text-muted"></i> Télécharger PDF</a></li>
                                    @can('invoices.edit')
                                        @if(!in_array($invoice->status, ['soldee', 'annulee']))
                                            <li>
                                                <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Annuler cette facture ?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item py-2 text-warning"><i class="bx bx-block me-2"></i> Annuler la facture</button>
                                                </form>
                                            </li>
                                        @endif
                                    @endcan
                                    @can('invoices.delete')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Supprimer cette facture ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bx bx-trash me-2"></i> Supprimer</button>
                                            </form>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Net à payer</p>
                            <h4 class="mb-0 fw-bold mt-1 text-amount">{{ number_format($invoice->net_to_pay, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-wallet fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total payé</p>
                            <h4 class="mb-0 fw-bold mt-1 text-success text-amount">{{ number_format($invoice->amount_paid, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-check-circle fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Reste à payer</p>
                            <h4 class="mb-0 fw-bold mt-1 text-amount {{ $invoice->amount_remaining > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($invoice->amount_remaining, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small>
                            </h4>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-error-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Progression -->
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body py-3">
                    @php $percent = $invoice->net_to_pay > 0 ? ($invoice->amount_paid / $invoice->net_to_pay) * 100 : 0; @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Encaissement : {{ number_format($percent, 1) }}%</span>
                        <span class="fw-bold text-dark small">{{ number_format($invoice->amount_paid, 0, ',', ' ') }} / {{ number_format($invoice->net_to_pay, 0, ',', ' ') }} Ar</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Lines & Forms -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-2 text-primary"></i>Détail de la facturation</h6>
                    @if($invoice->status === 'brouillon')
                        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addItemForm">
                            <i class="bx bx-plus me-1"></i>Ajouter une ligne
                        </button>
                    @endif
                </div>

                @if($invoice->status === 'brouillon')
                <div class="collapse" id="addItemForm">
                    <div class="card-body border-bottom bg-light bg-opacity-50">
                        <form method="POST" action="{{ route('invoices.items.add', $invoice) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase">Désignation</label>
                                    <input type="text" name="description" class="form-control" placeholder="Description de l'article..." required>
                                </div>
                                <div class="col-md-2 col-6">
                                    <label class="form-label small fw-bold text-uppercase">Qté</label>
                                    <input type="number" name="quantity" class="form-control text-center" step="0.001" min="0" value="1" required>
                                </div>
                                <div class="col-md-4 col-6">
                                    <label class="form-label small fw-bold text-uppercase">PU HT</label>
                                    <div class="input-group">
                                        <input type="number" name="unit_price" class="form-control fw-bold" step="0.01" min="0" required>
                                        <span class="input-group-text bg-white">Ar</span>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                        <i class="bx bx-check me-1"></i>Ajouter la ligne
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Désignation</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-center">Qté</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-end">PU HT</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Total HT</th>
                                    @if($invoice->status === 'brouillon')<th class="border-0"></th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-medium text-dark">{{ $item->description }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-secondary px-2">{{ number_format($item->quantity, 2, ',', ' ') }}</span>
                                            <small class="text-muted ms-1">{{ $item->unit ?? 'u' }}</small>
                                        </td>
                                        <td class="text-end text-muted small text-amount">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                        <td class="pe-4 text-end fw-bold text-dark text-amount">{{ number_format($item->total_ht, 0, ',', ' ') }} <small class="text-muted">Ar</small></td>
                                        @if($invoice->status === 'brouillon')
                                            <td class="text-end pe-3">
                                                <form method="POST" action="{{ route('invoices.items.remove', [$invoice, $item]) }}" onsubmit="return confirm('Supprimer cette ligne ?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-icon btn-sm btn-label-danger"><i class="bx bx-trash"></i></button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted small">Aucune ligne de facturation.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary Footer -->
                <div class="card-footer bg-light bg-opacity-50 py-4 border-top">
                    <div class="row justify-content-end">
                        <div class="col-md-8 col-lg-7 col-xl-6 pe-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Sous-total HT :</span>
                                <span class="fw-bold text-dark text-amount">{{ number_format($invoice->subtotal_ht, 0, ',', ' ') }} Ar</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">TVA ({{ (float)$invoice->tva_rate }}%) :</span>
                                <span class="fw-bold text-dark text-amount">{{ number_format($invoice->tva_amount, 0, ',', ' ') }} Ar</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-dark text-amount">Total TTC :</span>
                                <span class="fw-bold text-dark text-amount">{{ number_format($invoice->total_ttc, 0, ',', ' ') }} Ar</span>
                            </div>
                            @if($invoice->rg_rate > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-danger small">Retenue de garantie ({{ (float)$invoice->rg_rate }}%) :</span>
                                    <span class="fw-bold text-danger text-amount">- {{ number_format($invoice->rg_amount, 0, ',', ' ') }} Ar</span>
                                </div>
                            @endif
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark fs-6 text-uppercase">Net à payer :</span>
                                <span class="fw-bold text-primary fs-4 text-amount">{{ number_format($invoice->net_to_pay, 0, ',', ' ') }} Ar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-credit-card me-2 text-success"></i>Historique des règlements</h6>
                    @if(in_array($invoice->status, ['envoye', 'partiellement_payee']))
                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-label-success">
                            <i class="bx bx-plus me-1"></i>Encaisser
                        </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Date</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted">Référence / Mode</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-end">Montant</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->payments as $pmt)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-medium text-dark">{{ $pmt->payment_date?->format('d/m/Y') }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-dark small fw-bold">{{ $pmt->reference }}</span>
                                                <small class="text-muted text-capitalize">{{ str_replace('_', ' ', $pmt->payment_mode) }}</small>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            + {{ number_format($pmt->pivot->amount, 0, ',', ' ') }} <small>Ar</small>
                                        </td>
                                        <td class="pe-4 text-end">
                                            @can('payments.delete')
                                                <form method="POST" action="{{ route('payments.destroy', $pmt) }}" onsubmit="return confirm('Annuler ce paiement ?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-icon btn-sm btn-label-danger"><i class="bx bx-x"></i></button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted small">Aucun règlement enregistré pour cette facture.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Client & Project Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Informations générales</h6>
                </div>
                <div class="card-body py-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-user fs-3"></i>
                        </div>
                        <div>
                            <a href="{{ route('clients.show', $invoice->client) }}" class="fw-bold text-dark text-decoration-none d-block fs-6">
                                {{ $invoice->client->name }}
                            </a>
                            <small class="text-muted">{{ $invoice->client->type_libelle }}</small>
                        </div>
                    </div>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Chantier :</span>
                            <span class="fw-bold text-dark small">{{ $invoice->project->name ?? 'Indépendant' }}</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Date d'émission :</span>
                            <span class="fw-bold text-dark small">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Date d'échéance :</span>
                            <span class="fw-bold {{ $invoice->isOverdue() ? 'text-danger' : 'text-dark' }} small">
                                {{ $invoice->due_date?->format('d/m/Y') ?? 'Non définie' }}
                            </span>
                        </li>
                        @if($invoice->quote)
                            <li class="d-flex justify-content-between align-items-center mb-3 pt-3 border-top">
                                <span class="text-muted small">Devis source :</span>
                                <a href="{{ route('quotes.show', $invoice->quote) }}" class="fw-bold text-primary small text-decoration-none">
                                    {{ $invoice->quote->reference }}
                                </a>
                            </li>
                        @endif
                        <li class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="text-muted small">Établie par :</span>
                            <span class="fw-medium text-dark small">{{ $invoice->createdBy->name ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Internal Notes -->
            @if($invoice->notes)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom bg-transparent py-3">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-note me-2 text-warning"></i>Notes d'observations</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">
                            {{ $invoice->notes }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Help / Next Steps -->
            @if($invoice->status === 'brouillon')
                <div class="card border-0 shadow-sm bg-label-info">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-2 text-info">Prochaine étape</h6>
                        <p class="small text-muted mb-0">Vérifiez toutes les lignes de facturation avant d'envoyer la facture au client. Une fois envoyée, vous pourrez enregistrer des paiements.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .last-child-mb-0:last-child {
            margin-bottom: 0 !important;
        }
        .badge-xs {
            padding: 0.2rem 0.4rem;
            font-size: 0.6rem;
        }
    </style>
    @endpush
</x-layouts.app>
