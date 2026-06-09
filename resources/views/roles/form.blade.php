<x-layouts.app :title="isset($role) ? 'Modifier le Rôle' : 'Créer un Rôle'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-decoration-none opacity-50 text-dark">Rôles</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($role) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <form action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}" method="POST">
        @csrf
        @if(isset($role)) @method('PUT') @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm-app mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Informations générales</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nom du rôle <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control shadow-none @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $role->name ?? '') }}" placeholder="Ex: Magasinier, Chef de Chantier Junior..." required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <p class="text-muted small">Le nom doit être explicite pour faciliter l'attribution aux utilisateurs.</p>
                        <hr class="my-4 opacity-25">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i> {{ isset($role) ? 'Enregistrer les modifications' : 'Créer le rôle' }}
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-light text-secondary">Annuler</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm-app">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h5 class="fw-bold mb-0">Permissions & Accès</h5>
                        <p class="text-secondary small mb-0">Cochez les modules et actions autorisés pour ce rôle.</p>
                    </div>
                    <div class="card-body p-4">
                        @foreach($permissions as $module => $modulePermissions)
                        <div class="mb-4">
                            <h6 class="text-primary fw-bold text-uppercase small mb-3 border-bottom pb-2">
                                <i class="bi bi-folder2-open me-2"></i> Module : {{ ucfirst($module) }}
                            </h6>
                            <div class="row g-3">
                                @foreach($modulePermissions as $permission)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check form-switch custom-switch">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                               value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                               {{ (isset($rolePermissions) && in_array($permission->name, $rolePermissions)) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                            @php
                                                $action = explode('.', $permission->name)[1] ?? $permission->name;
                                                $labels = [
                                                    'view' => 'Consulter',
                                                    'create' => 'Créer / Ajouter',
                                                    'edit' => 'Modifier',
                                                    'delete' => 'Supprimer',
                                                    'send' => 'Envoyer',
                                                    'accept' => 'Accepter',
                                                    'validate' => 'Valider',
                                                    'reject' => 'Rejeter',
                                                    'change_status' => 'Changer statut'
                                                ];
                                            @endphp
                                            {{ $labels[$action] ?? ucfirst(str_replace('_', ' ', $action)) }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>

    <style>
        .custom-switch .form-check-input { width: 2.5em; height: 1.25em; cursor: pointer; }
        .custom-switch .form-check-label { cursor: pointer; padding-left: 0.5rem; padding-top: 0.1rem; }
    </style>
</x-layouts.app>
