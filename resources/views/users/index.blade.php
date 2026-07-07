<x-layouts.app title="Utilisateurs Système">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Utilisateurs</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Comptes Utilisateurs</h3>
            <p class="text-secondary small mb-0">Gérez les accès et les permissions de vos collaborateurs sur la plateforme.</p>
        </div>
        @can('users.create')
        <a href="{{ route('users.create') }}" id="tour-users-new" class="btn btn-primary shadow-app d-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill fs-5"></i>
            <span>Créer un compte</span>
        </a>
        @endcan
    </div>

    <div class="card border-0 shadow-sm-app overflow-hidden">
        <div class="table-responsive">
            <table id="tour-users-table" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Utilisateur</th>
                        <th>Adresse E-mail</th>
                        <th>Rôle / Niveau d'accès</th>
                        <th>Date d'inscription</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 38px; height: 38px;">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <div class="fw-bold text-dark">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                    <span class="badge bg-primary-subtle text-primary border-0 rounded-pill ms-2 fw-normal" style="font-size: 0.65rem">Vous</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-muted small"><i class="bi bi-envelope me-2"></i>{{ $user->email }}</div>
                        </td>
                        <td>
                            @foreach($user->roles as $role)
                            <span class="badge rounded-pill bg-info-subtle text-info px-3 py-1 border-0 small">
                                <i class="bi bi-shield-lock me-1"></i> {{ $role->name }}
                            </span>
                            @endforeach
                            @if($user->roles->isEmpty())
                                <span class="badge rounded-pill bg-light text-muted px-3 py-1 small">Aucun rôle</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-secondary small">{{ $user->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                @can('users.edit')
                                <a href="{{ route('users.edit', $user) }}" class="btn-action-edit" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('users.delete')
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}"
                                      onsubmit="return confirm('Supprimer définitivement cet utilisateur ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-action-delete" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-people fs-1 opacity-25 d-block mb-3"></i>
                                <h5 class="text-muted">Aucun utilisateur enregistré</h5>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
