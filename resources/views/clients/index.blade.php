<x-layouts.app title="Répertoire Clients">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Clients</li>
    </x-slot>

    <!-- Header & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-user-voice fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Gestion des Clients</h4>
                                <p class="text-muted small mb-0">Pilotez votre relation client et suivez vos opportunités commerciales.</p>
                            </div>
                        </div>
                        <div>
                            @can('clients.create')
                            <button type="button" id="tour-clients-new" class="btn btn-primary shadow-sm px-4" onclick="clientModalCreate()">
                                <i class="bx bx-user-plus me-1"></i>Nouveau client
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Clients</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['total_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-group fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Particuliers</p>
                            <h4 class="mb-0 fw-bold mt-1 text-info">{{ $stats['by_type']['particulier'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-user fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Entreprises</p>
                            <h4 class="mb-0 fw-bold mt-1 text-success">{{ $stats['by_type']['entreprise'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Administrations</p>
                            <h4 class="mb-0 fw-bold mt-1 text-warning">{{ $stats['by_type']['administration'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-landmark fs-3"></i>
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
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nom, email, tél..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-uppercase">Type</label>
                    <select name="type" class="form-select border-0 bg-light">
                        <option value="">Tous les types</option>
                        <option value="particulier" @selected(request('type') === 'particulier')>Particulier</option>
                        <option value="entreprise" @selected(request('type') === 'entreprise')>Entreprise</option>
                        <option value="administration" @selected(request('type') === 'administration')>Administration</option>
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
                        <option value="">Clients actifs</option>
                        <option value="1" @selected(request('archived'))>Clients archivés</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bx bx-refresh"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tour-clients-table" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Client</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Type</th>
                            <th class="py-3 border-0 small text-uppercase text-muted text-center">Région</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Contact</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                        $typeConfig = [
                            'particulier' => ['label' => 'Particulier', 'class' => 'bg-label-info', 'icon' => 'bx-user'],
                            'entreprise' => ['label' => 'Entreprise', 'class' => 'bg-label-primary', 'icon' => 'bx-buildings'],
                            'administration' => ['label' => 'Admin.', 'class' => 'bg-label-warning', 'icon' => 'bx-landmark']
                        ];
                        @endphp
                        @forelse($clients as $client)
                        @php $cfg = $typeConfig[$client->type] ?? ['label' => $client->type, 'class' => 'bg-label-secondary', 'icon' => 'bx-user']; @endphp
                        <tr class="{{ $client->trashed() ? 'bg-light bg-opacity-50' : '' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm {{ $cfg['class'] }} me-3">
                                        <i class="bx {{ $cfg['icon'] }} fs-4"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('clients.show', $client) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $client->name }}
                                        </a>
                                        <small class="text-muted font-monospace" style="font-size: 0.7rem;">ID: #{{ str_pad($client->id, 4, '0', STR_PAD_LEFT) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $cfg['class'] }} badge-sm text-uppercase">{{ $cfg['label'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted small fw-medium">{{ $client->region?->name ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark small fw-medium">{{ $client->phone ?? '—' }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $client->email ?? '—' }}</small>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($client->trashed())
                                        <form method="POST" action="{{ route('clients.restore', $client->id) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-icon btn-sm btn-label-success shadow-none" title="Restaurer">
                                                <i class="bx bx-undo"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Détails">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        @can('clients.edit')
                                        <button type="button" class="btn btn-icon btn-sm btn-label-info shadow-none" title="Modifier"
                                            onclick='clientModalEdit({{ json_encode(["id"=>$client->id,"name"=>$client->name,"type"=>$client->type,"phone"=>$client->phone,"email"=>$client->email,"address"=>$client->address,"city"=>$client->city,"region_id"=>$client->region_id,"nif"=>$client->nif,"stat"=>$client->stat,"rcs"=>$client->rcs,"notes"=>$client->notes]) }})'>
                                            <i class="bx bx-edit-alt"></i>
                                        </button>
                                        @endcan
                                        @can('clients.delete')
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Archiver ce client ?')">
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
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-user-voice fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun client trouvé.</h6>
                                <p class="small text-muted">Ajustez vos filtres ou <a href="#" onclick="clientModalCreate()">ajoutez un nouveau client</a>.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($clients->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $clients->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- Modal Client --}}
    <div class="modal fade" id="clientModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="clientForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="clientMethod" value="">
                    <div class="modal-header bg-light border-bottom">
                        <h5 class="modal-title fw-bold text-dark" id="clientModalTitle">Nouveau client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-uppercase">Nom complet / Raison sociale <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="cName" class="form-control" required maxlength="191" placeholder="Ex: Jean Dupont ou Acme Corp">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Type de client <span class="text-danger">*</span></label>
                                <select name="type" id="cType" class="form-select" required>
                                    <option value="">— Sélectionner —</option>
                                    <option value="particulier">Particulier</option>
                                    <option value="entreprise">Entreprise</option>
                                    <option value="administration">Administration</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-phone text-muted"></i></span>
                                    <input type="text" name="phone" id="cPhone" class="form-control" placeholder="Ex: +261 34 00 000 00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Email professionnel</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-envelope text-muted"></i></span>
                                    <input type="email" name="email" id="cEmail" class="form-control" placeholder="client@exemple.com">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Adresse physique</label>
                                <input type="text" name="address" id="cAddress" class="form-control" placeholder="Numéro, rue, quartier...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Ville</label>
                                <input type="text" name="city" id="cCity" class="form-control" placeholder="Ex: Antananarivo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Région</label>
                                <select name="region_id" id="cRegion" class="form-select">
                                    <option value="">— Sélectionner —</option>
                                    @foreach($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <div class="bg-light p-3 rounded border border-dashed mt-2">
                                    <h6 class="fw-bold mb-3 small text-uppercase text-muted"><i class="bx bx-id-card me-1"></i>Informations Fiscales (Optionnel)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small">NIF</label>
                                            <input type="text" name="nif" id="cNif" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">STAT</label>
                                            <input type="text" name="stat" id="cStat" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">RCS</label>
                                            <input type="text" name="rcs" id="cRcs" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Notes internes</label>
                                <textarea name="notes" id="cNotes" class="form-control" rows="2" placeholder="Informations complémentaires sur le client..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bx bx-check-circle me-1"></i>Enregistrer le client
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function clientModalCreate() {
        document.getElementById('clientModalTitle').textContent = 'Créer un nouveau client';
        document.getElementById('clientForm').action = '{{ route('clients.store') }}';
        document.getElementById('clientMethod').value = '';
        ['cName','cPhone','cEmail','cAddress','cCity','cNif','cStat','cRcs','cNotes'].forEach(function(id) {
            var el = document.getElementById(id); if (el) el.value = '';
        });
        document.getElementById('cType').value = '';
        document.getElementById('cRegion').value = '';
        new bootstrap.Modal(document.getElementById('clientModal')).show();
    }
    function clientModalEdit(data) {
        document.getElementById('clientModalTitle').textContent = 'Modifier les informations';
        document.getElementById('clientForm').action = '/clients/' + data.id;
        document.getElementById('clientMethod').value = 'PATCH';
        document.getElementById('cName').value    = data.name    || '';
        document.getElementById('cType').value    = data.type    || '';
        document.getElementById('cPhone').value   = data.phone   || '';
        document.getElementById('cEmail').value   = data.email   || '';
        document.getElementById('cAddress').value = data.address || '';
        document.getElementById('cCity').value    = data.city    || '';
        document.getElementById('cRegion').value  = data.region_id || '';
        document.getElementById('cNif').value     = data.nif     || '';
        document.getElementById('cStat').value    = data.stat    || '';
        document.getElementById('cRcs').value     = data.rcs     || '';
        document.getElementById('cNotes').value   = data.notes   || '';
        new bootstrap.Modal(document.getElementById('clientModal')).show();
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
        .badge-xs { padding: 0.2rem 0.4rem; font-size: 0.6rem; }
    </style>
    @endpush
</x-layouts.app>
