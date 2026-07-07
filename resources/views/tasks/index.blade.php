<x-layouts.app title="Tâches">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Tâches</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-kanban me-2"></i>Tâches</h4>
        <div>
            <a href="{{ route('tasks.kanban') }}" id="tour-tasks-kanban-link" class="btn btn-outline-secondary btn-sm me-1">
                <i class="bi bi-grid-3x3-gap me-1"></i>Kanban
            </a>
            @can('tasks.create')
                <a href="{{ route('tasks.create') }}" id="tour-tasks-new" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Nouvelle tâche
                </a>
            @endcan
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Titre…" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Tous les chantiers</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous statuts</option>
                        <option value="a_faire" @selected(request('status')=='a_faire')>À faire</option>
                        <option value="en_cours" @selected(request('status')=='en_cours')>En cours</option>
                        <option value="en_pause" @selected(request('status')=='en_pause')>En pause</option>
                        <option value="termine" @selected(request('status')=='termine')>Terminée</option>
                        <option value="annule" @selected(request('status')=='annule')>Annulée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Toutes priorités</option>
                        <option value="urgente" @selected(request('priority')=='urgente')>Urgente</option>
                        <option value="haute" @selected(request('priority')=='haute')>Haute</option>
                        <option value="normale" @selected(request('priority')=='normale')>Normale</option>
                        <option value="basse" @selected(request('priority')=='basse')>Basse</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Titre</th>
                            <th>Chantier</th>
                            <th>Assignés</th>
                            <th>Poids</th>
                            <th>Priorité</th>
                            <th>Échéance</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a>
                                    @if($task->isOverdue())
                                        <span class="badge bg-danger ms-1">En retard</span>
                                    @endif
                                </td>
                                <td>{{ $task->project->name ?? '-' }}</td>
                                <td>
                                    @foreach($task->employees->take(3) as $emp)
                                        <span class="badge bg-light text-dark border">{{ $emp->first_name }}</span>
                                    @endforeach
                                    @if($task->employees->count() > 3)
                                        <span class="text-muted small">+{{ $task->employees->count() - 3 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $task->weight }}</span>
                                </td>
                                <td><span class="badge {{ $task->priority_badge_class }}">{{ ucfirst($task->priority) }}</span></td>
                                <td>{{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}</td>
                                <td><span class="badge {{ $task->status_badge_class }}">{{ $task->status_libelle }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('tasks.show', $task) }}" class="btn-action-view" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('tasks.edit', $task) }}" class="btn-action-edit" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @can('delete', $task)
                                            <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                                                  onsubmit="return confirm('Supprimer cette tâche ?')">
                                                @csrf @method('DELETE')
                                                <button class="btn-action-delete" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucune tâche trouvée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tasks->hasPages())
            <div class="card-footer">{{ $tasks->links() }}</div>
        @endif
    </div>
</x-layouts.app>
