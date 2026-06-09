<x-layouts.app :title="$supplier->name">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none opacity-50 text-dark">Fournisseurs</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $supplier->name }}</li>
    </x-slot>

    @php
    $typeLabels = ['fournisseur' => 'Fournisseur', 'sous_traitant' => 'Sous-traitant', 'les_deux' => 'Fournisseur + S/T'];
    @endphp

    <!-- Header & Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bx bx-truck fs-1"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h4 class="mb-0 fw-bold">{{ $supplier->name }}</h4>
                                    <span class="badge {{ $supplier->is_active ? 'bg-success' : 'bg-secondary' }} badge-sm">
                                        {{ $supplier->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge bg-label-info text-uppercase small">{{ $typeLabels[$supplier->type] ?? $supplier->type }}</span>
                                    <span class="text-muted small"><i class="bx bx-map me-1"></i>{{ $supplier->city ?? '—' }}</span>
                                    <span class="text-muted small"><i class="bx bx-phone me-1"></i>{{ $supplier->phone ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @can('suppliers.edit')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal">
                                <i class="bx bx-edit-alt me-1"></i>Modifier
                            </button>
                            @endcan
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-plus me-1"></i>Nouveau
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}"><i class="bx bx-file me-2"></i>Bon de commande</a></li>
                                    <li><a class="dropdown-item" href="{{ route('expenses.create', ['supplier_id' => $supplier->id]) }}"><i class="bx bx-receipt me-2"></i>Facture / Dépense</a></li>
                                </ul>
                            </div>
                            @can('suppliers.delete')
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Supprimer ce partenaire ?')">
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Total engagé</p>
                            <h4 class="mb-0 fw-bold mt-1 text-nowrap">{{ number_format($stats['total_spent'], 0, ',', ' ') }} <small class="fs-6 fw-normal">Ar</small></h4>
                        </div>
                        <div class="avatar bg-label-danger rounded p-2">
                            <i class="bx bx-cart fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Commandes</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['orders_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-package fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">En attente</p>
                            <h4 class="mb-0 fw-bold mt-1 text-warning">{{ $stats['pending_orders'] }}</h4>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-time fs-3"></i>
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
                            <p class="mb-0 text-muted small text-uppercase fw-semibold">Effectif</p>
                            <h4 class="mb-0 fw-bold mt-1">{{ $stats['employees_count'] }}</h4>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-group fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar: Details -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Détails du partenaire</h6>
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
                                    <small class="text-muted">Contact principal</small>
                                    <span class="fw-medium text-dark">{{ $supplier->contact_name ?? '—' }}</span>
                                </div>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center">
                                    <i class="bx bx-envelope fs-5"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Email</small>
                                    <span class="fw-medium text-dark text-truncate" style="max-width: 200px;">{{ $supplier->email ?? '—' }}</span>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <div class="avatar avatar-sm bg-label-primary rounded me-3 d-flex align-items-center justify-content-center mt-1">
                                    <i class="bx bx-map fs-5"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Adresse</small>
                                    <span class="fw-medium text-dark">
                                        {{ $supplier->address ?? 'Pas d\'adresse' }}<br>
                                        {{ $supplier->city ?? '' }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mb-0">
                        <small class="text-uppercase text-muted fw-semibold d-block mb-3">Identification fiscale</small>
                        <div class="bg-light p-3 rounded-3 border border-dashed text-center">
                            <small class="d-block text-muted mb-1">NIF</small>
                            <span class="fw-bold text-dark fs-5">{{ $supplier->nif ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($supplier->notes)
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-transparent py-3">
                    <h6 class="mb-0 fw-bold">Notes internes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 small text-muted" style="white-space: pre-line; line-height: 1.6;">{{ $supplier->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs border-0 shadow-sm bg-white rounded-top" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-orders">
                            <i class="bx bx-package me-2"></i> Commandes
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-expenses">
                            <i class="bx bx-receipt me-2"></i> Factures
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link py-3 px-4" role="tab" data-bs-toggle="tab" data-bs-target="#tab-employees">
                            <i class="bx bx-group me-2"></i> Effectif S/T
                        </button>
                    </li>
                </ul>
                <div class="tab-content border-0 shadow-sm p-0 rounded-bottom overflow-hidden bg-white">
                    <!-- Orders Tab -->
                    <div class="tab-pane fade show active" id="tab-orders" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Réf / Projet</th>
                                        <th class="py-3">Statut</th>
                                        <th class="text-end py-3">Montant TTC</th>
                                        <th class="text-end pe-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($supplier->purchaseOrders as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark">{{ $order->reference }}</span>
                                                <small class="text-muted">{{ $order->project?->name ?? 'Sans projet' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $order->status_badge_class }} badge-sm">
                                                {{ $order->status_libelle }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($order->total_ttc, 0, ',', ' ') }} Ar</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('purchase-orders.show', $order) }}" class="btn-action-view">
                                                <i class="bx bx-show fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Aucune commande enregistrée.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Expenses Tab -->
                    <div class="tab-pane fade" id="tab-expenses" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Date / Description</th>
                                        <th class="py-3">Statut</th>
                                        <th class="text-end py-3">Montant</th>
                                        <th class="text-end pe-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($supplier->expenses as $expense)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-medium text-dark">{{ Str::limit($expense->description, 40) }}</div>
                                            <small class="text-muted">{{ $expense->expense_date?->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $expense->status === 'validee' ? 'bg-success' : 'bg-warning' }} badge-sm">
                                                {{ ucfirst($expense->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($expense->total_amount, 0, ',', ' ') }} Ar</td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('expenses.show', $expense) }}" class="btn-action-view">
                                                <i class="bx bx-show fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Aucune facture enregistrée.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Employees Tab -->
                    <div class="tab-pane fade" id="tab-employees" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3">Collaborateur</th>
                                        <th class="py-3">Poste</th>
                                        <th class="text-end pe-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($supplier->employees as $emp)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-label-primary me-3">
                                                    <span class="avatar-initial rounded-circle">{{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}</span>
                                                </div>
                                                <span class="fw-bold text-dark">{{ $emp->full_name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-label-secondary badge-sm">{{ $emp->jobType?->name ?? '—' }}</span></td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('employees.show', $emp) }}" class="btn-action-view">
                                                <i class="bx bx-show fs-5"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted">Aucun employé rattaché.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Modifier Fournisseur --}}
    <div class="modal fade" id="supplierModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @csrf @method('PATCH')

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bx bx-truck me-2"></i>Modifier le partenaire
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="fournisseur" {{ old('type', $supplier->type) === 'fournisseur' ? 'selected' : '' }}>Fournisseur</option>
                                    <option value="sous_traitant" {{ old('type', $supplier->type) === 'sous_traitant' ? 'selected' : '' }}>Sous-traitant</option>
                                    <option value="les_deux" {{ old('type', $supplier->type) === 'les_deux' ? 'selected' : '' }}>Fournisseur + S/T</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact principal</label>
                                <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $supplier->contact_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ville</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $supplier->city) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Adresse complète</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $supplier->address) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIF</label>
                                <input type="text" name="nif" class="form-control" value="{{ old('nif', $supplier->nif) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes & Observations</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $supplier->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-check me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
