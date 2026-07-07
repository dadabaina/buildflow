<x-layouts.app :title="$quote->reference">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}" class="text-decoration-none opacity-50 text-dark">Devis</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $quote->reference }}</li>
    </x-slot>

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="overflow: visible;">
                <div class="card-body p-0" style="overflow: visible;">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="overflow: visible;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-spreadsheet fs-1"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">{{ $quote->reference }}</h4>
                                    <span class="badge {{ $quote->status_badge_class }} badge-sm text-uppercase">
                                        {{ $quote->status_libelle }}
                                    </span>
                                    @if($quote->version > 1)
                                        <span class="badge bg-secondary badge-sm">v{{ $quote->version }}</span>
                                    @endif
                                    @if($quote->valid_until && $quote->valid_until->isPast() && !in_array($quote->status, ['accepte', 'refuse', 'annule']))
                                        <span class="badge bg-danger badge-sm">Expiré</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <span class="text-muted small"><i class="bx bx-calendar me-1"></i>{{ $quote->quote_date->format('d/m/Y') }}</span>
                                    <span class="text-muted small"><i class="bx bx-user me-1"></i>{{ $quote->client->name }}</span>
                                    @if($quote->project)
                                        <span class="text-muted small"><i class="bx bx-building me-1"></i>{{ $quote->project->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('quotes.edit')
                                @if($quote->status === 'brouillon')
                                    <form method="POST" action="{{ route('quotes.accept', $quote) }}">
                                        @csrf
                                        <button id="tour-quote-accept" class="btn btn-success shadow-sm px-3" onclick="return confirm('Valider et accepter ce devis ? Cela activera le chantier.')">
                                            <i class="bx bx-check-circle me-1"></i>Accepter & Créer le Chantier
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('quotes.send', $quote) }}">
                                        @csrf
                                        <button id="tour-quote-send" class="btn btn-info text-white shadow-sm px-3">
                                            <i class="bx bx-send me-1"></i>Envoyer
                                        </button>
                                    </form>
                                    <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-outline-secondary btn-icon shadow-sm" title="Modifier">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                @endif
                                @if($quote->status === 'envoye')
                                    <form method="POST" action="{{ route('quotes.accept', $quote) }}">
                                        @csrf
                                        <button id="tour-quote-accept" class="btn btn-success shadow-sm px-3" onclick="return confirm('Accepter ce devis ? Cela activera le chantier.')">
                                            <i class="bx bx-check-circle me-1"></i>Accepter & Créer le Chantier
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('quotes.refuse', $quote) }}"
                                          onsubmit="return confirm('Marquer ce devis comme refusé ?')">
                                        @csrf
                                        <button class="btn btn-outline-danger shadow-sm px-3">
                                            <i class="bx bx-x-circle me-1"></i>Refuser
                                        </button>
                                    </form>
                                @endif
                                @if($quote->status === 'accepte')
                                    <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                                        @csrf
                                        <button class="btn btn-success shadow-sm px-3">
                                            <i class="bx bx-receipt me-1"></i>Facturer
                                        </button>
                                    </form>
                                @endif
                            @endcan
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="z-index: 1060;">
                                    <li><a class="dropdown-item py-2" href="{{ route('quotes.pdf', $quote) }}"><i class="bx bx-printer me-2 text-muted"></i> Télécharger PDF</a></li>
                                    <li>
                                        <button type="button" class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#duplicateQuoteModal">
                                            <i class="bx bx-copy me-2 text-muted"></i> Dupliquer le devis
                                        </button>
                                    </li>
                                    @can('quotes.delete')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('quotes.destroy', $quote) }}" onsubmit="return confirm('Supprimer ce devis ?')">
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

    <!-- Financial Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Sous-total HT</p>
                            <h4 class="mb-0 fw-bold mt-1 text-amount">{{ number_format($quote->subtotal_ht, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-secondary rounded p-2">
                            <i class="bx bx-money fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Remise ({{ (float)$quote->discount_global }}{{ $quote->discount_type === 'percent' ? '%' : ' Ar' }})</p>
                            <h4 class="mb-0 fw-bold mt-1 {{ $quote->discount_amount > 0 ? 'text-danger' : '' }}">- {{ number_format($quote->discount_amount, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-danger rounded p-2">
                            <i class="bx bx-minus-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">TVA ({{ (float)$quote->tva_rate }}%)</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ number_format($quote->tva_amount, 0, ',', ' ') }} <small class="fs-6 fw-normal text-muted">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-calculator fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total TTC</p>
                            <h4 class="mb-0 fw-bold mt-1 text-primary text-amount">{{ number_format($quote->total_ttc, 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-check-double fs-3"></i>
                        </div>
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
                    <h6 class="mb-0 fw-bold"><i class="bx bx-list-ul me-2 text-primary"></i>Lignes du devis</h6>
                    @if($quote->status === 'brouillon')
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#dosageCalcModal">
                                <i class="bx bx-calculator me-1"></i>Calcul dosage
                            </button>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addSectionForm">
                                <i class="bx bx-category me-1"></i>Ajouter un lot
                            </button>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addItemForm">
                                <i class="bx bx-plus me-1"></i>Ajouter une ligne
                            </button>
                        </div>
                    @endif
                </div>

                @if($quote->status === 'brouillon')
                <div class="collapse" id="addSectionForm">
                    <div class="card-body border-bottom bg-light bg-opacity-25">
                        <form method="POST" action="{{ route('quotes.sections.add', $quote) }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-uppercase">Titre du lot / section</label>
                                <input type="text" name="title" class="form-control" placeholder="Ex: Gros œuvre, Électricité…" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bx bx-plus me-1"></i>Créer le lot
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @if($quote->status === 'brouillon')
                <div class="collapse" id="addItemForm">
                    <div class="card-body border-bottom bg-light bg-opacity-50">
                        <form method="POST" action="{{ route('quotes.items.add', $quote) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-uppercase">Désignation des travaux</label>
                                    <input type="text" name="description" class="form-control" placeholder="Ex: Terrassement massif..." required>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small fw-bold text-uppercase">Quantité</label>
                                    <input type="number" name="quantity" class="form-control" step="0.001" min="0" value="1" required>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small fw-bold text-uppercase">Unité</label>
                                    <select name="unit" class="form-select">
                                        <option value="">—</option>
                                        @foreach($unitTypes as $ut)
                                            <option value="{{ $ut->symbol }}">{{ $ut->symbol }} – {{ $ut->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-uppercase">Prix Unitaire HT</label>
                                    <div class="input-group">
                                        <input type="number" name="unit_price" class="form-control fw-bold" step="0.01" min="0" required>
                                        <span class="input-group-text bg-white">Ar</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-uppercase">Remise (%)</label>
                                    <input type="number" name="discount" class="form-control" step="0.1" min="0" max="100" value="0">
                                </div>
                                @if($quote->sections->isNotEmpty())
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Section / Lot</label>
                                    <select name="quote_section_id" class="form-select">
                                        <option value="">— Sans section —</option>
                                        @foreach($quote->sections as $sec)
                                            <option value="{{ $sec->id }}">{{ $sec->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                                        <i class="bx bx-check me-1"></i>Valider
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <div class="card-body p-0" id="tour-quote-items">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Désignation</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-center">Qté</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-end">PU HT</th>
                                    <th class="py-3 border-0 small text-uppercase text-muted text-end">Remise</th>
                                    <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Total HT</th>
                                    @if($quote->status === 'brouillon')<th class="border-0"></th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $hasSections = $quote->sections->isNotEmpty(); @endphp

                                @if($hasSections)
                                    @foreach($quote->sections as $section)
                                        <tr class="table-primary">
                                            <td colspan="{{ $quote->status === 'brouillon' ? 6 : 5 }}" class="ps-3 fw-bold text-primary border-start border-3 border-primary">
                                                {{ $section->title }}
                                                @if($quote->status === 'brouillon')
                                                <form method="POST" action="{{ route('quotes.sections.remove', [$quote, $section]) }}"
                                                      class="d-inline float-end me-2" onsubmit="return confirm('Supprimer cette section ? Les lignes seront conservées.')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-xs btn-label-danger py-0 px-1" style="font-size:11px"><i class="bx bx-trash"></i></button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @forelse($section->items as $item)
                                            <tr>
                                                <td class="ps-5"><span class="fw-medium text-dark">{{ $item->description }}</span></td>
                                                <td class="text-center">
                                                    <span class="badge bg-label-secondary px-2">{{ number_format($item->quantity, 2, ',', ' ') }}</span>
                                                    <small class="text-muted ms-1">{{ $item->unit ?? 'u' }}</small>
                                                </td>
                                                <td class="text-end text-muted small text-amount">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                                <td class="text-end small text-amount">{{ $item->discount > 0 ? number_format($item->discount, 1, ',', ' ').' %' : '—' }}</td>
                                                <td class="pe-4 text-end fw-bold text-dark text-amount">{{ number_format($item->total_ht, 0, ',', ' ') }} <small class="text-muted">Ar</small></td>
                                                @if($quote->status === 'brouillon')
                                                <td class="text-end pe-3">
                                                    <form method="POST" action="{{ route('quotes.items.remove', [$quote, $item]) }}" onsubmit="return confirm('Supprimer cette ligne ?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-icon btn-sm btn-label-danger"><i class="bx bx-trash"></i></button>
                                                    </form>
                                                </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr><td colspan="{{ $quote->status === 'brouillon' ? 6 : 5 }}" class="ps-5 text-muted small fst-italic">Aucune ligne dans ce lot.</td></tr>
                                        @endforelse
                                    @endforeach

                                    @php $unsectioned = $quote->items->whereNull('quote_section_id') @endphp
                                    @if($unsectioned->isNotEmpty())
                                        <tr class="table-secondary">
                                            <td colspan="{{ $quote->status === 'brouillon' ? 6 : 5 }}" class="ps-3 fw-bold text-muted">Divers / Sans section</td>
                                        </tr>
                                        @foreach($unsectioned as $item)
                                        <tr>
                                            <td class="ps-4"><span class="fw-medium text-dark">{{ $item->description }}</span></td>
                                            <td class="text-center">
                                                <span class="badge bg-label-secondary px-2">{{ number_format($item->quantity, 2, ',', ' ') }}</span>
                                                <small class="text-muted ms-1">{{ $item->unit ?? 'u' }}</small>
                                            </td>
                                            <td class="text-end text-muted small text-amount">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                            <td class="text-end small text-amount">{{ $item->discount > 0 ? number_format($item->discount, 1, ',', ' ').' %' : '—' }}</td>
                                            <td class="pe-4 text-end fw-bold text-dark text-amount">{{ number_format($item->total_ht, 0, ',', ' ') }} <small class="text-muted">Ar</small></td>
                                            @if($quote->status === 'brouillon')
                                            <td class="text-end pe-3">
                                                <form method="POST" action="{{ route('quotes.items.remove', [$quote, $item]) }}" onsubmit="return confirm('Supprimer cette ligne ?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-icon btn-sm btn-label-danger"><i class="bx bx-trash"></i></button>
                                                </form>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    @endif
                                @else
                                    @forelse($quote->items as $item)
                                        <tr>
                                            <td class="ps-4"><span class="fw-medium text-dark">{{ $item->description }}</span></td>
                                            <td class="text-center">
                                                <span class="badge bg-label-secondary px-2">{{ number_format($item->quantity, 2, ',', ' ') }}</span>
                                                <small class="text-muted ms-1">{{ $item->unit ?? 'u' }}</small>
                                            </td>
                                            <td class="text-end text-muted small text-amount">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                            <td class="text-end small text-amount">{{ $item->discount > 0 ? number_format($item->discount, 1, ',', ' ').' %' : '—' }}</td>
                                            <td class="pe-4 text-end fw-bold text-dark text-amount">{{ number_format($item->total_ht, 0, ',', ' ') }} <small class="text-muted">Ar</small></td>
                                            @if($quote->status === 'brouillon')
                                                <td class="text-end pe-3">
                                                    <form method="POST" action="{{ route('quotes.items.remove', [$quote, $item]) }}" onsubmit="return confirm('Supprimer cette ligne ?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-icon btn-sm btn-label-danger"><i class="bx bx-trash"></i></button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ $quote->status === 'brouillon' ? 6 : 5 }}" class="text-center py-5 text-muted small">Aucun article dans ce devis.</td></tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light bg-opacity-50 py-4 border-top">
                    <div class="row justify-content-end">
                        <div class="col-md-8 col-lg-7 col-xl-6 pe-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Sous-total HT :</span>
                                <span class="fw-bold text-dark text-amount">{{ number_format($quote->subtotal_ht, 0, ',', ' ') }} Ar</span>
                            </div>
                            @if($quote->discount_amount > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Remise ({{ (float)$quote->discount_global }}{{ $quote->discount_type === 'percent' ? '%' : '' }}) :</span>
                                    <span class="fw-bold text-danger text-amount">- {{ number_format($quote->discount_amount, 0, ',', ' ') }} Ar</span>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">TVA ({{ (float)$quote->tva_rate }}%) :</span>
                                <span class="fw-bold text-dark text-amount">{{ number_format($quote->tva_amount, 0, ',', ' ') }} Ar</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold text-dark fs-6 text-uppercase">Total TTC :</span>
                                <span class="fw-bold text-primary fs-4 text-amount">{{ number_format($quote->total_ttc, 0, ',', ' ') }} Ar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom bg-transparent py-3">
                            <h6 class="mb-0 fw-bold"><i class="bx bx-info-circle me-2 text-primary"></i>Notes d'observations</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">
                                {{ $quote->notes ?? 'Aucune note spécifique.' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom bg-transparent py-3">
                            <h6 class="mb-0 fw-bold"><i class="bx bx-file me-2 text-primary"></i>Conditions & Validité</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">
                                {{ $quote->terms ?? 'Conditions générales de vente standards.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Informations Client</h6>
                </div>
                <div class="card-body py-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-user fs-3"></i>
                        </div>
                        <div>
                            <a href="{{ route('clients.show', $quote->client) }}" class="fw-bold text-dark text-decoration-none d-block fs-6">
                                {{ $quote->client->name }}
                            </a>
                            <small class="text-muted">{{ $quote->client->type_libelle }}</small>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-3">
                            <i class="bx bx-phone me-3 text-primary fs-5"></i>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Téléphone</small>
                                <span class="fw-medium text-dark">{{ $quote->client->phone ?? '—' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <i class="bx bx-envelope me-3 text-primary fs-5"></i>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Email</small>
                                <span class="fw-medium text-dark text-truncate" style="max-width: 200px;">{{ $quote->client->email ?? '—' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="bx bx-map me-3 text-primary fs-5 mt-1"></i>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Adresse</small>
                                <span class="fw-medium text-dark small">{{ $quote->client->address ?? '—' }}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Détails généraux</h6>
                </div>
                <div class="card-body py-4">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Chantier :</span>
                            <span class="fw-bold text-dark">{{ $quote->project->name ?? 'Indépendant' }}</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Date d'émission :</span>
                            <span class="fw-bold text-dark">{{ $quote->quote_date->format('d/m/Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Échéance :</span>
                            <span class="fw-bold {{ $quote->valid_until && $quote->valid_until->isPast() ? 'text-danger' : 'text-dark' }}">
                                {{ $quote->valid_until?->format('d/m/Y') ?? 'Non définie' }}
                            </span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="text-muted small">Créé par :</span>
                            <span class="fw-medium text-dark small">{{ $quote->createdBy->name ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if($quote->client_token)
                <div class="card border-0 shadow-sm mb-4 bg-label-info">
                    <div class="card-body p-4 text-center">
                        <i class="bx bx-link-external fs-1 mb-2 text-info"></i>
                        <h6 class="fw-bold mb-2 text-info">Lien de validation publique</h6>
                        <p class="small text-muted mb-3">Partagez ce lien avec votre client pour qu'il puisse valider ou refuser le devis en ligne.</p>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" value="{{ route('quotes.public', $quote->client_token) }}" id="publicLink" readonly>
                            <button class="btn btn-outline-info" type="button" onclick="copyLink()">
                                <i class="bx bx-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($quote->client_responded_at)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom bg-transparent py-3">
                        <h6 class="mb-0 fw-bold text-success">Réponse Client</h6>
                    </div>
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bx bx-calendar-check text-success fs-5"></i>
                            <span class="small fw-bold">{{ $quote->client_responded_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        @if($quote->client_response_note)
                            <div class="bg-light p-3 rounded italic small text-muted">
                                "{{ $quote->client_response_note }}"
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($quote->status === 'brouillon')
        <!-- Dosage Modal -->
        <div class="modal fade" id="dosageCalcModal" tabindex="-1" x-data="dosageCalc()">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bx bx-calculator me-2 text-primary"></i>Calcul depuis un modèle de dosage
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div x-show="step === 1">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-uppercase">Modèle de dosage</label>
                                    <select x-model="form.dosage_model_id" class="form-select" required>
                                        <option value="">— Sélectionner un modèle —</option>
                                        @foreach($dosageModels as $dm)
                                            <option value="{{ $dm->id }}">{{ $dm->name }} ({{ $dm->output_unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-uppercase">Quantité</label>
                                    <input type="number" x-model.number="form.quantity" class="form-control" step="0.001" min="0.001">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-uppercase">Description</label>
                                    <input type="text" x-model="form.description" class="form-control" placeholder="Auto-remplie si vide">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-uppercase">Unité</label>
                                    <input type="text" x-model="form.unit" class="form-control" placeholder="Ex: m3">
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="bg-light p-3 rounded border border-dashed">
                                        <h6 class="mb-3 small fw-bold text-uppercase text-muted"><i class="bx bx-cog me-1"></i>Coefficients d'ajustement</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small">Frais généraux (%)</label>
                                                <input type="number" x-model.number="form.fg_rate" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Marge (%)</label>
                                                <input type="number" x-model.number="form.margin_rate" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Aléas (%)</label>
                                                <input type="number" x-model.number="form.alea_rate" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div x-show="step === 2">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="bg-light p-3 rounded text-center border">
                                        <small class="text-muted d-block text-uppercase mb-1">Coût DBE</small>
                                        <span class="fw-bold fs-5" x-text="fmt(result.dbe_total)"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light p-3 rounded text-center border">
                                        <small class="text-muted d-block text-uppercase mb-1">Coût Unitaire DBE</small>
                                        <span class="fw-bold fs-5" x-text="fmt(result.dbe_unit)"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded text-center border border-primary">
                                        <small class="text-primary d-block text-uppercase mb-1">Prix de vente U.</small>
                                        <span class="fw-bold text-primary fs-5" x-text="fmt(result.unit_price)"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive border rounded mb-3" style="max-height: 300px;">
                                <table class="table table-sm table-hover mb-0 align-middle">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th class="border-0 ps-3">Ressource</th>
                                            <th class="border-0 text-end">Qté</th>
                                            <th class="border-0">Unité</th>
                                            <th class="border-0 text-end pe-3">Coût</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, i) in result.breakdown" :key="i">
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="small" x-text="row.description"></span>
                                                </td>
                                                <td class="text-end small" x-text="row.total_quantity"></td>
                                                <td class="small" x-text="row.unit"></td>
                                                <td class="text-end pe-3 fw-bold small" x-text="fmt(row.line_cost)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div>
                                <label class="form-label fw-bold small text-uppercase">Prix unitaire à appliquer</label>
                                <div class="input-group">
                                    <input type="number" x-model.number="result.unit_price" class="form-control fw-bold border-primary">
                                    <span class="input-group-text bg-primary text-white">Ar</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-outline-primary" x-show="step === 2" @click="step = 1">Retour</button>
                        <button type="button" class="btn btn-primary px-4" x-show="step === 1" :disabled="loading" @click="calculate()">Calculer</button>
                        <button type="button" class="btn btn-success px-4" x-show="step === 2" @click="applyToQuote()">Ajouter au devis</button>
                    </div>
                </div>
            </div>
        </div>
        <form id="dosageLineForm" method="POST" action="{{ route('quotes.items.add', $quote) }}" style="display:none">
            @csrf
            <input type="hidden" name="description" id="dbl_description">
            <input type="hidden" name="quantity"    id="dbl_quantity">
            <input type="hidden" name="unit"        id="dbl_unit">
            <input type="hidden" name="unit_price"  id="dbl_unit_price">
        </form>
    @endif

    <!-- Duplicate Modal -->
    <div class="modal fade" id="duplicateQuoteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="bx bx-copy me-2 text-primary"></i>Dupliquer le devis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('quotes.duplicate', $quote) }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Nouveau titre / Objet du devis</label>
                            <input type="text" name="title" class="form-control" value="{{ $quote->title }} (copie)" required>
                            <div class="form-text small">Le nouveau devis sera créé en statut <strong>Brouillon</strong>.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="bx bx-check me-1"></i>Dupliquer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    @if($quote->status === 'brouillon')
    function dosageCalc() {
        return {
            step: 1, loading: false, error: null, result: {},
            form: {
                dosage_model_id: '', quantity: 1, description: '', unit: '',
                fg_rate: {{ $company->fg_rate ?? 0 }},
                margin_rate: {{ $company->marge_rate ?? 0 }},
                alea_rate: {{ $company->aleas_rate ?? 0 }},
            },
            calculate() {
                this.loading = true; this.error = null;
                fetch('{{ route('dosage.calculate') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.form)
                })
                .then(r => r.json()).then(data => {
                    this.result = data; this.step = 2;
                    if (!this.form.description) this.form.description = data.dosage_model?.name;
                    if (!this.form.unit) this.form.unit = data.dosage_model?.output_unit;
                })
                .catch(() => this.error = 'Erreur lors du calcul.')
                .finally(() => this.loading = false);
            },
            applyToQuote() {
                document.getElementById('dbl_description').value = this.form.description;
                document.getElementById('dbl_quantity').value = this.form.quantity;
                document.getElementById('dbl_unit').value = this.form.unit;
                document.getElementById('dbl_unit_price').value = this.result.unit_price;
                document.getElementById('dosageLineForm').submit();
            },
            fmt(v) { return Number(v).toLocaleString() + ' Ar'; }
        }
    }
    @endif
    function copyLink() {
        const el = document.getElementById('publicLink');
        if (el) { el.select(); document.execCommand('copy'); alert('Lien copié !'); }
    }
    </script>
    @endpush
</x-layouts.app>
