<x-layouts.app title="Paiements Salariés">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Paiements Salariés</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-people-fill me-2 text-primary"></i>Paiements salariés
            <span class="badge bg-secondary ms-2">{{ $payments->total() }}</span>
        </h5>
        @can('salary_payments.create')
        <a href="{{ route('salary-payments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau paiement
        </a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Salarié</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Chantier</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Du</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Au</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary flex-fill">Filtrer</button>
                    <a href="{{ route('salary-payments.index') }}" class="btn btn-sm btn-outline-secondary">Réinit.</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Salarié</th>
                        <th>Ventilation par chantier</th>
                        <th>Mode</th>
                        <th>Référence</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="small">{{ $payment->payment_date?->format('d/m/Y') }}</td>
                        <td class="small fw-medium">{{ $payment->employee?->full_name }}</td>
                        <td>
                            @foreach($payment->projects as $proj)
                            <span class="badge bg-light text-dark border mb-1">
                                {{ $proj->name }} : {{ number_format($proj->pivot->amount, 0, ',', ' ') }}
                            </span>
                            @endforeach
                        </td>
                        <td class="small">{{ $payment->payment_mode ?? '—' }}</td>
                        <td class="small text-muted">{{ $payment->reference ?? '—' }}</td>
                        <td class="text-end fw-medium text-warning">{{ number_format($payment->amount, 0, ',', ' ') }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                @can('salary_payments.delete')
                                <form method="POST" action="{{ route('salary-payments.destroy', $payment) }}"
                                      onsubmit="return confirm('Supprimer ce paiement ?')">
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
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            Aucun paiement salarié enregistré
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="card-footer">{{ $payments->links() }}</div>
        @endif
    </div>
</x-layouts.app>
