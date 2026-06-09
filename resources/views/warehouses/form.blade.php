<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Dépôts</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($warehouse) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <x-card title="{{ isset($warehouse) ? 'Modifier le dépôt' : 'Nouveau dépôt' }}">
                <form method="POST" action="{{ isset($warehouse) ? route('warehouses.update', $warehouse) : route('warehouses.store') }}">
                    @csrf @if(isset($warehouse)) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Nom du dépôt <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $warehouse->name ?? '') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Rattaché à un chantier (Optionnel)</label>
                            <select name="project_id" class="form-select select2">
                                <option value="">— Dépôt indépendant / central —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $warehouse->project_id ?? '') == $p->id ? 'selected' : '' }}>
                                        [{{ $p->reference }}] {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Laissez vide si c'est un dépôt central ou régional.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Emplacement / Adresse</label>
                            <input type="text" name="location" class="form-control"
                                   value="{{ old('location', $warehouse->location ?? '') }}" placeholder="Ex: Lot 123 Ankorondrano">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1"
                                       {{ old('is_active', $warehouse->is_active ?? true) ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">Dépôt actif</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-light">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
