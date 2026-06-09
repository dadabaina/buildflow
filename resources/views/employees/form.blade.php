<x-layouts.app :title="isset($employee) ? 'Modifier employé' : 'Nouvel employé'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-decoration-none opacity-50 text-dark">Employés</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($employee) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-person-plus me-2"></i>
                        {{ isset($employee) ? 'Modifier l\'employé' : 'Nouvel employé' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($employee) ? route('employees.update', $employee) : route('employees.store') }}">
                        @csrf
                        @isset($employee) @method('PATCH') @endisset

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" name="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $employee->first_name ?? '') }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $employee->last_name ?? '') }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Matricule</label>
                                <input type="text" name="matricule" class="form-control"
                                       value="{{ old('matricule', $employee->matricule ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone', $employee->phone ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', $employee->email ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Postes / Fonctions (Polyvalence)</label>
                                <select name="job_type_ids[]" id="job_type_ids" class="form-select @error('job_type_ids') is-invalid @enderror" multiple>
                                    @foreach($jobTypes as $jt)
                                    <option value="{{ $jt->id }}"
                                        {{ in_array($jt->id, old('job_type_ids', isset($employee) ? $employee->jobTypes->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                                        {{ $jt->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text" style="font-size: 0.75rem;">Sélectionnez un ou plusieurs postes occupés par ce collaborateur.</div>
                                @error('job_type_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control {
        padding: 0.4375rem 0.875rem !important;
        border: 1px solid #d9dee3 !important;
        border-radius: 0.375rem !important;
        box-shadow: none !important;
    }
    .ts-wrapper.multi .ts-control > div {
        background: #696cff !important;
        color: #fff !important;
        border-radius: 4px !important;
        padding: 2px 10px !important;
        margin: 2px 4px 2px 0 !important;
    }
    .ts-wrapper.multi.plugin-remove_button .item .remove {
        border-left: 1px solid rgba(255,255,255,0.3) !important;
    }
    .ts-dropdown {
        border-radius: 0.375rem !important;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#job_type_ids', {
            plugins: ['remove_button'],
            placeholder: "Chercher un poste...",
            maxOptions: null,
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results">Aucun poste trouvé pour "' + escape(data.input) + '"</div>';
                }
            }
        });
    });
</script>
@endpush

                            <div class="col-md-6">
                                <label class="form-label">Fournisseur / Sous-traitant</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">— Interne —</option>
                                    @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}"
                                        {{ old('supplier_id', $employee->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Région</label>
                                <select name="region_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($regions as $region)
                                    <option value="{{ $region->id }}"
                                        {{ old('region_id', $employee->region_id ?? '') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date d'entrée</label>
                                <input type="date" name="hire_date" class="form-control"
                                       value="{{ old('hire_date', isset($employee) ? $employee->hire_date?->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type de contrat</label>
                                <select name="contract_type" class="form-select @error('contract_type') is-invalid @enderror">
                                    <option value="cdd" {{ old('contract_type', $employee->contract_type ?? 'cdd') === 'cdd' ? 'selected' : '' }}>CDD</option>
                                    <option value="cdi" {{ old('contract_type', $employee->contract_type ?? '') === 'cdi' ? 'selected' : '' }}>CDI</option>
                                    <option value="interim" {{ old('contract_type', $employee->contract_type ?? '') === 'interim' ? 'selected' : '' }}>Intérim</option>
                                    <option value="journalier" {{ old('contract_type', $employee->contract_type ?? '') === 'journalier' ? 'selected' : '' }}>Journalier</option>
                                    <option value="mensuel" {{ old('contract_type', $employee->contract_type ?? '') === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                                    <option value="sous_traitant" {{ old('contract_type', $employee->contract_type ?? '') === 'sous_traitant' ? 'selected' : '' }}>Sous-traitant</option>
                                </select>
                                @error('contract_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Salaire journalier (MGA)</label>
                                <input type="number" name="daily_rate" class="form-control" step="0.01"
                                       value="{{ old('daily_rate', $employee->daily_rate ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Salaire mensuel (MGA)</label>
                                <input type="number" name="monthly_salary" class="form-control" step="0.01"
                                       value="{{ old('monthly_salary', $employee->monthly_salary ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">CIN</label>
                                <input type="text" name="cin" class="form-control"
                                       value="{{ old('cin', $employee->cin ?? '') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $employee->notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($employee) ? 'Enregistrer' : 'Créer l\'employé' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
