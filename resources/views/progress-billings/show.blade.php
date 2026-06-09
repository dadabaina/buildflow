<x-layouts.app :title="'Situation ' . $progressBilling->reference">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('progress-billings.index') }}" class="text-decoration-none opacity-50 text-dark">Situations</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $progressBilling->reference }}</li>
    </x-slot>

    @foreach(['success','error'] as $t)
        @if(session($t))
        <div class="alert alert-{{ $t === 'success' ? 'success' : 'danger' }} alert-dismissible fade show">
            {{ session($t) }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    @endforeach

    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-steps me-2"></i>{{ $progressBilling->reference }}
                        <span class="badge {{ $progressBilling->status_badge_class }} ms-2">{{ $progressBilling->status_libelle }}</span>
                        <span class="badge bg-primary ms-1">Situation N°{{ $progressBilling->situation_number }}</span>
                    </h5>
                    @if($progressBilling->status === 'brouillon')
                    <a href="{{ route('progress-billings.edit', $progressBilling) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Chantier</small>
                            <strong>{{ $progressBilling->project->name ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Date de situation</small>
                            <strong>{{ $progressBilling->billing_date->format('d/m/Y') }}</strong>
                        </div>
                        @if(($progressBilling ?? null)?->quote)
                        <div class="col-md-6">
                            <small class="text-muted d-block">Devis de référence</small>
                            <a href="{{ route('quotes.show', ($progressBilling ?? null)?->quote) }}" class="fw-bold text-primary text-decoration-none">{{ ($progressBilling ?? null)?->quote->reference }}</a>
                        </div>
                        @endif
                        @if(($progressBilling ?? null)?->invoice)
                        <div class="col-md-6">
                            <small class="text-muted d-block">Facture liée</small>
                            <a href="{{ route('invoices.show', ($progressBilling ?? null)?->invoice) }}" class="badge bg-success text-decoration-none">
                                <i class="bi bi-file-earmark-text me-1"></i>{{ ($progressBilling ?? null)?->invoice->reference }}
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- Tableau avancement --}}
                    <h6 class="mb-2">Tableau d'avancement</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Qté</th>
                                    <th>Unité</th>
                                    <th class="text-end">P.U. HT</th>
                                    <th class="text-end">% Préc.</th>
                                    <th class="text-end">% Cette sit.</th>
                                    <th class="text-end">% Cumulé</th>
                                    <th class="text-end">Montant HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($progressBilling->lines as $line)
                                <tr>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ number_format($line->quote_quantity, 3, ',', ' ') }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($line->unit_price, 2, ',', ' ') }}</td>
                                    <td class="text-end text-muted">{{ number_format($line->previous_pct, 1) }}%</td>
                                    <td class="text-end fw-semibold text-primary">{{ number_format($line->current_pct, 1) }}%</td>
                                    <td class="text-end">{{ number_format($line->cumulative_pct, 1) }}%</td>
                                    <td class="text-end fw-bold">{{ number_format($line->current_amount, 2, ',', ' ') }} Ar</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr><td colspan="7" class="text-end fw-semibold">Sous-total HT</td><td class="text-end fw-bold">{{ number_format($progressBilling->subtotal_ht, 2, ',', ' ') }} Ar</td></tr>
                                <tr><td colspan="7" class="text-end">TVA ({{ $progressBilling->tva_rate }}%)</td><td class="text-end">{{ number_format($progressBilling->tva_amount, 2, ',', ' ') }} Ar</td></tr>
                                <tr><td colspan="7" class="text-end fw-bold">Total TTC</td><td class="text-end fw-bold">{{ number_format($progressBilling->total_ttc, 2, ',', ' ') }} Ar</td></tr>
                                <tr><td colspan="7" class="text-end text-danger">Retenue de garantie ({{ $progressBilling->rg_rate }}%)</td><td class="text-end text-danger">− {{ number_format($progressBilling->rg_amount, 2, ',', ' ') }} Ar</td></tr>
                                <tr class="table-primary"><td colspan="7" class="text-end fw-bold fs-6">Net à payer</td><td class="text-end fw-bold fs-6 text-primary">{{ number_format($progressBilling->net_to_pay, 2, ',', ' ') }} Ar</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Actions</h6></div>
                <div class="card-body d-grid gap-2">
                    @if($progressBilling->status === 'brouillon')
                    <form method="POST" action="{{ route('progress-billings.send', $progressBilling) }}">
                        @csrf
                        <button class="btn btn-info text-white btn-sm w-100"><i class="bi bi-send me-1"></i>Marquer Envoyé</button>
                    </form>
                    @endif

                    @if($progressBilling->status === 'envoye')
                    <form method="POST" action="{{ route('progress-billings.validate', $progressBilling) }}">
                        @csrf
                        <button class="btn btn-success btn-sm w-100"><i class="bi bi-check-circle me-1"></i>Valider la situation</button>
                    </form>
                    @endif

                    @if($progressBilling->status === 'valide')
                    <form method="POST" action="{{ route('progress-billings.invoice', $progressBilling) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm w-100"><i class="bi bi-file-earmark-plus me-1"></i>Générer la facture</button>
                    </form>
                    @endif
                </div>
            </div>
            @can('progress_billings.delete')
            <div class="card border-danger">
                <div class="card-header bg-danger text-white"><h6 class="mb-0">Zone danger</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('progress-billings.destroy', $progressBilling) }}" onsubmit="return confirm('Supprimer cette situation ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Supprimer</button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-layouts.app>
