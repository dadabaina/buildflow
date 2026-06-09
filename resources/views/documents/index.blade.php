<x-layouts.app title="Gestion Documentaire">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Documents</li>
    </x-slot>

    <!-- Header & Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-folder-open fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Gestion des Documents</h4>
                                <p class="text-muted small mb-0">Centralisez et organisez tous vos plans, contrats et pièces administratives.</p>
                            </div>
                        </div>
                        <div>
                            @can('documents.create')
                            <a href="{{ route('documents.create', request()->only('project_id')) }}" class="btn btn-primary shadow-sm px-4">
                                <i class="bx bx-upload me-1"></i>Ajouter un document
                            </a>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total Documents</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['total_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-file fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Espace utilisé</p>
                            <h4 class="mb-0 fw-bold mt-1">
                                @php
                                    $bytes = $stats['total_size'];
                                    if ($bytes >= 1048576) $fmt = round($bytes / 1048576, 1) . ' Mo';
                                    elseif ($bytes >= 1024) $fmt = round($bytes / 1024, 1) . ' Ko';
                                    else $fmt = $bytes . ' o';
                                @endphp
                                {{ $fmt }}
                            </h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-hdd fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Plans / Dessins</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['by_category']['plan'] ?? 0 }}</h4>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-map-alt fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Contrats & Devis</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ ($stats['by_category']['contrat'] ?? 0) + ($stats['by_category']['devis'] ?? 0) }}</h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-badge-check fs-3"></i>
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
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase">Rechercher</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nom du fichier..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Chantier</label>
                    <select name="project_id" class="form-select border-0 bg-light">
                        <option value="">Tous les chantiers</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase">Catégorie</label>
                    <select name="category" class="form-select border-0 bg-light">
                        <option value="">Toutes catégories</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100 fw-bold">
                        <i class="bx bx-filter-alt me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Nom du document</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Catégorie</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Chantier</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Taille</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Auteur</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-{{ $doc->isPdf() ? 'danger' : ($doc->isImage() ? 'info' : 'secondary') }} me-3">
                                        <i class="bx {{ $doc->isPdf() ? 'bxs-file-pdf' : ($doc->isImage() ? 'bxs-image' : 'bxs-file') }} fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $doc->original_name }}</div>
                                        @if($doc->notes)
                                            <small class="text-muted text-truncate d-block" style="max-width: 250px;">{{ $doc->notes }}</small>
                                        @else
                                            <small class="text-muted italic" style="font-size: 0.7rem;">Ajouté le {{ $doc->created_at->format('d/m/Y') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-secondary badge-sm text-uppercase">{{ $doc->category_libelle }}</span>
                            </td>
                            <td>
                                @if($doc->project)
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('projects.show', $doc->project) }}" class="fw-medium text-dark text-decoration-none small text-truncate" style="max-width: 150px;">{{ $doc->project->name }}</a>
                                        <small class="text-muted" style="font-size: 0.65rem;">REF: {{ $doc->project->reference }}</small>
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td><span class="text-muted small">{{ $doc->file_size_formatted }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 0.6rem;">{{ strtoupper(substr($doc->uploadedBy->name ?? '?', 0, 1)) }}</span>
                                    </div>
                                    <span class="text-dark small">{{ $doc->uploadedBy->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('documents.show', $doc) }}" class="btn btn-icon btn-sm btn-label-primary shadow-none" title="Télécharger / Voir">
                                        <i class="bx bx-download"></i>
                                    </a>
                                    @can('documents.delete')
                                    <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Supprimer ce document ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-icon btn-sm btn-label-danger shadow-none" title="Supprimer"><i class="bx bx-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-folder-open fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun document ne correspond à vos critères.</h6>
                                <p class="small text-muted">Essayez de modifier vos filtres ou <a href="{{ route('documents.create') }}">téléchargez un nouveau fichier</a>.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($documents->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $documents->links() }}
            </div>
        </div>
        @endif
    </div>

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
