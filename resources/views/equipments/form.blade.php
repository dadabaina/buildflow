<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Matériels</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($equipment) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-card title="{{ isset($equipment) ? 'Modifier le matériel' : 'Nouveau matériel' }}">
                <form method="POST" action="{{ isset($equipment) ? route('equipments.update', $equipment) : route('equipments.store') }}">
                    @csrf @if(isset($equipment)) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nom / Désignation <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $equipment->name ?? '') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Référence interne</label>
                            <input type="text" name="reference" class="form-control"
                                   value="{{ old('reference', $equipment->reference ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Catégorie</label>
                            <input type="text" name="category" class="form-control"
                                   value="{{ old('category', $equipment->category ?? '') }}" list="cats" placeholder="Ex: Engin, Outil...">
                            <datalist id="cats">
                                <option value="Engin de chantier">
                                <option value="Outil électroportatif">
                                <option value="Véhicule">
                                <option value="Échafaudage">
                                <option value="Coffrage">
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Marque</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand', $equipment->brand ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Modèle</label>
                            <input type="text" name="model" class="form-control" value="{{ old('model', $equipment->model ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Numéro de série</label>
                            <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $equipment->serial_number ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Date d'acquisition</label>
                            <input type="date" name="acquisition_date" class="form-control"
                                   value="{{ old('acquisition_date', isset($equipment) && $equipment->acquisition_date ? $equipment->acquisition_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Coût d'acquisition (Ar)</label>
                            <input type="number" name="acquisition_cost" class="form-control" step="0.01" min="0"
                                   value="{{ old('acquisition_cost', $equipment->acquisition_cost ?? 0) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Type de propriété <span class="text-danger">*</span></label>
                            <select name="is_internal" class="form-select" id="is_internal" required>
                                <option value="1" {{ old('is_internal', $equipment->is_internal ?? 1) == 1 ? 'selected' : '' }}>Interne (Propriété)</option>
                                <option value="0" {{ old('is_internal', $equipment->is_internal ?? 1) == 0 ? 'selected' : '' }}>Externe (Location)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="supplier_group" style="{{ old('is_internal', $equipment->is_internal ?? 1) == 0 ? '' : 'display:none' }}">
                            <label class="form-label fw-semibold small">Fournisseur / Loueur</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">— Sélectionner le loueur —</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id', $equipment->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Coût location/jour (Ar)</label>
                            <input type="number" name="daily_rental_cost" class="form-control" step="0.01" min="0"
                                   value="{{ old('daily_rental_cost', $equipment->daily_rental_cost ?? 0) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Statut <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['disponible'=>'Disponible','affecte'=>'Affecté','maintenance'=>'Maintenance','hors_service'=>'Hors service'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('status', $equipment->status ?? 'disponible') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $equipment->notes ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                        <a href="{{ route('equipments.index') }}" class="btn btn-light">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1"></i>{{ isset($equipment) ? 'Mettre à jour' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
    @push('scripts')
    <script>
        document.getElementById('is_internal').addEventListener('change', function() {
            document.getElementById('supplier_group').style.display = this.value == '0' ? 'block' : 'none';
        });
    </script>
    @endpush
</x-layouts.app>
