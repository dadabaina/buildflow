<x-layouts.app title="Ajouter un document">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none opacity-50 text-dark">Documents</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Ajouter</li>
    </x-slot>

    <x-card title="Ajouter un document" icon="bi bi-upload">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Chantier <span class="text-muted small">(facultatif)</span></label>
                    <select name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                        <option value="">— Aucun chantier —</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ (old('project_id', $selected) == $proj->id) ? 'selected' : '' }}>{{ $proj->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Catégorie <span class="text-danger">*</span></label>
                    <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', 'autre') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Fichier <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                    <div class="form-text text-muted">Formats acceptés : PDF, Word, Excel, Image, ZIP — Max 20 Mo</div>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Notes <span class="text-muted small">(facultatif)</span></label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="500">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end mt-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-light border px-4">Annuler</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm-app">
                        <i class="bi bi-upload me-2"></i>Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </x-card>
</x-layouts.app>
