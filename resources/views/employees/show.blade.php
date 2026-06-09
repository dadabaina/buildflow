<x-layouts.app :title="$employee->full_name">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-decoration-none opacity-50 text-dark">Employés</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $employee->full_name }}</li>
    </x-slot>

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center fw-bold" style="width: 64px; height: 64px; font-size: 1.5rem">
                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">{{ $employee->full_name }}</h4>
                                    <span class="badge {{ $employee->is_active ? 'bg-success' : 'bg-secondary' }} badge-sm">
                                        {{ $employee->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    @foreach($employee->jobTypes as $jt)
                                        <span class="badge bg-label-primary text-uppercase small">{{ $jt->name }}</span>
                                    @endforeach
                                    @if($employee->jobTypes->isEmpty())
                                        <span class="badge bg-label-secondary text-uppercase small">Sans poste</span>
                                    @endif
                                    <span class="text-muted small"><i class="bx bx-id-card me-1"></i>{{ $employee->matricule ?? 'Sans matricule' }}</span>
                                    <span class="text-muted small"><i class="bx bx-phone me-1"></i>{{ $employee->phone ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('employees.edit')
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
                                <i class="bx bx-edit-alt me-1"></i>Modifier
                            </a>
                            @endcan
                            @can('employees.delete')
                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Archiver cet employé ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-icon">
                                    <i class="bx bx-archive"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Jours travaillés</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ number_format($stats['total_days'], 1, ',', ' ') }}</h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-calendar-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Chantiers</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['projects_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-building fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Heures totales</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ number_format($stats['total_hours'], 1, ',', ' ') }} <small class="fs-6 fw-normal">h</small></h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-time fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Gains estimés</p>
                            <h4 class="mb-0 fw-bold mt-1 text-nowrap">{{ number_format($stats['total_earned'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-wallet fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar: Personal Details -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Informations personnelles</h6>
                </div>
                <div class="card-body py-4">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-envelope fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Email</small>
                                <span class="fw-medium text-dark text-truncate" style="max-width: 200px;">{{ $employee->email ?? '—' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-map fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Adresse</small>
                                <span class="fw-medium text-dark">{{ $employee->address ?? '—' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-calendar fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Région d'origine</small>
                                <span class="fw-medium text-dark">{{ $employee->region?->name ?? '—' }}</span>
                            </div>
                        </li>
                        <li class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-buildings fs-5"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <small class="text-muted">Employeur</small>
                                <span class="fw-medium text-dark">
                                    @if($employee->supplier)
                                        <a href="{{ route('suppliers.show', $employee->supplier) }}" class="text-decoration-none">{{ $employee->supplier->name }}</a>
                                    @else
                                        Effectif Interne
                                    @endif
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Conditions contractuelles</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Type de contrat</small>
                        <span class="badge bg-label-info">{{ $employee->contract_type_libelle }}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block mb-1">Date d'embauche</small>
                            <span class="fw-bold text-dark">{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1">Taux journalier</small>
                            <span class="fw-bold text-success">{{ number_format($employee->daily_rate, 0, ',', ' ') }} Ar</span>
                        </div>
                        @if($employee->monthly_salary > 0)
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">Salaire mensuel</small>
                            <span class="fw-bold text-dark">{{ number_format($employee->monthly_salary, 0, ',', ' ') }} Ar</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($employee->notes)
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Observations</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">{{ $employee->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs border-0 shadow-sm bg-white rounded-top" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-projects">
                            <i class="bx bx-building me-2"></i> Chantiers
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-attendance">
                            <i class="bx bx-time-five me-2"></i> Présences
                        </button>
                    </li>
                </ul>
                <div class="tab-content border-0 shadow-sm p-0 rounded-bottom overflow-hidden bg-white">
                    <!-- Projects Tab -->
                    <div class="tab-pane fade show active" id="tab-projects" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Chantier</th>
                                        <th class="py-3">Rôle</th>
                                        <th class="py-3">Statut</th>
                                        <th class="text-end pe-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->projects as $project)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('projects.show', $project) }}" class="fw-bold text-dark text-decoration-none">{{ $project->name }}</a>
                                                <small class="text-muted">{{ $project->reference }}</small>
                                            </div>
                                        </td>
                                        <td><span class="text-muted small">{{ $project->pivot->role ?? 'Ouvrier' }}</span></td>
                                        <td>
                                            <span class="badge {{ $project->status_badge_class }} badge-sm">
                                                {{ $project->status_libelle }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('projects.show', $project) }}" class="btn-action-view">
                                                <i class="bx bx-show fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Aucun chantier affecté.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Attendance Tab -->
                    <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Date</th>
                                        <th class="py-3">Chantier</th>
                                        <th class="py-3">Statut</th>
                                        <th class="text-end pe-4 py-3">Heures / Jours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->attendances as $attendance)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-medium text-dark">{{ $attendance->work_date?->format('d/m/Y') }}</span>
                                        </td>
                                        <td><small class="text-muted">{{ $attendance->project?->name ?? 'N/A' }}</small></td>
                                        <td>
                                            <span class="badge {{ $attendance->status_badge_class }} badge-sm">
                                                {{ $attendance->status_libelle }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="fw-bold text-dark">{{ number_format($attendance->hours_worked, 1) }}h</div>
                                            <small class="text-muted">{{ number_format($attendance->days_worked, 1) }}j</small>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Aucun pointage enregistré.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
