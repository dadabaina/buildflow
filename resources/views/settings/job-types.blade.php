<x-layouts.app title="Paramètres — Postes & Fonctions">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none opacity-50 text-dark">Paramètres</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Postes & Fonctions</li>
    </x-slot>

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Paramètres</h3>
        <p class="text-secondary small">Configurez votre entreprise et personnalisez votre espace de travail.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-3">
            @include('settings._nav')
        </div>

        <div class="col-lg-9">
            <!-- Catégories de Postes -->
            <x-card title="Catégories de Postes" subtitle="Organisez vos postes par catégories (ex: Études, Travaux...)" class="mb-4">
                <form method="POST" action="{{ route('settings.job_categories.store') }}" class="mb-4">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Ex: Études et conception..." required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary px-3 shadow-sm-app"><i class="bi bi-plus-lg me-1"></i> Ajouter</button>
                        </div>
                    </div>
                </form>

                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    @foreach($jobCategories as $category)
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3 px-4 hov-light">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-folder2-open text-primary"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block">{{ $category->name }}</span>
                                <span class="badge bg-light text-primary border small">{{ $category->job_types_count }} métiers</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('settings.job_categories.destroy', $category) }}"
                              onsubmit="return confirm('Supprimer cette catégorie ? Cela affectera les postes liés.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-link text-danger p-0 border-0" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    @endforeach
                    @if($jobCategories->isEmpty())
                        <div class="p-4 text-center text-muted small">Aucune catégorie configurée.</div>
                    @endif
                </div>
            </x-card>

            <!-- Postes & Fonctions -->
            <x-card title="Postes & Fonctions" subtitle="Définissez les rôles professionnels pour vos employés.">
                <div class="mb-4 p-3 border rounded-4 bg-light bg-opacity-50">
                    <form method="POST" action="{{ route('settings.job_types.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Nom du poste</label>
                                <input type="text" name="name" class="form-control" placeholder="Ex: Chef de Chantier..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Métier (facultatif)</label>
                                <input type="text" name="metiers" class="form-control" placeholder="Ex: Maçonnerie, Électricité...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Catégorie</label>
                                <select name="job_category_id" class="form-select">
                                    <option value="">Choisir une catégorie</option>
                                    @foreach($jobCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button class="btn btn-primary px-4 shadow-sm-app"><i class="bi bi-plus-lg me-1"></i> Ajouter le poste</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('settings.job_types.index') }}" id="filterForm">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-primary-subtle"><i class="bi bi-filter text-primary"></i></span>
                                <select name="category_id" class="form-select border-primary-subtle" onchange="this.form.submit()">
                                    <option value="">Toutes les catégories</option>
                                    @foreach($jobCategories as $cat)
                                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    @php $currentCat = null; @endphp
                    @foreach($jobTypes as $jt)
                        @if(!$categoryId && $jt->job_category_id !== $currentCat)
                            <div class="bg-light p-2 px-4 border-bottom small fw-bold text-uppercase text-primary tracking-wider" style="letter-spacing: 0.05em; font-size: 0.75rem;">
                                <i class="bi bi-folder2-open me-2"></i>{{ $jt->category ? $jt->category->name : 'Sans catégorie' }}
                            </div>
                            @php $currentCat = $jt->job_category_id; @endphp
                        @endif

                        <div class="list-group-item d-flex justify-content-between align-items-start p-3 px-4 hov-light border-bottom">
                            <div class="d-flex align-items-start flex-grow-1 overflow-hidden">
                                <div class="bg-white border rounded-circle p-2 me-3 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-workspace text-primary"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <span class="fw-bold text-dark d-block mb-1">{{ $jt->name }}</span>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        @if($jt->category)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                <i class="bi bi-tag-fill small me-1"></i>{{ $jt->category->name }}
                                            </span>
                                        @endif
                                        @if($jt->employees_count > 0)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                                <i class="bi bi-people-fill small me-1"></i>{{ $jt->employees_count }} employés
                                            </span>
                                        @endif
                                        @if($jt->metiers)
                                            <div class="text-secondary small d-flex align-items-start">
                                                <i class="bi bi-tools small me-1 mt-1"></i>
                                                <span style="word-break: break-word;">{{ $jt->metiers }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3 flex-shrink-0">
                                <form method="POST" action="{{ route('settings.job_types.destroy', $jt) }}"
                                      onsubmit="return confirm('Supprimer ce poste ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-light btn-sm text-danger border shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                                            style="width: 32px; height: 32px;" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    @if($jobTypes->isEmpty())
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-search d-block fs-2 mb-2 opacity-25"></i>
                            Aucun poste trouvé pour cette sélection.
                        </div>
                    @endif
                </div>

                <x-slot name="footer">
                    <div class="d-flex justify-content-center">
                        {{ $jobTypes->links() }}
                    </div>
                </x-slot>
            </x-card>
        </div>
    </div>
</x-layouts.app>
