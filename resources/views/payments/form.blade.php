<x-layouts.app title="Nouveau paiement">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}" class="text-decoration-none opacity-50 text-dark">Paiements</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Nouveau</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash me-2"></i>Enregistrer un paiement</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payments.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Facture <span class="text-danger">*</span></label>
                                <select name="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($invoices as $inv)
                                    <option value="{{ $inv->id }}"
                                        {{ old('invoice_id', $selectedInvoice?->id) == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->reference }} — {{ $inv->client?->name }} (Reste: {{ number_format($inv->amount_remaining, 0, ',', ' ') }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('invoice_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Montant (MGA) <span class="text-danger">*</span></label>
                                <input type="number" name="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       step="1" min="1"
                                       value="{{ old('amount', $selectedInvoice?->amount_remaining) }}" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date du paiement <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date"
                                       class="form-control @error('payment_date') is-invalid @enderror"
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                                @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                <select name="method" class="form-select @error('method') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach(['Virement bancaire', 'Chèque', 'Espèces', 'Mobile Money', 'Autre'] as $m)
                                    <option value="{{ $m }}" {{ old('method') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                                @error('method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Référence / N° de chèque</label>
                                <input type="text" name="reference" class="form-control"
                                       value="{{ old('reference') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer le paiement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
