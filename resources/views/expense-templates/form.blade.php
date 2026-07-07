<x-layouts.app :title="isset($expenseTemplate) ? 'Modifier '.$expenseTemplate->name : 'Nouveau modèle de dépense'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expense-templates.index') }}" class="text-decoration-none opacity-50 text-dark">Modèles de dépense</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($expenseTemplate) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-receipt-cutoff me-2"></i>
                        {{ isset($expenseTemplate) ? 'Modifier le modèle de dépense' : 'Nouveau modèle de dépense' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($expenseTemplate) ? route('expense-templates.update', $expenseTemplate) : route('expense-templates.store') }}">
                        @csrf
                        @isset($expenseTemplate) @method('PATCH') @endisset

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nom du modèle <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Ex: Fouille en excavation"
                                       value="{{ old('name', $expenseTemplate->name ?? '') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unité produite <span class="text-danger">*</span></label>
                                <select name="output_unit"
                                        class="form-select @error('output_unit') is-invalid @enderror" required>
                                    <option value="">— Sélectionner —</option>
                                    @foreach($unitTypes as $ut)
                                        <option value="{{ $ut->symbol }}"
                                            {{ old('output_unit', $expenseTemplate->output_unit ?? '') === $ut->symbol ? 'selected' : '' }}>
                                            {{ $ut->name }} ({{ $ut->symbol }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Unité de l'ouvrage produit par ce modèle (ex: m³ excavé)</div>
                                @error('output_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Quantité produite par application <span class="text-danger">*</span></label>
                                <input type="number" name="output_quantity"
                                       class="form-control @error('output_quantity') is-invalid @enderror"
                                       step="0.001" min="0.001"
                                       value="{{ old('output_quantity', $expenseTemplate->output_quantity ?? 1) }}" required>
                                <div class="form-text">Ex: 1 (pour 1 m³ de fouille)</div>
                                @error('output_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                          placeholder="Contexte d'utilisation, type de tâche concernée...">{{ old('description', $expenseTemplate->description ?? '') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('expense-templates.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($expenseTemplate) ? 'Enregistrer' : 'Créer le modèle' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
