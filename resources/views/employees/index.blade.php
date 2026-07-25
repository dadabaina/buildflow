<x-layouts.app title="Effectif & Collaborateurs">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Employés</li>
    </x-slot>

    <!-- Header & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-group fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Gestion des Employés</h4>
                                <p class="text-muted small mb-0">Suivez vos effectifs internes et les intervenants de vos sous-traitants.</p>
                            </div>
                        </div>
                        <div>
                            @can('employees.create')
                            <button type="button" id="tour-employees-new" class="btn btn-primary shadow-sm px-4" onclick="empModalCreate()">
                                <i class="bx bx-user-plus me-1"></i>Nouvel employé
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Effectif Total</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['total_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-user fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Personnel Interne</p>
                            <h4 class="mb-0 fw-bold mt-1 text-success">{{ $stats['internal_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-shield-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Intervenants Extérieurs</p>
                            <h4 class="mb-0 fw-bold mt-1 text-info">{{ $stats['external_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-truck fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom bg-transparent py-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Rechercher</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nom, matricule..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-uppercase">Poste</label>
                    <select name="job_type_id" class="form-select border-0 bg-light">
                        <option value="">Tous les postes</option>
                        @foreach($jobTypes as $jt)
                            <option value="{{ $jt->id }}" @selected(request('job_type_id') == $jt->id)>{{ $jt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-uppercase">Région</label>
                    <select name="region_id" class="form-select border-0 bg-light">
                        <option value="">Toutes régions</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->id }}" @selected(request('region_id') == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-uppercase">Statut</label>
                    <select name="archived" class="form-select border-0 bg-light">
                        <option value="">Employés actifs</option>
                        <option value="1" @selected(request('archived'))>Archivés</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bx bx-refresh"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tour-employees-table" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Collaborateur</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Poste</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Origine</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-center">Contact</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr class="{{ $employee->trashed() ? 'bg-light bg-opacity-50' : '' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        @if($employee->photo_url)
                                            <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <span class="avatar-initial rounded-circle fw-bold bg-label-primary">{{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $employee) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $employee->full_name }}
                                        </a>
                                        <small class="text-muted font-monospace" style="font-size: 0.7rem;">MAT: {{ $employee->matricule ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach($employee->jobTypes as $jt)
                                    <span class="badge bg-label-secondary badge-sm text-uppercase">
                                        {{ $jt->name }}
                                    </span>
                                @endforeach
                                @if($employee->jobTypes->isEmpty())
                                    <span class="badge bg-label-secondary badge-sm text-uppercase">Sans poste</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->supplier)
                                    <div class="d-flex flex-column">
                                        <span class="text-dark small fw-medium">{{ $employee->supplier->name }}</span>
                                        <small class="text-muted" style="font-size: 0.65rem;">S/T</small>
                                    </div>
                                @else
                                    <span class="badge bg-label-success badge-xs">Interne</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-medium">{{ $employee->phone ?? '—' }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $employee->email ?? '—' }}</small>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($employee->trashed())
                                        <form method="POST" action="{{ route('employees.restore', $employee->id) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-icon btn-sm btn-label-success shadow-none" title="Restaurer">
                                                <i class="bx bx-undo"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('employees.show', $employee) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Détails">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        @can('employees.edit')
                                        <button type="button" class="btn btn-icon btn-sm btn-label-info shadow-none" title="Modifier"
                                            onclick='empModalEdit({{ json_encode(["id"=>$employee->id,"first_name"=>$employee->first_name,"last_name"=>$employee->last_name,"photo_url"=>$employee->photo_url,"matricule"=>$employee->matricule,"phone"=>$employee->phone,"email"=>$employee->email,"job_type_id"=>$employee->job_type_id, "job_type_ids"=>$employee->jobTypes->pluck("id"), "supplier_id"=>$employee->supplier_id,"region_id"=>$employee->region_id,"contract_type"=>$employee->contract_type,"hire_date"=>$employee->hire_date?->format("Y-m-d"),"daily_rate"=>$employee->daily_rate,"monthly_salary"=>$employee->monthly_salary,"notes"=>$employee->notes]) }})'>
                                            <i class="bx bx-edit-alt"></i>
                                        </button>
                                        @endcan
                                        @can('employees.delete')
                                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Archiver cet employé ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-icon btn-sm btn-label-danger shadow-none" title="Archiver"><i class="bx bx-archive"></i></button>
                                        </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-group fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun collaborateur trouvé.</h6>
                                <p class="small text-muted">Ajustez vos filtres ou <a href="#" onclick="empModalCreate()">ajoutez un nouvel employé</a>.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($employees->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $employees->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- Modal Employé --}}
    <div class="modal fade" id="empModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="empForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="empMethod" value="">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold text-dark" id="empModalTitle">Nouvel employé</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-12 text-center mb-2">
                                <div class="mx-auto border rounded-circle bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative"
                                     style="width: 110px; height: 110px; cursor: pointer;"
                                     onclick="document.getElementById('ePhotoInput').click()">
                                    <img id="ePhotoPreview" src="" class="w-100 h-100" style="object-fit: cover; display: none;">
                                    <div id="ePhotoPlaceholder" class="text-center p-2">
                                        <i class="bx bx-camera fs-2 opacity-50"></i>
                                        <p class="small text-muted mb-0" style="font-size: 0.65rem;">Photo</p>
                                    </div>
                                </div>
                                {{-- accept=image/* + capture=user : sur mobile, ouvre directement l'appareil photo (caméra frontale) ; sur ordinateur, ouvre le sélecteur de fichier classique. --}}
                                <input type="file" name="photo" id="ePhotoInput" class="d-none"
                                       accept="image/*" capture="user" onchange="empPhotoChange(this)">
                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">Cliquez pour choisir une photo ou la prendre avec l'appareil photo (mobile)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Prénom <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" id="eFirstName" class="form-control" required placeholder="Ex: Jean">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" id="eLastName" class="form-control" required placeholder="Ex: DUPONT">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Matricule</label>
                                <input type="text" name="matricule" id="eMatricule" class="form-control" placeholder="ID Interne">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-phone text-muted"></i></span>
                                    <input type="text" name="phone" id="ePhone" class="form-control" placeholder="+261 ...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-envelope text-muted"></i></span>
                                    <input type="email" name="email" id="eEmail" class="form-control" placeholder="email@entreprise.mg">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="bg-light p-3 rounded border border-dashed mt-2">
                                    <h6 class="text-primary fw-bold mb-3 small text-uppercase"><i class="bx bx-briefcase me-1"></i>Affectation & Contrat</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-uppercase">Postes / Fonctions (Polyvalence)</label>
                                            <select name="job_type_ids[]" id="eJobTypes" class="form-select" multiple>
                                                @foreach($jobTypes as $jt)
                                                <option value="{{ $jt->id }}">{{ $jt->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text" style="font-size: 0.65rem;">Recherchez et sélectionnez plusieurs postes si nécessaire.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Région principale</label>
                                            <select name="region_id" id="eRegion" class="form-select form-select-sm">
                                                <option value="">Sélectionner une région...</option>
                                                @foreach($regions as $region)
                                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Type de contrat</label>
                                            <select name="contract_type" id="eContractType" class="form-select form-select-sm">
                                                <option value="">—</option>
                                                <option value="cdd">CDD</option>
                                                <option value="cdi">CDI</option>
                                                <option value="interim">Intérim</option>
                                                <option value="journalier">Journalier</option>
                                                <option value="mensuel">Mensuel</option>
                                                <option value="sous_traitant">Sous-traitant</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Taux journalier</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="daily_rate" id="eDailyRate" class="form-control" step="0.01" min="0">
                                                <span class="input-group-text">Ar</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Date d'embauche</label>
                                            <input type="date" name="hire_date" id="eHireDate" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Notes / Observations</label>
                                <textarea name="notes" id="eNotes" class="form-control" rows="2" placeholder="Informations complémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bx bx-check-circle me-1"></i>Enregistrer le collaborateur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
    <script>
    let jtTomSelect;

    document.addEventListener('DOMContentLoaded', function() {
        jtTomSelect = new TomSelect('#eJobTypes', {
            plugins: ['remove_button'],
            placeholder: "Chercher des postes...",
            maxOptions: null,
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results">Aucun poste trouvé pour "' + escape(data.input) + '"</div>';
                }
            }
        });
    });

    function empPhotoChange(input) {
        const file = input.files[0];
        const preview = document.getElementById('ePhotoPreview');
        const placeholder = document.getElementById('ePhotoPlaceholder');
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
    }

    function empResetPhoto(url) {
        const input = document.getElementById('ePhotoInput');
        const preview = document.getElementById('ePhotoPreview');
        const placeholder = document.getElementById('ePhotoPlaceholder');
        input.value = '';
        if (url) {
            preview.src = url;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        } else {
            preview.src = '';
            preview.style.display = 'none';
            placeholder.style.display = 'block';
        }
    }

    function empModalCreate() {
        document.getElementById('empModalTitle').textContent = 'Créer une fiche employé';
        document.getElementById('empForm').action = '{{ route('employees.store') }}';
        document.getElementById('empMethod').value = '';
        ['eFirstName','eLastName','eMatricule','ePhone','eEmail','eDailyRate','eHireDate','eNotes'].forEach(function(id) {
            var el = document.getElementById(id); if (el) el.value = '';
        });
        empResetPhoto(null);

        // Reset multi-select
        if (jtTomSelect) {
            jtTomSelect.clear();
        } else {
            let jtSelect = document.getElementById('eJobTypes');
            Array.from(jtSelect.options).forEach(opt => opt.selected = false);
        }

        document.getElementById('eRegion').value = '';
        document.getElementById('eContractType').value = '';
        new bootstrap.Modal(document.getElementById('empModal')).show();
    }
    function empModalEdit(data) {
        document.getElementById('empModalTitle').textContent = 'Modifier la fiche employé';
        document.getElementById('empForm').action = '/employees/' + data.id;
        document.getElementById('empMethod').value = 'PATCH';
        document.getElementById('eFirstName').value    = data.first_name    || '';
        document.getElementById('eLastName').value     = data.last_name     || '';
        document.getElementById('eMatricule').value    = data.matricule     || '';
        document.getElementById('ePhone').value        = data.phone         || '';
        document.getElementById('eEmail').value        = data.email         || '';
        empResetPhoto(data.photo_url || null);

        // Handle multi-select for job types
        let selectedIds = data.job_type_ids || (data.job_type_id ? [data.job_type_id] : []);
        if (jtTomSelect) {
            jtTomSelect.setValue(selectedIds);
        } else {
            let jtSelect = document.getElementById('eJobTypes');
            Array.from(jtSelect.options).forEach(opt => {
                opt.selected = selectedIds.includes(parseInt(opt.value));
            });
        }

        document.getElementById('eRegion').value       = data.region_id     || '';
        document.getElementById('eContractType').value = data.contract_type || '';
        document.getElementById('eDailyRate').value    = data.daily_rate    || '';
        document.getElementById('eHireDate').value     = data.hire_date     || '';
        document.getElementById('eNotes').value        = data.notes         || '';
        new bootstrap.Modal(document.getElementById('empModal')).show();
    }
    </script>
    @endpush

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
        .bg-label-warning { background-color: #fff2e2 !important; color: #ffab00 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
        .bg-label-secondary { background-color: #ebeef0 !important; color: #8592a3 !important; }
        .badge-xs { padding: 0.2rem 0.4rem; font-size: 0.6rem; }

        /* Tom Select Customization */
        .ts-control {
            border: 1px solid #d9dee3 !important;
            border-radius: 0.375rem !important;
            box-shadow: none !important;
            padding: 0.4375rem 0.875rem !important;
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
            border: none !important;
            padding: 0.5rem !important;
        }
        .ts-dropdown .active {
            background-color: #696cff !important;
            color: #fff !important;
            border-radius: 0.25rem !important;
        }
    </style>
    @endpush
</x-layouts.app>
