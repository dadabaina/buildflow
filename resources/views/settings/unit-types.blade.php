<x-layouts.app title="Paramètres — Unités de mesure">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none opacity-50 text-dark">Paramètres</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Unités de mesure</li>
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
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <x-card title="Unités de mesure" subtitle="Gérez les unités utilisées pour vos matériaux et devis.">
                <form method="POST" action="{{ route('settings.unit_types.store') }}" class="mb-4">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="name" class="form-control" placeholder="Nom (ex: Mètre cube)" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="symbol" class="form-control" placeholder="Symbole (m³)">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary px-3 shadow-sm-app"><i class="bi bi-plus-lg me-1"></i> Ajouter</button>
                        </div>
                    </div>
                </form>

                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    @foreach($unitTypes as $ut)
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3 px-4 hov-light">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-3 p-2 me-3 d-flex align-items-center justify-content-center fw-bold text-primary" style="width: 40px; height: 36px; font-size: 0.8rem;">
                                {{ $ut->symbol ?? '??' }}
                            </div>
                            <span class="fw-bold text-dark">{{ $ut->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('settings.unit_types.destroy', $ut) }}"
                              onsubmit="return confirm('Supprimer cette unité ?')">
                            @csrf @method('DELETE')
                            <button class="btn-action-delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    @endforeach
                    @if($unitTypes->isEmpty())
                        <div class="p-5 text-center text-muted">Aucune unité configurée.</div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
