<x-layouts.app title="Gestion des Factures">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Factures</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Factures</h3>
            <p class="text-secondary small mb-0">Gérez vos encaissements et suivez les retards de paiement.</p>
        </div>
        @can('invoices.create')
        <a href="{{ route('invoices.create') }}" class="btn btn-primary shadow-app d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-check-fill fs-5"></i>
            <span>Nouvelle facture</span>
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <div class="card border-0 shadow-sm-app mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Réf, client..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous statuts</option>
                        @foreach(['brouillon' => 'Brouillon', 'envoye' => 'Envoyée', 'partiel' => 'Partiel', 'paye' => 'Payée', 'en_retard' => 'En retard'] as $v => $l)
                        <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous types</option>
                        <option value="facture">Facture</option>
                        <option value="acompte">Acompte</option>
                        <option value="avoir">Avoir</option>
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
                    @if(request()->hasAny(['search', 'status', 'type', 'project_id']))
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-light border px-3">Réinitialiser</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @php
    $statusConfig = [
        'brouillon' => ['label' => 'Brouillon',  'class' => 'badge-soft-secondary'],
        'envoye'    => ['label' => 'Envoyée',     'class' => 'badge-soft-info'],
        'partiel'   => ['label' => 'Partiel',     'class' => 'badge-soft-warning'],
        'paye'      => ['label' => 'Payée',       'class' => 'badge-soft-success'],
        'en_retard' => ['label' => 'En retard',   'class' => 'badge-soft-danger'],
        'annule'    => ['label' => 'Annulée',     'class' => 'bg-dark text-white'],
    ];
    @endphp

    <div class="card border-0 shadow-sm-app overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Référence & Titre</th>
                        <th>Client</th>
                        <th>Net à payer</th>
                        <th>Restant</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($invoices as $invoice)
                    @php $sc = $statusConfig[$invoice->status] ?? ['label' => $invoice->status, 'class' => 'badge-soft-secondary']; @endphp
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info-subtle text-info rounded-3 p-2 d-flex align-items-center justify-content-center me-3 shadow-sm-app" style="width: 42px; height: 42px;">
                                    <i class="bi bi-file-earmark-check-fill fs-5"></i>
                                </div>
                                <div>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none fw-bold text-dark d-block mb-0 hov-primary">
                                        {{ Str::limit($invoice->title, 35) }}
                                    </a>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="text-muted font-monospace small" style="font-size: 0.7rem">{{ $invoice->reference }}</span>
                                        <span class="badge bg-light text-muted border-0 fw-normal" style="font-size: 0.65rem">{{ strtoupper($invoice->type) }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium text-dark small">{{ $invoice->client?->name ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">
                                {{ number_format($invoice->net_to_pay, 0, ',', ' ') }}
                                <small class="text-muted fw-normal">MGA</small>
                            </div>
                        </td>
                        <td>
                            @if($invoice->amount_remaining > 0)
                                <div class="fw-bold text-danger">
                                    {{ number_format($invoice->amount_remaining, 0, ',', ' ') }}
                                    <small class="opacity-75 fw-normal">MGA</small>
                                </div>
                            @else
                                <span class="badge badge-soft-success">Soldé</span>
                            @endif
                        </td>
                        <td>
                            <div class="small {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paye' ? 'text-danger fw-bold' : 'text-muted' }}">
                                <i class="bi bi-calendar-x me-1"></i>
                                {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                            </div>
                        </td>
                        <td>
                            <span class="badge rounded-pill {{ $sc['class'] }} px-3 py-2">
                                {{ $sc['label'] }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn-action-view" title="Détails">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('invoices.edit')
                                    @if($invoice->status === 'brouillon')
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn-action-edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                @endcan
                                @can('invoices.delete')
                                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                                      onsubmit="return confirm('Supprimer cette facture ?')">
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
                                <h5 class="text-muted">Aucune facture trouvée</h5>
                                <p class="text-muted small">Vos factures émises apparaîtront ici pour le suivi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer bg-white py-3 border-top border-light">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</x-layouts.app>
