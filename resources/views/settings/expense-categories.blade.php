<x-layouts.app title="Paramètres — Catégories de dépenses">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none opacity-50 text-dark">Paramètres</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Catégories dépenses</li>
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
            <x-card title="Catégories de dépenses" subtitle="Organisez vos dépenses par catégories personnalisées.">
                <form method="POST" action="{{ route('settings.expense_categories.store') }}" class="mb-4">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Ex: Matériaux, Main d'œuvre..." required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary px-3 shadow-sm-app"><i class="bi bi-plus-lg me-1"></i> Ajouter</button>
                        </div>
                    </div>
                </form>

                <div class="row g-3">
                    @foreach($categories as $cat)
                    <div class="col-md-6">
                        <div class="p-3 bg-white border rounded-4 d-flex justify-content-between align-items-center shadow-sm-app">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-tag-fill"></i>
                                </div>
                                <span class="fw-bold text-dark">{{ $cat->name }}</span>
                            </div>
                            <form method="POST" action="{{ route('settings.expense_categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Supprimer cette catégorie ?')">
                                @csrf @method('DELETE')
                                <button class="btn-action-delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                    @if($categories->isEmpty())
                        <div class="col-12 text-center text-muted py-4">Aucune catégorie configurée.</div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
