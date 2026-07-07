<x-layouts.app title="Gestion des Dépenses">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Dépenses</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Dépenses</h3>
            <p class="text-secondary small mb-0">Suivez vos coûts opérationnels et validez les notes de frais.</p>
        </div>
        @can('expenses.create')
        <a href="{{ route('expenses.create') }}" id="tour-expenses-new" class="btn btn-primary shadow-app d-flex align-items-center gap-2">
            <i class="bi bi-receipt fs-5"></i>
            <span>Enregistrer une dépense</span>
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <div class="card border-0 shadow-sm-app mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Description, référence..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous statuts</option>
                        <option value="saisie" {{ request('status') === 'saisie' ? 'selected' : '' }}>Saisie</option>
                        <option value="validee" {{ request('status') === 'validee' ? 'selected' : '' }}>Validée</option>
                        <option value="rejetee" {{ request('status') === 'rejetee' ? 'selected' : '' }}>Rejetée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Tous les chantiers</option>
                        @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                            {{ $proj->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary px-3">Filtrer</button>
                    @if(request()->hasAny(['search', 'status', 'project_id']))
                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-light border px-3">Réinitialiser</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @php
    $statusConfig = [
        'saisie'  => ['label' => 'Saisie',   'class' => 'badge-soft-secondary'],
        'validee' => ['label' => 'Validée',  'class' => 'badge-soft-success'],
        'rejetee' => ['label' => 'Rejetée',  'class' => 'badge-soft-danger'],
    ];
    @endphp

    <div class="card border-0 shadow-sm-app overflow-hidden">
        <div class="table-responsive">
            <table id="tour-expenses-table" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Référence & Description</th>
                        <th>Chantier / Catégorie</th>
                        <th>Tâche</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($expenses as $expense)
                    @php $sc = $statusConfig[$expense->status] ?? ['label' => $expense->status, 'class' => 'badge-soft-secondary']; @endphp
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-indigo-subtle text-indigo rounded-3 p-2 d-flex align-items-center justify-content-center me-3 shadow-sm-app" style="width: 42px; height: 42px;">
                                    <i class="bi bi-receipt-cutoff fs-5"></i>
                                </div>
                                <div>
                                    <a href="{{ route('expenses.show', $expense) }}" class="text-decoration-none fw-bold text-dark d-block mb-0 hov-primary">
                                        {{ Str::limit($expense->description, 45) }}
                                    </a>
                                    <span class="text-muted font-monospace small" style="font-size: 0.7rem">{{ $expense->reference }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark small">{{ $expense->project?->name ?? 'Frais Généraux' }}</div>
                            <div class="small text-muted"><i class="bi bi-tag me-1"></i>{{ $expense->expenseCategory?->name ?? 'Non classé' }}</div>
                        </td>
                        <td class="small">
                            @if($expense->task)
                            <a href="{{ route('tasks.show', $expense->task) }}" class="text-decoration-none">{{ Str::limit($expense->task->title, 30) }}</a>
                            @else
                            <span class="text-muted">— Générale —</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-danger">
                                {{ number_format($expense->total_amount, 0, ',', ' ') }}
                                <small class="fw-normal opacity-75">MGA</small>
                            </div>
                        </td>
                        <td>
                            <div class="small text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ $expense->expense_date?->format('d M Y') }}
                            </div>
                        </td>
                        <td>
                            <span class="badge rounded-pill {{ $sc['class'] }} px-3 py-2">
                                {{ $sc['label'] }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                @if($expense->status === 'saisie')
                                    @can('expenses.validate')
                                    <form method="POST" action="{{ route('expenses.validate', $expense) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn-action-edit" title="Valider"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                    @endcan
                                    @can('expenses.reject')
                                    <form method="POST" action="{{ route('expenses.reject', $expense) }}" onsubmit="return injectRejectionReason(this)">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="rejection_reason">
                                        <button class="btn-action-delete" title="Rejeter"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                    @endcan
                                @endif
                                <a href="{{ route('expenses.show', $expense) }}" class="btn-action-view" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('expenses.delete')
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                      onsubmit="return confirm('Supprimer cette dépense ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-action-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-5">
                                <i class="bi bi-receipt fs-1 opacity-25 d-block mb-3"></i>
                                <h5 class="text-muted">Aucune dépense trouvée</h5>
                                <p class="text-muted small">Les dépenses enregistrées apparaîtront ici.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="card-footer bg-white py-3 border-top border-light">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function injectRejectionReason(form) {
            const reason = prompt('Motif du rejet :');
            if (!reason || !reason.trim()) return false;
            form.querySelector('input[name="rejection_reason"]').value = reason.trim();
            return true;
        }
    </script>
    @endpush
</x-layouts.app>
