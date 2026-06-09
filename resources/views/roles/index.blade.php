<x-layouts.app title="Gestion des Rôles">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Rôles & Permissions</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Rôles Personnalisés</h3>
            <p class="text-secondary small mb-0">Définissez des niveaux d'accès spécifiques pour vos différents collaborateurs.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-people"></i>
                <span>Gérer les utilisateurs</span>
            </a>
            <a href="{{ route('roles.create') }}" class="btn btn-primary shadow-app d-flex align-items-center gap-2">
                <i class="bi bi-shield-plus fs-5"></i>
                <span>Créer un rôle</span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm-app overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nom du Rôle</th>
                        <th>Permissions associées</th>
                        <th>Utilisateurs</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($roles as $role)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 38px; height: 38px;">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <div class="fw-bold text-dark">
                                    {{ $role->name }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $role->permissions->count() }} permissions configurées</span>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-light text-dark border px-3">{{ $role->users->count() }} utilisateurs</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('roles.edit', $role) }}" class="btn-action-edit" title="Modifier les permissions">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($role->users->isEmpty())
                                <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                      onsubmit="return confirm('Supprimer ce rôle définitivement ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-action-delete" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-shield-slash fs-1 opacity-25 d-block mb-3"></i>
                                <h5 class="text-muted">Aucun rôle personnalisé créé</h5>
                                <p class="small text-secondary">Commencez par créer un rôle pour définir des accès sur mesure.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
