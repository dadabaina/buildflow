<x-layouts.app title="Pointage">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Pointage</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pointage</h4>
        <div>
            <a href="{{ route('attendances.recap') }}" id="tour-attendance-recap" class="btn btn-outline-secondary btn-sm me-1">
                <i class="bi bi-bar-chart me-1"></i>Récap mensuel
            </a>
            @can('attendances.create')
                <a href="{{ route('attendances.create') }}" id="tour-attendance-new" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Saisir pointage
                </a>
            @endcan
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Tous chantiers</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">Tous employés</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>
                                {{ $emp->first_name }} {{ $emp->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ request('date_from') }}" placeholder="Du">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ request('date_to') }}" placeholder="Au">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous statuts</option>
                        <option value="present" @selected(request('status')=='present')>Présent</option>
                        <option value="absent_justifie" @selected(request('status')=='absent_justifie')>Absent justifié</option>
                        <option value="absent_non_justifie" @selected(request('status')=='absent_non_justifie')>Absent non justifié</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Photo employé</th>
                            <th>Photo pointage</th>
                            <th>Date</th>
                            <th>Employé</th>
                            <th>Chantier</th>
                            <th>Entrée</th>
                            <th>Sortie</th>
                            <th class="text-end">Heures</th>
                            <th class="text-end">Jours</th>
                            <th>Tâche</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                            <tr>
                                <td>
                                    @if($att->employee?->photo_url)
                                        <img src="{{ $att->employee->photo_url }}"
                                             class="rounded-circle shadow-sm"
                                             style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;"
                                             title="Photo de référence de l'employé"
                                             alt="Photo de l'employé">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted"
                                             style="width: 32px; height: 32px; border: 2px solid #fff;"
                                             title="Aucune photo employé enregistrée">
                                            <i class="bx bx-user small"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($att->photo_path_in || $att->photo_path_out)
                                        <div class="d-flex gap-1">
                                            @if($att->photo_path_in)
                                                <a href="javascript:void(0)" onclick="showPhotoModal('{{ asset('storage/' . $att->photo_path_in) }}', '{{ addslashes(($att->employee->first_name ?? '') . ' ' . ($att->employee->last_name ?? '')) }} — Entrée {{ substr($att->check_in ?? '', 0, 5) }}')">
                                                    <img src="{{ asset('storage/' . $att->photo_path_in) }}"
                                                         class="rounded-circle shadow-sm"
                                                         style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;"
                                                         title="Photo d'entrée (Kiosque)"
                                                         alt="Photo d'entrée">
                                                </a>
                                            @else
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted"
                                                     style="width: 32px; height: 32px; border: 2px solid #fff;" title="Pas de photo d'entrée">
                                                    <i class="bx bx-log-in-circle small"></i>
                                                </div>
                                            @endif
                                            @if($att->photo_path_out)
                                                <a href="javascript:void(0)" onclick="showPhotoModal('{{ asset('storage/' . $att->photo_path_out) }}', '{{ addslashes(($att->employee->first_name ?? '') . ' ' . ($att->employee->last_name ?? '')) }} — Sortie {{ substr($att->check_out ?? '', 0, 5) }}')">
                                                    <img src="{{ asset('storage/' . $att->photo_path_out) }}"
                                                         class="rounded-circle shadow-sm"
                                                         style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;"
                                                         title="Photo de sortie (Kiosque)"
                                                         alt="Photo de sortie">
                                                </a>
                                            @else
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted"
                                                     style="width: 32px; height: 32px; border: 2px solid #fff;" title="Pas de photo de sortie">
                                                    <i class="bx bx-log-out-circle small"></i>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($att->photo_path)
                                        <a href="javascript:void(0)" onclick="showPhotoModal('{{ asset('storage/' . $att->photo_path) }}', '{{ addslashes(($att->employee->first_name ?? '') . ' ' . ($att->employee->last_name ?? '')) }}')">
                                            <img src="{{ asset('storage/' . $att->photo_path) }}"
                                                 class="rounded-circle shadow-sm"
                                                 style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #fff;"
                                                 title="Photo capturée au moment du pointage"
                                                 alt="Photo de présence">
                                        </a>
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted"
                                             style="width: 32px; height: 32px; border: 2px solid #fff;">
                                            <i class="bx bx-camera small"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $att->work_date->format('d/m/Y') }}</td>
                                <td>{{ $att->employee->first_name ?? '' }} {{ $att->employee->last_name ?? '' }}</td>
                                <td>{{ $att->project->name ?? '-' }}</td>
                                <td>{{ $att->check_in ?? '-' }}</td>
                                <td>{{ $att->check_out ?? '-' }}</td>
                                <td class="text-end">{{ $att->hours_worked ? number_format($att->hours_worked, 2, ',', '') . 'h' : '-' }}</td>
                                <td class="text-end">{{ $att->days_worked ? number_format($att->days_worked, 2, ',', '') . 'j' : '-' }}</td>
                                <td>
                                    @if($att->task)
                                        <span class="badge bg-light text-dark border">{{ $att->task->title }}</span>
                                    @endif
                                    @if($att->task_note)
                                        <div class="small text-muted">{{ Str::limit($att->task_note, 40) }}</div>
                                    @endif
                                    @if(!$att->task && !$att->task_note)
                                        <span class="text-muted">—</span>
                                    @endif
                                    @if($att->work_date->isToday())
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline" data-bs-toggle="modal" data-bs-target="#taskModal{{ $att->id }}" title="Modifier la tâche du jour">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                </td>
                                <td><span class="badge {{ $att->status_badge_class }}">{{ $att->status_libelle }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @can('attendances.edit')
                                        <a href="{{ route('attendances.edit', $att) }}" class="btn-action-edit" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endcan
                                        @can('attendances.delete')
                                            <form method="POST" action="{{ route('attendances.destroy', $att) }}"
                                                  onsubmit="return confirm('Supprimer ce pointage ?')">
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
                            <tr><td colspan="12" class="text-center text-muted py-4">Aucun pointage trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())
            <div class="card-footer">{{ $attendances->links() }}</div>
        @endif
    </div>

    {{-- Modales de modification de tâche (pointages du jour uniquement) --}}
    @foreach($attendances as $att)
        @if($att->work_date->isToday())
        <div class="modal fade" id="taskModal{{ $att->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('attendances.task.update', $att) }}">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h6 class="modal-title">Tâche — {{ $att->employee?->full_name }} ({{ $att->work_date->format('d/m/Y') }})</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label small">Tâche</label>
                            <select name="task_id" class="form-select mb-2">
                                <option value="">— Aucune / non précisée —</option>
                                @foreach($att->project->tasks as $t)
                                    <option value="{{ $t->id }}" @selected($att->task_id == $t->id)>{{ $t->title }}</option>
                                @endforeach
                            </select>
                            <label class="form-label small">Précision libre</label>
                            <input type="text" name="task_note" class="form-control" maxlength="255" value="{{ $att->task_note }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    {{-- Aperçu photo pointage --}}
    <div class="modal fade" id="photoPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="photoPreviewModalTitle">Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 text-center bg-dark">
                    <img id="photoPreviewModalImg" src="" alt="" class="img-fluid" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showPhotoModal(src, title) {
            document.getElementById('photoPreviewModalImg').src = src;
            document.getElementById('photoPreviewModalTitle').textContent = title || 'Photo';
            new bootstrap.Modal(document.getElementById('photoPreviewModal')).show();
        }
    </script>
    @endpush
</x-layouts.app>
