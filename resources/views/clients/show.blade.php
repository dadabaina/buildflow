<x-layouts.app :title="$client->name">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-decoration-none opacity-50 text-dark">Clients</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $client->name }}</li>
    </x-slot>

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx {{ $client->type === 'particulier' ? 'bx-user' : 'bx-buildings' }} fs-1"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">{{ $client->name }}</h4>
                                    <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-secondary' }} badge-sm">
                                        {{ $client->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge bg-label-primary text-uppercase small">{{ $client->type_libelle }}</span>
                                    <span class="text-muted small"><i class="bx bx-hash me-1"></i>{{ $client->reference }}</span>
                                    <span class="text-muted small"><i class="bx bx-map me-1"></i>{{ $client->city ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('clients.edit')
                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">
                                <i class="bx bx-edit-alt me-1"></i>Modifier
                            </a>
                            @endcan
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-plus me-1"></i>Nouveau
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="{{ route('projects.create', ['client_id' => $client->id]) }}"><i class="bx bx-building me-2"></i>Chantier</a></li>
                                    <li><a class="dropdown-item" href="{{ route('quotes.create', ['client_id' => $client->id]) }}"><i class="bx bx-file me-2"></i>Devis</a></li>
                                    <li><a class="dropdown-item" href="{{ route('invoices.create', ['client_id' => $client->id]) }}"><i class="bx bx-receipt me-2"></i>Facture</a></li>
                                </ul>
                            </div>
                            @can('clients.delete')
                            <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Archiver ce client ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-icon">
                                    <i class="bx bx-trash"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Chantiers</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['projects_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Devis acceptés</p>
                            <h4 class="mb-0 fw-bold mt-1 text-nowrap">{{ number_format($stats['total_quotes'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-check-double fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total facturé</p>
                            <h4 class="mb-0 fw-bold mt-1 text-nowrap">{{ number_format($stats['total_invoiced'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-receipt fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Reste à payer</p>
                            <h4 class="mb-0 fw-bold mt-1 text-nowrap {{ $stats['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($stats['balance'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small>
                            </h4>
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
        <!-- Sidebar: Client Details -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Détails du client</h6>
                </div>
                <div class="card-body py-4">
                    <div class="mb-4">
                        <small class="text-uppercase text-muted fw-semibold d-block mb-3">Informations de contact</small>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                    <i class="bx bx-user fs-5"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Nom du contact</small>
                                    <span class="fw-medium text-dark">{{ $client->contact_name ?? '—' }}</span>
                                </div>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                    <i class="bx bx-phone fs-5"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Téléphone</small>
                                    <span class="fw-medium text-dark">{{ $client->phone ?? '—' }} {{ $client->phone2 ? ' / ' . $client->phone2 : '' }}</span>
                                </div>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                    <i class="bx bx-envelope fs-5"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Email</small>
                                    <span class="fw-medium text-dark text-truncate" style="max-width: 200px;">{{ $client->email ?? '—' }}</span>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center mt-1">
                                    <i class="bx bx-map fs-5"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Adresse</small>
                                    <span class="fw-medium text-dark">
                                        {{ $client->address ?? 'Pas d\'adresse' }}<br>
                                        {{ $client->city ?? '' }} {{ $client->region?->name ? '('.$client->region->name.')' : '' }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mb-0">
                        <small class="text-uppercase text-muted fw-semibold d-block mb-3">Identifiants fiscaux</small>
                        <div class="bg-light p-3 rounded-3 border border-dashed">
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="d-block text-muted mb-1">NIF</small>
                                    <span class="fw-bold text-dark">{{ $client->nif ?? '—' }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-muted mb-1">STAT</small>
                                    <span class="fw-bold text-dark">{{ $client->stat ?? '—' }}</span>
                                </div>
                                <div class="col-12">
                                    <small class="d-block text-muted mb-1">RCS</small>
                                    <span class="fw-bold text-dark">{{ $client->rcs ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($client->notes)
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Notes internes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">{{ $client->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content: Tabs -->
        <div class="col-lg-8">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs border-0 shadow-sm bg-white rounded-top" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-projects">
                            <i class="bx bx-building me-2"></i> Chantiers
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-quotes">
                            <i class="bx bx-file me-2"></i> Devis
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-invoices">
                            <i class="bx bx-receipt me-2"></i> Factures
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-payments">
                            <i class="bx bx-credit-card me-2"></i> Paiements
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
                                        <th class="ps-4 py-3">Référence / Nom</th>
                                        <th class="py-3">Statut</th>
                                        <th class="py-3">Début</th>
                                        <th class="text-end pe-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->projects as $project)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('projects.show', $project) }}" class="fw-bold text-dark text-decoration-none">{{ $project->name }}</a>
                                                <small class="text-muted">{{ $project->reference }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $project->status_badge_class }} badge-sm">
                                                {{ $project->status_libelle }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $project->start_date?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('projects.show', $project) }}" class="btn-action-view">
                                                <i class="bx bx-show fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted opacity-50 mb-2"><i class="bx bx-building fs-1"></i></div>
                                            <p class="mb-0">Aucun chantier enregistré pour ce client.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quotes Tab -->
                    <div class="tab-pane fade" id="tab-quotes" role="tabpanel">
                         <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Référence</th>
                                        <th class="py-3">Titre / Projet</th>
                                        <th class="py-3">Statut</th>
                                        <th class="text-end py-3">Montant TTC</th>
                                        <th class="text-end pe-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->quotes as $quote)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark">{{ $quote->reference }}</span>
                                            <div class="text-muted small">{{ $quote->quote_date?->format('d/m/Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $quote->title }}</div>
                                            <small class="text-muted">{{ $quote->project?->name ?? 'Sans projet' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $quote->status_badge_class }} badge-sm">
                                                {{ $quote->status_libelle }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($quote->total_ttc, 2, ',', ' ') }} <small class="text-muted">Ar</small></td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('quotes.show', $quote) }}" class="btn-action-view">
                                                <i class="bx bx-show fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted opacity-50 mb-2"><i class="bx bx-file fs-1"></i></div>
                                            <p class="mb-0">Aucun devis enregistré pour ce client.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Invoices Tab -->
                    <div class="tab-pane fade" id="tab-invoices" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Référence</th>
                                        <th class="py-3">Statut</th>
                                        <th class="text-end py-3">Montant TTC</th>
                                        <th class="text-end py-3">Reste à payer</th>
                                        <th class="text-end pe-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->invoices as $invoice)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark">{{ $invoice->reference }}</span>
                                            <div class="text-muted small">{{ $invoice->invoice_date?->format('d/m/Y') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $invoice->status_badge_class }} badge-sm">
                                                {{ $invoice->status_libelle }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $invoice->amount_remaining > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($invoice->amount_remaining, 2, ',', ' ') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn-action-view">
                                                <i class="bx bx-show fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted opacity-50 mb-2"><i class="bx bx-receipt fs-1"></i></div>
                                            <p class="mb-0">Aucune facture enregistrée pour ce client.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payments Tab -->
                    <div class="tab-pane fade" id="tab-payments" role="tabpanel">
                         <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Date</th>
                                        <th class="py-3">Référence</th>
                                        <th class="py-3">Mode</th>
                                        <th class="text-end pe-4 py-3">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->payments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-dark fw-medium">{{ $payment->payment_date?->format('d/m/Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $payment->reference }}</span>
                                            @if($payment->project)
                                                <div class="small text-muted">Projet: {{ $payment->project->name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-capitalize small text-dark">{{ str_replace('_', ' ', $payment->payment_mode) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="fw-bold text-success">+ {{ number_format($payment->amount, 2, ',', ' ') }} <small>Ar</small></span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted opacity-50 mb-2"><i class="bx bx-credit-card fs-1"></i></div>
                                            <p class="mb-0">Aucun paiement enregistré pour ce client.</p>
                                        </td>
                                    </tr>
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
