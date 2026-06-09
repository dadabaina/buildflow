<x-layouts.app title="Paiements">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Paiements</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-cash-coin me-2 text-primary"></i>Paiements reçus
            <span class="badge bg-secondary ms-2">{{ $payments->total() }}</span>
        </h5>
        @can('payments.create')
        <a href="{{ route('payments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau paiement
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Facture</th>
                        <th>Client</th>
                        <th>Méthode</th>
                        <th>Référence</th>
                        <th class="text-end">Montant</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="small">{{ $payment->payment_date?->format('d/m/Y') }}</td>
                        <td>
                            @php $inv = $payment->invoices->first() @endphp
                            @if($inv)
                            <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none font-monospace small">
                                {{ $inv->reference }}
                            </a>
                            @else —
                            @endif
                        </td>
                        <td class="small">{{ $payment->invoices->first()?->client?->name ?? '—' }}</td>
                        <td class="small">{{ $payment->payment_mode ?? '—' }}</td>
                        <td class="small text-muted">{{ $payment->reference ?? '—' }}</td>
                        <td class="text-end fw-medium text-success">{{ number_format($payment->amount, 0, ',', ' ') }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                @can('payments.delete')
                                <form method="POST" action="{{ route('payments.destroy', $payment) }}"
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
                            <i class="bi bi-cash-coin fs-2 d-block mb-2"></i>
                            Aucun paiement enregistré
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
