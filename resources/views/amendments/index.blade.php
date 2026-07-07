<x-layouts.app title="Avenants">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Avenants</li>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm-app border-0 rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <x-card title="Avenants" icon="bi bi-file-earmark-plus">
        <x-slot name="headerActions">
            @can('amendments.create')
            <a href="{{ route('amendments.create') }}" id="tour-amendments-new" class="btn btn-primary btn-sm px-3 shadow-sm-app">
                <i class="bi bi-plus-lg me-1"></i>Nouvel avenant
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
                    <option value="accepte" {{ request('status') === 'accepte' ? 'selected' : '' }}>Accepté</option>
                    <option value="refuse" {{ request('status') === 'refuse' ? 'selected' : '' }}>Refusé</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-light border w-100"><i class="bi bi-search me-1"></i>Filtrer</button>
            </div>
        </form>

        <div class="table-responsive">
            <table id="tour-amendments-table" class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Référence</th>
                        <th class="border-0">Titre</th>
                        <th class="border-0">Chantier</th>
                        <th class="border-0">Total TTC</th>
                        <th class="border-0">Statut</th>
                        <th class="border-0">Date</th>
                        <th class="border-0"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($amendments as $avt)
                    <tr>
                        <td><a href="{{ route('amendments.show', $avt) }}" class="font-monospace small text-decoration-none fw-bold text-primary">{{ $avt->reference }}</a></td>
                        <td class="fw-bold text-dark small">{{ Str::limit($avt->title, 50) }}</td>
                        <td class="text-muted small">{{ $avt->project->name ?? '—' }}</td>
                        <td class="fw-bold small {{ $avt->total_ttc < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($avt->total_ttc, 0, ',', ' ') }} <small>MGA</small></td>
                        <td><span class="badge {{ $avt->status_badge_class }} px-2 py-1 rounded-pill" style="font-size:0.7rem">{{ $avt->status_libelle }}</span></td>
                        <td class="text-muted small">{{ $avt->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('amendments.show', $avt) }}" class="btn btn-light btn-sm shadow-sm-app">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-plus fs-2 d-block mb-2 opacity-25"></i>
                            Aucun avenant trouvé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($amendments->hasPages())
        <div class="d-flex justify-content-center mt-4">{{ $amendments->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
