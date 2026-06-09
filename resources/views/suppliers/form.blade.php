<x-layouts.app :title="isset($supplier) ? 'Modifier fournisseur' : 'Nouveau fournisseur'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none opacity-50 text-dark">Fournisseurs</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($supplier) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-truck me-2"></i>
                        {{ isset($supplier) ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($supplier) ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
                        @csrf
                        @isset($supplier) @method('PATCH') @endisset

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $supplier->name ?? '') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="fournisseur" {{ old('type', $supplier->type ?? '') === 'fournisseur' ? 'selected' : '' }}>Fournisseur</option>
                                    <option value="sous_traitant" {{ old('type', $supplier->type ?? '') === 'sous_traitant' ? 'selected' : '' }}>Sous-traitant</option>
                                    <option value="les_deux" {{ old('type', $supplier->type ?? '') === 'les_deux' ? 'selected' : '' }}>Fournisseur + Sous-traitant</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone', $supplier->phone ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $supplier->email ?? '') }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="address" class="form-control"
                                       value="{{ old('address', $supplier->address ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">NIF</label>
                                <input type="text" name="nif" class="form-control"
                                       value="{{ old('nif', $supplier->nif ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">STAT</label>
                                <input type="text" name="stat" class="form-control"
                                       value="{{ old('stat', $supplier->stat ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">RCS</label>
                                <input type="text" name="rcs" class="form-control"
                                       value="{{ old('rcs', $supplier->rcs ?? '') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($supplier) ? 'Enregistrer' : 'Créer le fournisseur' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
