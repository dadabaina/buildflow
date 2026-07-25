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
                                    @if($att->photo_path)
                                        <a href="{{ asset('storage/' . $att->photo_path) }}" target="_blank">
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
                                <td><span class="badge {{ $att->status_badge_class }}">{{ $att->status_libelle }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('attendances.edit', $att) }}" class="btn-action-edit" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @can('delete', $att)
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
                            <tr><td colspan="11" class="text-center text-muted py-4">Aucun pointage trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())
            <div class="card-footer">{{ $attendances->links() }}</div>
        @endif
    </div>
</x-layouts.app>
