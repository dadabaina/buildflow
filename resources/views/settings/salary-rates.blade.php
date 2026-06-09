<x-layouts.app title="Paramètres — Grille salariale">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none opacity-50 text-dark">Paramètres</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Grille salariale</li>
    </x-slot>

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Paramètres</h3>
        <p class="text-secondary small">Configurez votre entreprise et personnalisez votre espace de travail.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-3">
            @include('settings._nav')
        </div>

        <div class="col-lg-9">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <x-card title="Grille salariale" subtitle="Définissez les tarifs journaliers par métier et région.">
                <form method="POST" action="{{ route('settings.salary_rates.store') }}" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Poste / Métier <span class="text-danger">*</span></label>
                            <select name="job_type_id" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                @foreach($jobTypes as $jt)
                                    <option value="{{ $jt->id }}">{{ $jt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-medium">Région</label>
                            <select name="region_id" class="form-select">
                                <option value="">Nationale</option>
                                @foreach($regions as $reg)
                                    <option value="{{ $reg->id }}">{{ $reg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-medium">Taux/jour (Ar) <span class="text-danger">*</span></label>
                            <input type="number" name="daily_rate" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-medium">Taux/heure (Ar)</label>
                            <input type="number" name="hourly_rate" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-medium">Date effet <span class="text-danger">*</span></label>
                            <input type="date" name="effective_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary shadow-sm-app"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Poste</th>
                                <th>Région</th>
                                <th class="text-end">Taux/jour</th>
                                <th class="text-end">Taux/h</th>
                                <th>Date effet</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salaryRates as $sr)
                            <tr>
                                <td class="fw-semibold">{{ $sr->jobType->name ?? '—' }}</td>
                                <td class="text-muted">{{ $sr->region->name ?? 'Nationale' }}</td>
                                <td class="text-end">{{ number_format($sr->daily_rate, 0, ',', ' ') }} Ar</td>
                                <td class="text-end">{{ $sr->hourly_rate ? number_format($sr->hourly_rate, 0, ',', ' ') . ' Ar' : '—' }}</td>
                                <td>{{ $sr->effective_date->format('d/m/Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('settings.salary_rates.destroy', $sr) }}" onsubmit="return confirm('Supprimer ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-action-delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucune entrée de grille salariale.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
