<x-layouts.app :title="$expense->reference">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}" class="text-decoration-none opacity-50 text-dark">Dépenses</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $expense->reference }}</li>
    </x-slot>

    @php
    $statusConfig = [
        'saisie'  => ['label' => 'Saisie',   'class' => 'badge-soft-secondary', 'icon' => 'bi-pencil'],
        'validee' => ['label' => 'Validée',  'class' => 'badge-soft-success',   'icon' => 'bi-check-circle-fill'],
        'rejetee' => ['label' => 'Rejetée',  'class' => 'badge-soft-danger',    'icon' => 'bi-x-circle-fill'],
    ];
    $sc = $statusConfig[$expense->status] ?? ['label' => $expense->status, 'class' => 'badge-soft-secondary', 'icon' => 'bi-info-circle'];
    @endphp

    {{-- Hero Section --}}
    <div class="card border-0 shadow-sm-app rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-0" id="tour-expense-details">
            <div class="p-4 p-md-5 position-relative" style="background: linear-gradient(135deg, #1e2a35 0%, #2c3e50 100%); color: white;">
                <div class="position-relative z-index-1">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <span class="badge rounded-pill {{ $sc['class'] }} bg-opacity-20 text-white border border-white border-opacity-25 px-3 py-2 mb-3">
                                <i class="{{ $sc['icon'] }} me-1"></i> {{ $sc['label'] }}
                            </span>
                            <h2 class="fw-bold mb-1 text-white">{{ $expense->description }}</h2>
                            <p class="opacity-75 mb-0">
                                <i class="bi bi-hash me-1"></i>{{ $expense->reference }} 
                                <span class="mx-2">·</span> 
                                <i class="bi bi-tag me-1"></i>{{ $expense->expenseCategory?->name ?? 'Catégorie non définie' }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($expense->status === 'saisie')
                                @can('expenses.validate')
                                <form method="POST" action="{{ route('expenses.validate', $expense) }}">
                                    @csrf @method('PATCH')
                                    <button id="tour-expense-validate" class="btn btn-success fw-bold px-4 shadow-sm">
                                        <i class="bi bi-check-lg me-2"></i>Valider la dépense
                                    </button>
                                </form>
                                @endcan
                                @can('expenses.reject')
                                <form method="POST" action="{{ route('expenses.reject', $expense) }}"
                                      onsubmit="return injectRejectionReason(this)">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="rejection_reason">
                                    <button class="btn btn-outline-light px-4 border-opacity-25 fw-bold">
                                        <i class="bi bi-x-lg me-2 text-danger"></i>Rejeter
                                    </button>
                                </form>
                                @endcan
                            @endif
                            @can('expenses.edit')
                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-white fw-bold px-4 text-dark shadow-sm">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>Modifier
                            </a>
                            @endcan
                            <div class="dropdown">
                                <button class="btn btn-outline-light px-3 border-opacity-25" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                    @if($expense->attachment_path)
                                    <li><a class="dropdown-item py-2" href="{{ Storage::url($expense->attachment_path) }}" target="_blank"><i class="bi bi-paperclip me-2 text-muted"></i> Voir justificatif</a></li>
                                    @endif
                                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-printer me-2 text-muted"></i> Imprimer</a></li>
                                    @can('expenses.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Supprimer cette dépense ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Supprimer</button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Background decorative element --}}
                <div class="position-absolute bottom-0 end-0 p-0 d-none d-lg-block text-white" style="pointer-events: none; opacity: 0.07; filter: blur(8px);">
                    <i class="bi bi-receipt-cutoff" style="font-size: 15rem; line-height: 1; margin-bottom: -4rem; margin-right: -2rem;"></i>
                </div>
            </div>
            
            {{-- Quick Stats Summary --}}
            <div class="bg-white p-4 border-top border-light">
                <div class="row g-4 text-center text-md-start">
                    <div class="col-6 col-md-4 border-end border-light">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                            <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-cash fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium text-uppercase">Montant Total TTC</div>
                                <div class="fw-bold text-dark fs-5">{{ number_format($expense->total_amount, 0, ',', ' ') }} <small class="text-muted" style="font-size: 0.65rem">MGA</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 border-end border-light">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                            <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-calendar-event fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium text-uppercase">Date de dépense</div>
                                <div class="fw-bold text-dark fs-5">{{ $expense->expense_date?->format('d M Y') ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3">
                            <div class="bg-success-subtle text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="bi bi-building fs-5"></i>
                            </div>
                            <div class="text-truncate">
                                <div class="text-muted small fw-medium text-uppercase">Chantier rattaché</div>
                                @if($expense->project)
                                <a href="{{ route('projects.show', $expense->project) }}" class="text-decoration-none fw-bold text-dark fs-6 d-block text-truncate">
                                    {{ $expense->project->name }}
                                </a>
                                @else
                                <div class="fw-bold text-muted fs-6 small">Aucun chantier</div>
                                @endif
                                @if($expense->task)
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-diagram-3 me-1"></i>
                                    <a href="{{ route('tasks.show', $expense->task) }}" class="text-decoration-none">{{ $expense->task->title }}</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Détails Administratifs --}}
        <div class="col-lg-7">
            <x-card title="Informations détaillées" icon="bi bi-info-square" class="h-100">
                <div class="row g-4">
                    <div class="col-sm-6">
                        <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Fournisseur / Tiers</label>
                        @if($expense->supplier)
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-light rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-shop text-primary"></i>
                                </div>
                                <a href="{{ route('suppliers.show', $expense->supplier) }}" class="text-decoration-none fw-bold text-dark">
                                    {{ $expense->supplier->name }}
                                </a>
                            </div>
                        @else
                            <span class="badge bg-light text-secondary px-3 py-2 rounded-pill fw-medium">Pas de fournisseur spécifié</span>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Créé par</label>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary-subtle text-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                {{ substr($expense->creator?->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="fw-medium text-dark">{{ $expense->creator?->name ?? 'Système' }}</span>
                        </div>
                    </div>
                    @if($expense->notes)
                    <div class="col-12 mt-4">
                        <label class="text-muted small text-uppercase fw-bold mb-2 d-block">Notes & Observations</label>
                        <div class="p-3 bg-light rounded-3 text-secondary border-0" style="white-space: pre-line;">{{ $expense->notes }}</div>
                    </div>
                    @endif

                    @if($expense->attachment_path)
                    <div class="col-12">
                        <label class="text-muted small text-uppercase fw-bold mb-2 d-block">Document justificatif</label>
                        <div class="d-flex align-items-center p-3 border rounded-3 bg-white">
                            <i class="bi bi-file-earmark-pdf fs-3 text-danger me-3"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark small mb-0">Pièce jointe scannée</div>
                                <div class="text-muted" style="font-size: 0.7rem">Format PDF / Image</div>
                            </div>
                            <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                                <i class="bi bi-download me-1"></i> Consulter
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- Récapitulatif Financier --}}
        <div class="col-lg-5">
            <x-card title="Détail Financier" icon="bi bi-calculator" class="h-100 shadow-sm border-0">
                <div class="p-3 bg-primary-subtle rounded-4 mb-4">
                    <div class="text-primary small fw-bold text-uppercase mb-1">Total à décaisser</div>
                    <div class="h2 fw-bold text-primary mb-0">{{ number_format($expense->total_amount, 0, ',', ' ') }} <small class="fs-6">MGA</small></div>
                </div>
                
                <ul class="list-group list-group-flush border-0">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
                        <span class="text-muted">Quantité / Volume</span>
                        <span class="fw-bold text-dark">{{ $expense->quantity }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
                        <span class="text-muted">Prix Unitaire HT</span>
                        <span class="fw-bold text-dark">{{ number_format($expense->amount, 0, ',', ' ') }} MGA</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
                        <span class="text-muted">TVA ({{ $expense->tax_rate }}%)</span>
                        @php $taxAmount = $expense->total_amount - ($expense->amount * $expense->quantity); @endphp
                        <span class="fw-bold text-dark">+ {{ number_format($taxAmount, 0, ',', ' ') }} MGA</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent border-top border-2">
                        <span class="fw-bold text-dark">Montant Total TTC</span>
                        <span class="fw-bold text-primary fs-5">{{ number_format($expense->total_amount, 0, ',', ' ') }} MGA</span>
                    </li>
                </ul>

                <div class="mt-4 p-3 border-start border-4 border-info bg-light rounded-end-3">
                    <div class="small text-muted"><i class="bi bi-info-circle me-1"></i> Imputation budgétaire</div>
                    <div class="fw-bold small text-dark mt-1">Section : {{ $expense->expenseCategory?->name ?? 'Général' }}</div>
                </div>
            </x-card>
        </div>
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
