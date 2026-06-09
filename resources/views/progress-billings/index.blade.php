<x-layouts.app title="Situations de travaux">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Situations de travaux</li>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm-app border-0 rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <x-card title="Situations de travaux" icon="bi bi-bar-chart-steps">
        <x-slot name="headerActions">
            @can('progress_billings.create')
            <a href="{{ route('progress-billings.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm-app">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle situation
            </a>
            @endcan
        </x-slot>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-4">
                <select name="project_id" class="form-select">
                    <option value="">Tous les chantiers</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Tous statuts</option>
                    <option value="brouillon" {{ request('status') === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                    <option value="envoye" {{ request('status') === 'envoye' ? 'selected' : '' }}>Envoyé</option>
                    <option value="valide" {{ request('status') === 'valide' ? 'selected' : '' }}>Validé</option>
                    <option value="facture" {{ request('status') === 'facture' ? 'selected' : '' }}>Facturé</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-light border w-100"><i class="bi bi-search me-1"></i>Filtrer</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Référence</th>
                        <th class="border-0">Titre</th>
                        <th class="border-0">Chantier</th>
                        <th class="border-0">Sit. N°</th>
                        <th class="border-0">Net à payer</th>
                        <th class="border-0">Statut</th>
                        <th class="border-0">Date</th>
                        <th class="border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billings as $sit)
                    <tr>
                        <td><a href="{{ route('progress-billings.show', $sit) }}" class="font-monospace small text-decoration-none fw-bold text-primary">{{ $sit->reference }}</a></td>
                        <td class="text-dark small">{{ Str::limit($sit->title, 40) }}</td>
                        <td class="text-muted small">{{ $sit->project->name ?? '—' }}</td>
                        <td class="text-center fw-bold text-primary">{{ $sit->situation_number }}</td>
                        <td class="fw-bold small">{{ number_format($sit->net_to_pay, 0, ',', ' ') }} <small>MGA</small></td>
                        <td><span class="badge {{ $sit->status_badge_class }} px-2 py-1 rounded-pill" style="font-size:0.7rem">{{ $sit->status_libelle }}</span></td>
                        <td class="text-muted small">{{ $sit->billing_date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('progress-billings.show', $sit) }}" class="btn btn-light btn-sm shadow-sm-app">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart-steps fs-2 d-block mb-2 opacity-25"></i>
                            Aucune situation de travaux.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($billings->hasPages())
        <div class="d-flex justify-content-center mt-4">{{ $billings->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
