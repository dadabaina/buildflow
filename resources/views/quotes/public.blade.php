<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Devis {{ $quote->reference }} — BuildFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f4f5fb; }
        .quote-card { max-width: 780px; margin: 2rem auto; }
    </style>
</head>
<body>
<div class="container quote-card">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1">Devis {{ $quote->reference }}</h4>
                    <p class="text-muted mb-0">{{ $quote->title }}</p>
                </div>
                <div class="text-end">
                    @php
                        $badges = [
                            'brouillon' => 'secondary',
                            'envoye'    => 'info',
                            'accepte'   => 'success',
                            'refuse'    => 'danger',
                            'annule'    => 'dark',
                        ];
                        $libelles = [
                            'brouillon' => 'Brouillon',
                            'envoye'    => 'En attente',
                            'accepte'   => 'Accepté',
                            'refuse'    => 'Refusé',
                            'annule'    => 'Annulé',
                        ];
                    @endphp
                    <span class="badge bg-{{ $badges[$quote->status] ?? 'secondary' }} fs-6 px-3 py-2">
                        {{ $libelles[$quote->status] ?? $quote->status }}
                    </span>
                </div>
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-sm-6">
                    <p class="text-muted small mb-1 text-uppercase fw-semibold">Client</p>
                    <p class="mb-0 fw-medium">{{ $quote->client->name }}</p>
                </div>
                @if($quote->project)
                <div class="col-sm-6">
                    <p class="text-muted small mb-1 text-uppercase fw-semibold">Projet</p>
                    <p class="mb-0 fw-medium">{{ $quote->project->name }}</p>
                </div>
                @endif
                <div class="col-sm-6">
                    <p class="text-muted small mb-1 text-uppercase fw-semibold">Date du devis</p>
                    <p class="mb-0">{{ $quote->quote_date->format('d/m/Y') }}</p>
                </div>
                @if($quote->valid_until)
                <div class="col-sm-6">
                    <p class="text-muted small mb-1 text-uppercase fw-semibold">Valide jusqu'au</p>
                    <p class="mb-0 {{ $quote->valid_until->isPast() ? 'text-danger' : '' }}">
                        {{ $quote->valid_until->format('d/m/Y') }}
                        @if($quote->valid_until->isPast()) <span class="badge bg-danger ms-1">Expiré</span> @endif
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Lignes du devis --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h6 class="fw-bold mb-0">Détail du devis</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Description</th>
                            <th class="text-end">Qté</th>
                            <th class="text-end">Unité</th>
                            <th class="text-end pe-4">P.U. HT</th>
                            <th class="text-end pe-4">Total HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quote->sections as $section)
                            <tr class="table-secondary">
                                <td colspan="5" class="ps-4 fw-semibold">{{ $section->title }}</td>
                            </tr>
                            @foreach($section->items as $item)
                            <tr>
                                <td class="ps-5">{{ $item->description }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $item->unit }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                <td class="text-end pe-4">{{ number_format($item->total_ht, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        @empty
                            @foreach($quote->items as $item)
                            <tr>
                                <td class="ps-4">{{ $item->description }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ $item->unit }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                <td class="text-end pe-4">{{ number_format($item->total_ht, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top px-4 py-3">
            <div class="row justify-content-end">
                <div class="col-sm-5">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Sous-total HT</span>
                        <span>{{ number_format($quote->subtotal_ht, 0, ',', ' ') }} Ar</span>
                    </div>
                    @if($quote->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-1 text-danger">
                        <span>Remise</span>
                        <span>− {{ number_format($quote->discount_amount, 0, ',', ' ') }} Ar</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">TVA ({{ $quote->tva_rate }}%)</span>
                        <span>{{ number_format($quote->tva_amount, 0, ',', ' ') }} Ar</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2 mt-2">
                        <span>Total TTC</span>
                        <span>{{ number_format($quote->total_ttc, 0, ',', ' ') }} Ar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($quote->notes)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body px-4">
            <h6 class="fw-bold mb-2">Notes</h6>
            <p class="mb-0 text-muted">{{ $quote->notes }}</p>
        </div>
    </div>
    @endif

    @if($quote->client_responded_at && $quote->client_response_note)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body px-4">
            <h6 class="fw-bold mb-2">Votre commentaire</h6>
            <p class="mb-0 text-muted">{{ $quote->client_response_note }}</p>
        </div>
    </div>
    @endif

    {{-- Session messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    {{-- Decision form (only when status is 'envoye') --}}
    @if($quote->status === 'envoye')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">Votre décision</h6>
            <form method="POST" action="{{ route('quotes.public.validate', $token) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Commentaire <span class="text-muted">(optionnel)</span></label>
                    <textarea name="note" class="form-control" rows="3" maxlength="1000"
                        placeholder="Remarques, questions…">{{ old('note') }}</textarea>
                    @error('note') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="decision" value="accepte"
                        class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i>Accepter le devis
                    </button>
                    <button type="submit" name="decision" value="refuse"
                        class="btn btn-outline-danger px-4"
                        onclick="return confirm('Confirmer le refus du devis ?')">
                        <i class="bi bi-x-circle me-1"></i>Refuser
                    </button>
                </div>
                @error('decision') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </form>
        </div>
    </div>
    @endif

</div>
</body>
</html>
