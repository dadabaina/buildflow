<x-layouts.app :title="isset($quote) ? 'Modifier devis' : 'Nouveau devis'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}" class="text-decoration-none opacity-50 text-dark">Devis</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($quote) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-file-text me-2"></i>
                        {{ isset($quote) ? 'Modifier le devis' : 'Nouveau devis' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($quote) ? route('quotes.update', $quote) : route('quotes.store') }}">
                        @csrf
                        @isset($quote) @method('PATCH') @endisset

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Titre / Objet <span class="text-danger">*</span></label>
                                <input type="text" name="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       placeholder="Ex: Rénovation Villa, Installation électrique..."
                                       value="{{ old('title', $quote->title ?? '') }}" required>
                                <div class="form-text small">Ce titre deviendra le nom du chantier si vous ne sélectionnez pas de chantier existant ci-dessous.</div>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Chantier (Optionnel)</label>
                                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                                    <option value="">— Créer un nouveau chantier à la validation —</option>
                                    @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}"
                                        {{ old('project_id', $quote->project_id ?? $selectedProject?->id) == $proj->id ? 'selected' : '' }}>
                                        {{ $proj->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text small">Laissez vide pour lier ce devis à un futur nouveau chantier.</div>
                                @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id', $quote->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date du devis <span class="text-danger">*</span></label>
                                <input type="date" name="quote_date"
                                       class="form-control @error('quote_date') is-invalid @enderror"
                                       value="{{ old('quote_date', isset($quote) ? $quote->quote_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                @error('quote_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Valide jusqu'au</label>
                                <input type="date" name="valid_until" class="form-control"
                                       value="{{ old('valid_until', isset($quote) ? $quote->valid_until?->format('Y-m-d') : now()->addDays(30)->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">TVA (%)</label>
                                <input type="number" name="tva_rate" class="form-control"
                                       step="0.01" min="0" max="100"
                                       value="{{ old('tva_rate', $quote->tva_rate ?? 20) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Remise globale</label>
                                <input type="number" name="discount_global" class="form-control"
                                       step="0.01" min="0"
                                       value="{{ old('discount_global', $quote->discount_global ?? 0) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type de remise</label>
                                <select name="discount_type" class="form-select">
                                    <option value="percent" {{ old('discount_type', $quote->discount_type ?? 'percent') === 'percent' ? 'selected' : '' }}>Pourcentage (%)</option>
                                    <option value="amount" {{ old('discount_type', $quote->discount_type ?? '') === 'amount' ? 'selected' : '' }}>Montant fixe (MGA)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes / Remarques</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $quote->notes ?? '') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Conditions générales</label>
                                <textarea name="terms" class="form-control" rows="2">{{ old('terms', $quote->terms ?? '') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($quote) ? 'Enregistrer' : 'Créer le devis' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
