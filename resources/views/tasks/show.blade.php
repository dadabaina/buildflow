<x-layouts.app :title="$task->title">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}" class="text-decoration-none opacity-50 text-dark">Tâches</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ Str::limit($task->title, 30) }}</li>
    </x-slot>

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-label-{{ $task->status === 'termine' ? 'success' : 'primary' }} rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx {{ $task->status === 'termine' ? 'bx-check-double' : 'bx-task' }} fs-1"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">{{ $task->title }}</h4>
                                    @if($task->isOverdue())
                                        <span class="badge bg-danger animate__animated animate__pulse animate__infinite">En retard</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge {{ $task->status_badge_class }} text-uppercase small">{{ $task->status_libelle }}</span>
                                    <span class="badge {{ $task->priority_badge_class }} text-uppercase small">{{ $task->priority }}</span>
                                    <span class="text-muted small"><i class="bx bx-building me-1"></i>{{ $task->project->name ?? 'Sans projet' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('tasks.edit')
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">
                                <i class="bx bx-edit-alt me-1"></i>Modifier
                            </a>
                            @endcan
                            @can('tasks.delete')
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Supprimer cette tâche ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-icon">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Description & Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-align-left me-2"></i>Description & Détails</h6>
                </div>
                <div class="card-body py-4">
                    @if($task->description)
                        <div class="mb-4">
                            <p class="text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $task->description }}</p>
                        </div>
                    @else
                        <div class="mb-4 p-3 bg-light rounded text-center">
                            <small class="text-muted italic">Aucune description fournie pour cette tâche.</small>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 0.65rem;">Date d'échéance</small>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-calendar fs-5 text-primary"></i>
                                <span class="fw-bold {{ $task->isOverdue() ? 'text-danger' : 'text-dark' }}">
                                    {{ $task->due_date ? $task->due_date->format('d/m/Y') : 'Non définie' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 0.65rem;">Créée le</small>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-time fs-5 text-primary"></i>
                                <span class="text-dark fw-medium">{{ $task->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 0.65rem;">Auteur</small>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-user-circle fs-5 text-primary"></i>
                                <span class="text-dark fw-medium">{{ $task->createdBy->name ?? 'Système' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist -->
            @if($task->checklist && count($task->checklist))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-check-square me-2"></i>Checklist de progression</h6>
                    @php
                        $done = collect($task->checklist)->where('done', true)->count();
                        $total = count($task->checklist);
                        $percent = $total > 0 ? round(($done / $total) * 100) : 0;
                    @endphp
                    <span class="badge bg-label-primary">{{ $done }}/{{ $total }} complété ({{ $percent }}%)</span>
                </div>
                <div class="card-body py-4">
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div id="checklist-container">
                        @foreach($task->checklist as $index => $item)
                            <div class="form-check custom-checklist-item p-3 rounded mb-2 border transition-all hover-bg-light">
                                <input class="form-check-input ms-0 checklist-toggle" type="checkbox" 
                                       id="check-{{ $index }}" 
                                       data-index="{{ $index }}"
                                       @if(!empty($item['done'])) checked @endif>
                                <label class="form-check-label ms-2 fw-medium {{ !empty($item['done']) ? 'text-decoration-line-through text-muted' : 'text-dark' }}" for="check-{{ $index }}">
                                    {{ $item['label'] ?? '' }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Comments Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-comment-detail me-2"></i>Commentaires ({{ $task->comments->count() }})</h6>
                </div>
                <div class="card-body py-4">
                    <div class="comments-list mb-4">
                        @forelse($task->comments as $comment)
                            <div class="d-flex gap-3 mb-4 last-child-mb-0">
                                <div class="avatar avatar-sm flex-shrink-0">
                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                        {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                                <div class="bg-light p-3 rounded-3 flex-grow-1 border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark">{{ $comment->user->name ?? 'Utilisateur' }}</span>
                                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-muted small">{{ $comment->body }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="bx bx-chat d-block fs-1 opacity-25 mb-2"></i>
                                Aucun commentaire pour le moment.
                            </div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-4 border-top pt-4">
                        @csrf
                        <div class="d-flex gap-3">
                            <div class="avatar avatar-sm flex-shrink-0 d-none d-sm-block">
                                <img src="{{ auth()->user()->avatar_url }}" alt="avatar" class="rounded-circle">
                            </div>
                            <div class="flex-grow-1">
                                <textarea name="body" class="form-control @error('body') is-invalid @enderror" 
                                          rows="2" placeholder="Écrire un commentaire..." required></textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="mt-2 text-end">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                                        Envoyer<i class="bx bx-paper-plane ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Transition -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Statut de la tâche</h6>
                </div>
                <div class="card-body py-4 text-center">
                    <div class="mb-4">
                        <small class="text-muted d-block mb-2">Statut actuel</small>
                        <span class="badge {{ $task->status_badge_class }} p-2 px-3 text-uppercase" style="font-size: 0.9rem;">
                            {{ $task->status_libelle }}
                        </span>
                    </div>

                    <div class="dropdown w-100">
                        <button class="btn btn-outline-primary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Changer le statut
                        </button>
                        <ul class="dropdown-menu w-100 shadow-sm border-0">
                            @foreach(['a_faire'=>'À faire','en_cours'=>'En cours','en_pause'=>'En pause','termine'=>'Terminée','annule'=>'Annulée'] as $val=>$lbl)
                                @if($val !== $task->status)
                                    <li>
                                        <form method="POST" action="{{ route('tasks.status', $task) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $val }}">
                                            <button type="submit" class="dropdown-item py-2">→ {{ $lbl }}</button>
                                        </form>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Assignees -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Assigné(e)s ({{ $task->employees->count() }})</h6>
                </div>
                <div class="card-body py-4">
                    <ul class="list-unstyled mb-0">
                        @forelse($task->employees as $emp)
                            <li class="d-flex align-items-center mb-3 last-child-mb-0">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $emp->full_name }}</span>
                                    <small class="text-muted small">{{ $emp->jobType?->name ?? 'Ouvrier' }}</small>
                                </div>
                                <a href="{{ route('employees.show', $emp) }}" class="ms-auto btn btn-icon btn-sm btn-label-secondary">
                                    <i class="bx bx-chevron-right"></i>
                                </a>
                            </li>
                        @empty
                            <li class="text-center py-2">
                                <small class="text-muted">Aucun employé assigné.</small>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Danger Zone -->
            @can('tasks.delete')
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 border-danger border-opacity-10">
                <div class="card-body p-4 text-center">
                    <p class="text-danger small mb-3 fw-medium">Attention: La suppression est définitive.</p>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Supprimer cette tâche ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 shadow-sm">
                            <i class="bx bx-trash me-2"></i>Supprimer la tâche
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>

    @push('styles')
    <style>
        .custom-checklist-item {
            transition: all 0.2s;
            background-color: #fff;
        }
        .custom-checklist-item:hover {
            background-color: #f8f9fa;
            border-color: #d9dee3 !important;
        }
        .hover-bg-light:hover {
            background-color: #f8f9fa;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
        .last-child-mb-0:last-child {
            margin-bottom: 0 !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checklistItems = document.querySelectorAll('.checklist-toggle');
            
            checklistItems.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.nextElementSibling;
                    const index = this.getAttribute('data-index');
                    const isDone = this.checked;

                    // UI Feedback
                    if (isDone) {
                        label.classList.add('text-decoration-line-through', 'text-muted');
                    } else {
                        label.classList.remove('text-decoration-line-through', 'text-muted');
                    }

                    // Prepare Checklist Data
                    const currentChecklist = [];
                    document.querySelectorAll('.checklist-toggle').forEach(chk => {
                        currentChecklist.push({
                            label: chk.nextElementSibling.innerText,
                            done: chk.checked
                        });
                    });

                    // AJAX Update
                    fetch('{{ route('tasks.checklist', $task) }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ checklist: currentChecklist })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok) {
                            // Optionally update the badge count/percentage here
                            console.log('Checklist updated');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revert on error
                        this.checked = !isDone;
                        if (!isDone) label.classList.add('text-decoration-line-through', 'text-muted');
                        else label.classList.remove('text-decoration-line-through', 'text-muted');
                    });
                });
            });
        });
    </script>
    @endpush
</x-layouts.app>
