<x-layouts.app title="Tableau Kanban">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}" class="text-decoration-none opacity-50 text-dark">Tâches</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Kanban</li>
    </x-slot>

    <!-- Header & Filters -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Gestion des Tâches</h4>
            <p class="text-muted small mb-0">Suivez l'avancement de vos travaux en temps réel.</p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <select name="project_id" class="form-select border-0 shadow-sm" style="min-width: 200px;" onchange="this.form.submit()">
                    <option value="">Tous les chantiers</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-icon shadow-sm" title="Vue Liste">
                <i class="bx bx-list-ul"></i>
            </a>
            @can('tasks.create')
                <a href="{{ route('tasks.create', ['project_id' => $projectId]) }}" class="btn btn-primary shadow-sm">
                    <i class="bx bx-plus me-1"></i>Nouvelle tâche
                </a>
            @endcan
        </div>
    </div>

    @php
    $colConfig = [
        'a_faire'  => ['label' => 'À faire',   'color' => 'secondary', 'icon' => 'bx-list-plus'],
        'en_cours' => ['label' => 'En cours',   'color' => 'primary',   'icon' => 'bx-loader-circle'],
        'en_pause' => ['label' => 'En pause',   'color' => 'warning',   'icon' => 'bx-pause-circle'],
        'termine'  => ['label' => 'Terminées',  'color' => 'success',   'icon' => 'bx-check-circle'],
    ];
    @endphp

    <!-- Kanban Board -->
    <div class="kanban-wrapper pb-4">
        <div class="row g-3 flex-nowrap overflow-auto pb-3" style="min-height: calc(100vh - 250px);">
            @foreach($colConfig as $status => $cfg)
                <div class="col-12 col-sm-6 col-md-4 col-xl-3" style="min-width: 300px;">
                    <div class="kanban-column bg-light rounded-3 p-2 d-flex flex-column h-100 border border-light-subtle shadow-sm">
                        <!-- Column Header -->
                        <div class="d-flex align-items-center justify-content-between p-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $cfg['color'] }} p-1 rounded">
                                    <i class="bx {{ $cfg['icon'] }} fs-5"></i>
                                </span>
                                <h6 class="mb-0 fw-bold text-dark">{{ $cfg['label'] }}</h6>
                                <span class="badge bg-white text-{{ $cfg['color'] }} border border-{{ $cfg['color'] }} rounded-pill small">
                                    {{ $columns[$status]->count() }}
                                </span>
                            </div>
                        </div>

                        <!-- Column Content (Sortable) -->
                        <div class="kanban-items flex-grow-1 p-1" id="column-{{ $status }}" data-status="{{ $status }}">
                            @foreach($columns[$status] as $task)
                                <div class="card mb-2 shadow-sm kanban-item cursor-pointer border-0" data-id="{{ $task->id }}">
                                    <div class="card-body p-3">
                                        <!-- Task Priority & Tags -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge {{ $task->priority_badge_class }} badge-sm text-uppercase" style="font-size: 0.65rem;">
                                                {{ $task->priority }}
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn btn-link p-0 text-muted hide-arrow" type="button" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li><a class="dropdown-item py-2" href="{{ route('tasks.show', $task) }}"><i class="bx bx-show me-2"></i>Détails</a></li>
                                                    <li><a class="dropdown-item py-2" href="{{ route('tasks.edit', $task) }}"><i class="bx bx-edit-alt me-2"></i>Modifier</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Task Title -->
                                        <h6 class="mb-2">
                                            <a href="{{ route('tasks.show', $task) }}" class="text-dark fw-semibold text-decoration-none">
                                                {{ $task->title }}
                                            </a>
                                        </h6>

                                        <!-- Project Name (if not filtered) -->
                                        @if(!$projectId && $task->project)
                                            <div class="mb-2">
                                                <small class="text-primary fw-medium"><i class="bx bx-building me-1"></i>{{ $task->project->name }}</small>
                                            </div>
                                        @endif

                                        <!-- Task Metadata -->
                                        <div class="d-flex align-items-center justify-content-between mt-3">
                                            <div class="d-flex align-items-center gap-2 text-muted small">
                                                @if($task->due_date)
                                                    <span class="{{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                                        <i class="bx bx-calendar me-1"></i>{{ $task->due_date->format('d/m') }}
                                                    </span>
                                                @endif
                                                @if($task->checklist && count($task->checklist))
                                                    @php
                                                        $done = collect($task->checklist)->where('done', true)->count();
                                                        $total = count($task->checklist);
                                                    @endphp
                                                    <span class="ms-1">
                                                        <i class="bx bx-check-square me-1"></i>{{ $done }}/{{ $total }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Assignees Avatars -->
                                            <div class="avatar-group d-flex align-items-center">
                                                @foreach($task->employees->take(3) as $emp)
                                                    <div class="avatar avatar-xs" title="{{ $emp->full_name }}">
                                                        <span class="avatar-initial rounded-circle bg-label-primary" style="width: 24px; height: 24px; font-size: 0.65rem;">
                                                            {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                                @if($task->employees->count() > 3)
                                                    <div class="avatar avatar-xs ms-n1">
                                                        <span class="avatar-initial rounded-circle bg-secondary text-white small" style="width: 24px; height: 24px; font-size: 0.6rem;">
                                                            +{{ $task->employees->count() - 3 }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('styles')
    <style>
        .kanban-wrapper {
            margin-right: -1.5rem;
            margin-left: -1.5rem;
            padding-right: 1.5rem;
            padding-left: 1.5rem;
        }
        .kanban-column {
            background-color: #f1f3f5 !important;
        }
        .kanban-items {
            overflow-y: auto;
            max-height: calc(100vh - 350px);
        }
        .kanban-item {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: grab;
        }
        .kanban-item:active {
            cursor: grabbing;
        }
        .kanban-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        .sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef !important;
        }
        .avatar-group .avatar {
            margin-left: -0.4rem;
            border: 2px solid #fff;
            transition: all 0.2s;
        }
        .avatar-group .avatar:first-child {
            margin-left: 0;
        }
        .avatar-group .avatar:hover {
            z-index: 10;
            transform: translateY(-2px);
        }
        /* Custom Scrollbar for columns */
        .kanban-items::-webkit-scrollbar { width: 4px; }
        .kanban-items::-webkit-scrollbar-track { background: transparent; }
        .kanban-items::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const columns = ['a_faire', 'en_cours', 'en_pause', 'termine'];
            
            columns.forEach(status => {
                const el = document.getElementById('column-' + status);
                new Sortable(el, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function (evt) {
                        const taskId = evt.item.getAttribute('data-id');
                        const newStatus = evt.to.getAttribute('data-status');
                        const oldStatus = evt.from.getAttribute('data-status');

                        if (newStatus === oldStatus) return;

                        // Update Status via AJAX
                        fetch(`/tasks/${taskId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update counts (optional but nice)
                                updateColumnCounts();
                            } else {
                                alert('Erreur lors de la mise à jour du statut.');
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erreur réseau.');
                            location.reload();
                        });
                    }
                });
            });

            function updateColumnCounts() {
                columns.forEach(status => {
                    const col = document.getElementById('column-' + status);
                    const count = col.querySelectorAll('.kanban-item').length;
                    const badge = col.closest('.kanban-column').querySelector('.badge.rounded-pill');
                    if (badge) badge.innerText = count;
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>
