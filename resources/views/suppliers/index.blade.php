<x-layouts.app title="Répertoire Fournisseurs">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Fournisseurs</li>
    </x-slot>

    <!-- Header & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-truck fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Gestion des Fournisseurs</h4>
                                <p class="text-muted small mb-0">Centralisez vos partenaires, sous-traitants et prestataires logistiques.</p>
                            </div>
                        </div>
                        <div>
                            @can('suppliers.create')
                            <button type="button" class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#supplierModal" onclick="openCreate()">
                                <i class="bx bx-plus me-1"></i>Nouveau fournisseur
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
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Partenaires</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['total_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-buildings fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Fournisseurs</p>
                            <h4 class="mb-0 fw-bold mt-1 text-info">{{ $stats['by_type']['fournisseur'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-package fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Sous-traitants</p>
                            <h4 class="mb-0 fw-bold mt-1 text-warning">{{ $stats['by_type']['sous_traitant'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-hard-hat fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Hybrides (F+ST)</p>
                            <h4 class="mb-0 fw-bold mt-1 text-success">{{ $stats['by_type']['les_deux'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-refresh fs-3"></i>
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
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-uppercase">Rechercher</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nom, email, NIF..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Type d'activité</label>
                    <select name="type" class="form-select border-0 bg-light">
                        <option value="">Tous les types</option>
                        <option value="fournisseur" @selected(request('type') === 'fournisseur')>Fournisseur uniquement</option>
                        <option value="sous_traitant" @selected(request('type') === 'sous_traitant')>Sous-traitant uniquement</option>
                        <option value="les_deux" @selected(request('type') === 'les_deux')>Hybride (Fournisseur + S/T)</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bx bx-refresh"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Partenaire</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Activité</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Contact</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Identification</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $typeConfig = [
                            'fournisseur' => ['label' => 'Fournisseur', 'class' => 'bg-label-info', 'icon' => 'bx-package'],
                            'sous_traitant' => ['label' => 'Sous-traitant', 'class' => 'bg-label-warning', 'icon' => 'bx-hard-hat'],
                            'les_deux' => ['label' => 'Hybride', 'class' => 'bg-label-primary', 'icon' => 'bx-refresh']
                        ];
                        @endphp
                        @forelse($suppliers as $supplier)
                        @php $cfg = $typeConfig[$supplier->type] ?? ['label' => $supplier->type, 'class' => 'bg-label-secondary', 'icon' => 'bx-buildings']; @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm {{ $cfg['class'] }} me-3">
                                        <i class="bx {{ $cfg['icon'] }} fs-4"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $supplier->name }}
                                        </a>
                                        <small class="text-muted font-monospace" style="font-size: 0.7rem;">ID: #{{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $cfg['class'] }} badge-sm text-uppercase">{{ $cfg['label'] }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-medium">{{ $supplier->contact_name ?? '—' }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $supplier->phone ?? '—' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="text-muted small fw-bold">NIF:</span> <span class="font-monospace small">{{ $supplier->nif ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Détails">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @can('suppliers.edit')
                                    <button type="button" class="btn btn-icon btn-sm btn-label-info shadow-none" title="Modifier"
                                            onclick='openEdit({{ json_encode(["id"=>$supplier->id,"name"=>$supplier->name,"type"=>$supplier->type,"contact_name"=>$supplier->contact_name,"phone"=>$supplier->phone,"email"=>$supplier->email,"address"=>$supplier->address,"city"=>$supplier->city,"nif"=>$supplier->nif,"notes"=>$supplier->notes,"is_active"=>$supplier->is_active]) }})'>
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    @endcan
                                    @can('suppliers.delete')
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Supprimer ce partenaire ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-icon btn-sm btn-label-danger shadow-none" title="Supprimer"><i class="bx bx-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-truck fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun partenaire trouvé.</h6>
                                <p class="small text-muted">Ajustez vos filtres ou <a href="#" data-bs-toggle="modal" data-bs-target="#supplierModal" onclick="openCreate()">ajoutez un nouveau fournisseur</a>.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($suppliers->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $suppliers->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- Modal Fournisseur --}}
    <div class="modal fade" id="supplierModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="supplierForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="supplierMethod" value="">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold text-dark" id="supplierModalTitle">Nouveau fournisseur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-uppercase">Nom de l'entreprise <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="sName" class="form-control" required maxlength="191" placeholder="Ex: SOGEA, COLAS, etc.">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Type <span class="text-danger">*</span></label>
                                <select name="type" id="sType" class="form-select" required>
                                    <option value="">— Sélectionner —</option>
                                    <option value="fournisseur">Fournisseur</option>
                                    <option value="sous_traitant">Sous-traitant</option>
                                    <option value="les_deux">Hybride (F+ST)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Nom du contact</label>
                                <input type="text" name="contact_name" id="sContactName" class="form-control" placeholder="Responsable commercial...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-phone text-muted"></i></span>
                                    <input type="text" name="phone" id="sPhone" class="form-control" placeholder="+261 ...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-envelope text-muted"></i></span>
                                    <input type="email" name="email" id="sEmail" class="form-control" placeholder="contact@fournisseur.mg">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Ville</label>
                                <input type="text" name="city" id="sCity" class="form-control" placeholder="Ex: Antananarivo">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Adresse</label>
                                <input type="text" name="address" id="sAddress" class="form-control" placeholder="Rue, quartier...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">NIF</label>
                                <input type="text" name="nif" id="sNif" class="form-control" placeholder="Identifiant fiscal">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Notes & Observations</label>
                                <textarea name="notes" id="sNotes" class="form-control" rows="3" placeholder="Informations complémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="supplierSubmitBtn">
                            <i class="bx bx-check-circle me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function openCreate() {
        document.getElementById('supplierModalTitle').textContent = 'Nouveau partenaire';
        document.getElementById('supplierForm').action = '{{ route('suppliers.store') }}';
        document.getElementById('supplierMethod').value = '';
        ['sName','sType','sContactName','sPhone','sEmail','sCity','sAddress','sNif','sNotes'].forEach(id => {
            var el = document.getElementById(id); if (el) el.value = '';
        });
        document.getElementById('supplierSubmitBtn').textContent = 'Créer le partenaire';
    }
    function openEdit(data) {
        document.getElementById('supplierModalTitle').textContent = 'Modifier le partenaire';
        document.getElementById('supplierForm').action = '/suppliers/' + data.id;
        document.getElementById('supplierMethod').value = 'PATCH';
        document.getElementById('sName').value = data.name ?? '';
        document.getElementById('sType').value = data.type ?? '';
        document.getElementById('sContactName').value = data.contact_name ?? '';
        document.getElementById('sPhone').value = data.phone ?? '';
        document.getElementById('sEmail').value = data.email ?? '';
        document.getElementById('sCity').value = data.city ?? '';
        document.getElementById('sAddress').value = data.address ?? '';
        document.getElementById('sNif').value = data.nif ?? '';
        document.getElementById('sNotes').value = data.notes ?? '';
        document.getElementById('supplierSubmitBtn').textContent = 'Mettre à jour';
        new bootstrap.Modal(document.getElementById('supplierModal')).show();
    }
    </script>
    @endpush

    @push('styles')
    <style>
        .bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
        .bg-label-warning { background-color: #fff2e2 !important; color: #ffab00 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
        .bg-label-secondary { background-color: #ebeef0 !important; color: #8592a3 !important; }
    </style>
    @endpush
</x-layouts.app>
