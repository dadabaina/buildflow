<x-layouts.app title="Paramètres — Régions">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none opacity-50 text-dark">Paramètres</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Régions</li>
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
            <x-card title="Régions d'intervention" subtitle="Gérez les régions géographiques pour vos clients et chantiers.">
                <form method="POST" action="{{ route('settings.regions.store') }}" class="mb-4">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Ajouter une nouvelle région..." required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary px-3 shadow-sm-app"><i class="bi bi-plus-lg me-1"></i> Ajouter</button>
                        </div>
                    </div>
                </form>

                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    @foreach($regions as $region)
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3 px-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-geo-alt text-primary"></i>
                            </div>
                            <span class="fw-bold text-dark">{{ $region->code }} - {{ $region->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('settings.regions.destroy', $region) }}"
                              onsubmit="return confirm('Voulez-vous vraiment supprimer cette région ?')">
                            @csrf @method('DELETE')
                            <button class="btn-action-delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    @endforeach
                    @if($regions->isEmpty())
                        <div class="p-5 text-center text-muted">Aucune région configurée.</div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
