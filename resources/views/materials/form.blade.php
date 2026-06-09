<x-layouts.app :title="isset($material) ? 'Modifier '.$material->name : 'Nouveau matériau'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('materials.index') }}" class="text-decoration-none opacity-50 text-dark">Matériaux</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($material) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-box me-2"></i>
                        {{ isset($material) ? 'Modifier le matériau' : 'Nouveau matériau' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($material) ? route('materials.update', $material) : route('materials.store') }}">
                        @csrf
                        @isset($material) @method('PATCH') @endisset

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $material->name ?? '') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Référence</label>
                                <input type="text" name="reference" class="form-control"
                                       value="{{ old('reference', $material->reference ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unité <span class="text-danger">*</span></label>
                                <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="">Choisir une unité</option>
                                    @foreach($unitTypes as $ut)
                                        <option value="{{ $ut->symbol }}"
                                            {{ old('unit', $material->unit ?? '') == $ut->symbol ? 'selected' : '' }}>
                                            {{ $ut->name }} ({{ $ut->symbol }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Catégorie</label>
                                <select name="material_category_id" class="form-select">
                                    <option value="">Aucune catégorie</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('material_category_id', $material->material_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description', $material->description ?? '') }}</textarea>
                            </div>

                            @unless(isset($material))
                            <div class="col-12">
                                <hr class="my-1">
                                <p class="text-muted small mb-2">Prix initial (optionnel)</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix unitaire (MGA)</label>
                                <input type="number" name="unit_price" class="form-control"
                                       step="1" min="0" value="{{ old('unit_price') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Région (prix)</label>
                                <input type="text" name="region_id" class="form-control d-none">
                                <p class="form-text text-muted">Laisser vide = prix général</p>
                            </div>
                            @endunless
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($material) ? 'Enregistrer' : 'Créer le matériau' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
