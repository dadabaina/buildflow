<x-layouts.app title="Récap mensuel — Pointage">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Pointage</li>
        <li class="breadcrumb-item active fw-bold text-dark">Récap mensuel</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Récap mensuel — Pointage</h4>
        <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1"></i>Détail
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Mois</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Chantier</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Tous les chantiers</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary btn-sm mt-4">Afficher</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                Mois de {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
            </h6>
            <a href="{{ route('attendances.recap.export', ['month' => $month, 'project_id' => $projectId]) }}"
               class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i>Exporter CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employé</th>
                            <th class="text-end">Jours travaillés</th>
                            <th class="text-end">Heures totales</th>
                            <th class="text-end">Taux journalier</th>
                            <th class="text-end">Salaire estimé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row['employee']->first_name ?? '' }} {{ $row['employee']->last_name ?? '' }}</strong>
                                    <small class="text-muted d-block">{{ $row['employee']->position ?? '' }}</small>
                                </td>
                                <td class="text-end">{{ number_format($row['total_days'], 2, ',', ' ') }} j</td>
                                <td class="text-end">{{ number_format($row['total_hours'], 2, ',', ' ') }} h</td>
                                <td class="text-end">{{ number_format($row['daily_rate'], 2, ',', ' ') }} Ar</td>
                                <td class="text-end fw-bold text-primary">{{ number_format($row['salary_est'], 2, ',', ' ') }} Ar</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucun pointage présent pour cette période.</td></tr>
                        @endforelse
                    </tbody>
                    @if($rows->count())
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold">{{ number_format($rows->sum('total_days'), 2, ',', ' ') }} j</td>
                                <td class="text-end fw-bold">{{ number_format($rows->sum('total_hours'), 2, ',', ' ') }} h</td>
                                <td></td>
                                <td class="text-end fw-bold text-primary">{{ number_format($rows->sum('salary_est'), 2, ',', ' ') }} Ar</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
