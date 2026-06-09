<x-layouts.app title="Bons de commande">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Bons de commande</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Bons de commande</h4>
        @can('purchase_orders.create')
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nouveau bon de commande
            </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Référence, fournisseur…" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Tous les chantiers</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous statuts</option>
                        <option value="brouillon" @selected(request('status')=='brouillon')>Brouillon</option>
                        <option value="envoye" @selected(request('status')=='envoye')>Envoyé</option>
                        <option value="partiellement_livre" @selected(request('status')=='partiellement_livre')>Partiellement livré</option>
                        <option value="livre" @selected(request('status')=='livre')>Livré</option>
                        <option value="annule" @selected(request('status')=='annule')>Annulé</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Chantier</th>
                            <th>Fournisseur</th>
                            <th>Date commande</th>
                            <th>Livraison</th>
                            <th class="text-end">Total TTC</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            <tr>
                                <td><a href="{{ route('purchase-orders.show', $po) }}">{{ $po->reference }}</a></td>
                                <td>{{ $po->project->name ?? '-' }}</td>
                                <td>{{ $po->supplier->name ?? '-' }}</td>
                                <td>{{ $po->order_date->format('d/m/Y') }}</td>
                                <td>{{ $po->delivery_date ? $po->delivery_date->format('d/m/Y') : '-' }}</td>
                                <td class="text-end">{{ number_format($po->total_ttc, 2, ',', ' ') }} Ar</td>
                                <td><span class="badge {{ $po->status_badge_class }}">{{ $po->status_libelle }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="btn-action-view" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(in_array($po->status, ['brouillon','envoye']))
                                            <a href="{{ route('purchase-orders.edit', $po) }}" class="btn-action-edit" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @can('delete', $po)
                                            <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}"
                                                  onsubmit="return confirm('Supprimer ce bon de commande ?')">
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
                            <tr><td colspan="8" class="text-center text-muted py-4">Aucun bon de commande trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer">{{ $purchaseOrders->links() }}</div>
        @endif
    </div>
</x-layouts.app>
