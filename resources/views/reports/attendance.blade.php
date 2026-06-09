<x-layouts.app title="Récapitulatif Pointage">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none opacity-50 text-dark">Rapports</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">RH / Pointage</li>
    </x-slot>

    <!-- Header & Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h4 class="mb-1 fw-bold">Récapitulatif de Pointage</h4>
                            <p class="text-muted small mb-0">Analyse de la main d'œuvre et des présences sur les chantiers.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="GET" class="d-flex gap-2">
                                <select name="month" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                    @foreach($months as $m => $name)
                                        <option value="{{ $m }}" @selected($m == $month)>{{ ucfirst($name) }}</option>
                                    @endforeach
                                </select>
                                <select name="year" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                    @foreach(range(now()->year, now()->year - 4) as $y)
                                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                                    @endforeach
                                </select>
                                <select name="project_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                                    <option value="">Tous les chantiers</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-outline-danger btn-icon shadow-sm" title="Exporter en PDF">
                                <i class="bx bxs-file-pdf"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 small text-uppercase text-muted">Date</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Employé</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Chantier</th>
                            <th class="py-3 border-0 small text-uppercase text-muted">Heures</th>
                            <th class="pe-4 py-3 border-0 small text-uppercase text-muted">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $a)
                        <tr>
                            <td class="ps-4">
                                <span class="text-dark small fw-medium">{{ $a->work_date?->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $a->employee->full_name ?? '—' }}</div>
                                <small class="text-muted small">{{ $a->employee->job_title ?? 'Ouvrier' }}</small>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $a->project->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-label-info">{{ $a->hours ?? 0 }}h</span>
                            </td>
                            <td class="pe-4">
                                <span class="badge {{ $a->status === 'present' ? 'bg-label-success' : ($a->status === 'absent' ? 'bg-label-danger' : 'bg-label-warning') }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ $a->status === 'present' ? 'Présent' : ($a->status === 'absent' ? 'Absent' : $a->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted opacity-25 mb-3"><i class="bx bx-user-check fs-1" style="font-size: 5rem !important;"></i></div>
                                <h6 class="text-muted">Aucun pointage trouvé pour cette période.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())
        <div class="card-footer bg-transparent border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $attendances->links() }}
            </div>
        </div>
        @endif
    </div>

    @push('styles')
    <style>
        .bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
        .bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
        .bg-label-warning { background-color: #fff2e2 !important; color: #ffab00 !important; }
        .bg-label-danger { background-color: #ffe5e5 !important; color: #ff3e1d !important; }
    </style>
    @endpush
</x-layouts.app>
