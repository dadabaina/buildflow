<x-layouts.app title="Paramètres — Catégories matériaux">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none opacity-50 text-dark">Paramètres</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Catégories matériaux</li>
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
            <x-card title="Catégories de matériaux" subtitle="Organisez votre catalogue matériaux par catégories.">
                <form method="POST" action="{{ route('settings.material_categories.store') }}" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-medium">Nom de la catégorie <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Ex: Ciment, Ferraille, Bois..." required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-medium">Couleur</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#0d6efd">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary shadow-sm-app"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
                        </div>
                    </div>
                </form>

                <div class="row g-3">
                    @forelse($materialCategories as $mc)
                    <div class="col-md-6">
                        <div class="p-3 bg-white border rounded-4 d-flex justify-content-between align-items-center shadow-sm-app">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle" style="width:16px;height:16px;background:{{ $mc->color ?? '#6c757d' }}"></div>
                                <span class="fw-bold text-dark">{{ $mc->name }}</span>
                                <span class="badge bg-light text-muted border">{{ $mc->materials()->count() }} mat.</span>
                            </div>
                            <form method="POST" action="{{ route('settings.material_categories.destroy', $mc) }}"
                                  onsubmit="return confirm('Supprimer cette catégorie ?')">
                                @csrf @method('DELETE')
                                <button class="btn-action-delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-4">Aucune catégorie de matériaux configurée.</div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
