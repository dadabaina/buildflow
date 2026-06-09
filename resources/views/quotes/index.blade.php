<x-layouts.app title="Gestion des Devis">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Devis</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Devis</h3>
            <p class="text-secondary small mb-0">Suivez vos propositions commerciales et leur taux d'acceptation.</p>
        </div>
        @can('quotes.create')
        <a href="{{ route('quotes.create') }}" class="btn btn-primary shadow-app d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-plus-fill fs-5"></i>
            <span>Nouveau devis</span>
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
                               placeholder="Rechercher par réf ou titre..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        @foreach(['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'accepte' => 'Accepté', 'refuse' => 'Refusé', 'expire' => 'Expiré'] as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Tous les chantiers</option>
                        @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto ms-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary px-3">Filtrer</button>
                    @if(request()->hasAny(['search', 'status', 'project_id']))
                    <a href="{{ route('quotes.index') }}" class="btn btn-sm btn-light border px-3">Réinitialiser</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm-app overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Référence & Titre</th>
                        <th>Client</th>
                        <th>Montant TTC</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($quotes as $quote)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-indigo-subtle text-indigo rounded-3 p-2 d-flex align-items-center justify-content-center me-3 shadow-sm-app" style="width: 42px; height: 42px;">
                                    <i class="bi bi-file-earmark-text-fill fs-5"></i>
                                </div>
                                <div>
                                    <a href="{{ route('quotes.show', $quote) }}" class="text-decoration-none fw-bold text-dark d-block mb-0 hov-primary">
                                        {{ Str::limit($quote->title, 40) }}
                                    </a>
                                    <span class="text-muted font-monospace small" style="font-size: 0.75rem">{{ $quote->reference }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $quote->client?->name ?? '—' }}</div>
                            <div class="small text-muted">{{ $quote->project?->name ?? 'Sans chantier' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">
                                {{ number_format($quote->total_ttc, 0, ',', ' ') }}
                                <small class="text-muted fw-normal">MGA</small>
                            </div>
                        </td>
                        <td>
                            <div class="small text-muted">
                                <div><i class="bi bi-calendar-event me-1"></i> {{ $quote->quote_date?->format('d M Y') }}</div>
                                @if($quote->valid_until)
                                <div class="{{ $quote->valid_until->isPast() ? 'text-danger fw-bold' : '' }}">
                                    <i class="bi bi-clock-history me-1"></i> Exp: {{ $quote->valid_until?->format('d/m/y') }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClasses = [
                                    'brouillon' => 'badge-soft-secondary',
                                    'envoye' => 'badge-soft-info',
                                    'accepte' => 'badge-soft-success',
                                    'refuse' => 'badge-soft-danger',
                                    'expire' => 'badge-soft-warning'
                                ];
                                $badgeClass = $statusClasses[$quote->status] ?? 'badge-soft-secondary';
                            @endphp
                            <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">
                                {{ $quote->status_libelle }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('quotes.show', $quote) }}" class="btn-action-view" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('quotes.edit')
                                    @if($quote->status === 'brouillon')
                                    <a href="{{ route('quotes.edit', $quote) }}" class="btn-action-edit" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                @endcan
                                @can('quotes.delete')
                                <form method="POST" action="{{ route('quotes.destroy', $quote) }}"
                                      onsubmit="return confirm('Supprimer ce devis ?')">
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
                        <td colspan="6" class="text-center py-5">
                            <div class="py-5">
                                <i class="bi bi-file-earmark-text fs-1 opacity-25 d-block mb-3"></i>
                                <h5 class="text-muted">Aucun devis trouvé</h5>
                                <p class="text-muted small mb-0">Établissez votre première proposition pour la retrouver ici.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
        <div class="card-footer bg-white py-3 border-top border-light">
            {{ $quotes->links() }}
        </div>
        @endif
    </div>
</x-layouts.app>
