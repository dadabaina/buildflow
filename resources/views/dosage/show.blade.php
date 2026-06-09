<x-layouts.app :title="$dosage->name">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('dosage.index') }}" class="text-decoration-none opacity-50 text-dark">Dosages</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $dosage->name }}</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="mb-1 fw-bold">{{ $dosage->name }}</h5>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-info text-dark">{{ $dosage->output_quantity }} {{ $dosage->output_unit }} / application</span>
                <span class="badge {{ $dosage->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $dosage->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            @if($dosage->description)
            <p class="text-muted small mt-1 mb-0">{{ $dosage->description }}</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            @can('dosage.edit')
            <a href="{{ route('dosage.edit', $dosage) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            @endcan
            @can('dosage.delete')
            <form method="POST" action="{{ route('dosage.destroy', $dosage) }}"
                  onsubmit="return confirm('Supprimer ce modèle ?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
            </form>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        {{-- Lignes du dosage --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Composition du dosage</h6>
                    @can('dosage.edit')
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addItem">
                        <i class="bi bi-plus"></i> Ajouter une ligne
                    </button>
                    @endcan
                </div>

                @can('dosage.edit')
                <div class="collapse" id="addItem">
                    <div class="card-body border-bottom bg-light">
                        <form method="POST" action="{{ route('dosage.items.store', $dosage) }}"
                              x-data="{
                                  type: 'material',
                                  get isMaterial()    { return this.type === 'material'; },
                                  get isLabor()       { return this.type === 'labor'; },
                                  get isPriced()      { return this.type === 'equipment' || this.type === 'subcontract'; }
                              }">
                            @csrf
                            <div class="row g-2">
                                {{-- Type --}}
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">Type</label>
                                    <select name="item_type" class="form-select form-select-sm" x-model="type">
                                        <option value="material">Matériau</option>
                                        <option value="labor">Main d'œuvre</option>
                                        <option value="equipment">Matériel</option>
                                        <option value="subcontract">Sous-traitance</option>
                                    </select>
                                </div>

                                {{-- Matériau (material uniquement) --}}
                                <div class="col-md-4" x-show="isMaterial">
                                    <label class="form-label form-label-sm">Matériau</label>
                                    <select name="material_id" class="form-select form-select-sm"
                                            :disabled="!isMaterial">
                                        <option value="">— Saisie libre —</option>
                                        @foreach($materials as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Métier (labor uniquement) → Grille salariale --}}
                                <div class="col-md-4" x-show="isLabor">
                                    <label class="form-label form-label-sm">Métier (grille salariale)</label>
                                    <select name="job_type_id" class="form-select form-select-sm"
                                            :disabled="!isLabor">
                                        <option value="">— Saisie libre (sans tarif auto) —</option>
                                        @foreach($jobTypes as $jt)
                                        <option value="{{ $jt->id }}">{{ $jt->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Prix direct (equipment / subcontract) --}}
                                <div class="col-md-4" x-show="isPriced">
                                    <label class="form-label form-label-sm">Prix unitaire (Ar)</label>
                                    <input type="number" name="unit_price" class="form-control form-control-sm"
                                           step="0.01" min="0" placeholder="Ex: 30000"
                                           :disabled="!isPriced">
                                </div>

                                {{-- Description (unique, toujours présente) --}}
                                <div class="col-md-5">
                                    <label class="form-label form-label-sm">
                                        Description
                                        <span x-show="!isMaterial" class="text-danger">*</span>
                                        <span x-show="isMaterial" class="text-muted small">(optionnel si matériau sélectionné)</span>
                                    </label>
                                    <input type="text" name="description" class="form-control form-control-sm"
                                           :placeholder="isMaterial ? 'Laisser vide si matériau sélectionné' : 'Maçon qualifié, Bétonnière 350L…'">
                                </div>

                                {{-- Unité + Qté + Perte --}}
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Unité *</label>
                                    <input type="text" name="unit" class="form-control form-control-sm"
                                           placeholder="kg, j, h…" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">Qté / {{ $dosage->output_unit }} *</label>
                                    <input type="number" name="quantity_per_unit" class="form-control form-control-sm"
                                           step="0.0001" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Perte %</label>
                                    <input type="number" name="waste_rate" class="form-control form-control-sm"
                                           step="0.1" min="0" max="100" value="0">
                                </div>
                                <div class="col-md-5 d-flex align-items-end">
                                    <button class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-plus me-1"></i>Ajouter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endcan

                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Qté / {{ $dosage->output_unit }}</th>
                                <th>Unité</th>
                                <th class="text-end">Perte %</th>
                                <th class="text-end">Qté effective</th>
                                <th>Source prix</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dosage->items as $item)
                            @php
                            $typeConfig = [
                                'material'   => ['label' => 'Matériau',      'class' => 'bg-primary'],
                                'labor'      => ['label' => 'M.O.',          'class' => 'bg-success'],
                                'equipment'  => ['label' => 'Matériel',      'class' => 'bg-warning text-dark'],
                                'subcontract'=> ['label' => 'S/Traitance',   'class' => 'bg-info text-dark'],
                            ];
                            $tc = $typeConfig[$item->item_type] ?? ['label' => $item->item_type, 'class' => 'bg-secondary'];

                            // Source du prix
                            if ($item->material_id && $item->material) {
                                $priceSource = '<i class="bi bi-boxes text-primary"></i> Bibliothèque';
                            } elseif ($item->item_type === 'labor' && $item->job_type_id && $item->jobType) {
                                $priceSource = '<i class="bi bi-person-badge text-success"></i> ' . e($item->jobType->name);
                            } elseif ($item->unit_price !== null) {
                                $priceSource = '<i class="bi bi-tag text-warning"></i> ' . number_format($item->unit_price, 0, ',', ' ') . ' Ar';
                            } else {
                                $priceSource = '<span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Manquant</span>';
                            }
                            @endphp
                            <tr>
                                <td><span class="badge {{ $tc['class'] }} small">{{ $tc['label'] }}</span></td>
                                <td class="small">{{ $item->display_name }}</td>
                                <td class="text-end small">{{ $item->quantity_per_unit }}</td>
                                <td class="small">{{ $item->display_unit }}</td>
                                <td class="text-end small">{{ $item->waste_rate > 0 ? $item->waste_rate.'%' : '—' }}</td>
                                <td class="text-end small fw-medium">{{ number_format($item->effectiveQuantityPerUnit(), 4) }}</td>
                                <td class="small">{!! $priceSource !!}</td>
                                <td>
                                    @can('dosage.edit')
                                    <form method="POST" action="{{ route('dosage.items.destroy', [$dosage, $item]) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-x"></i></button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3 small">
                                    Aucune ligne — ajoutez des matériaux ou de la main d'œuvre
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Simulateur DBE --}}
        <div class="col-lg-4">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-calculator me-2"></i>Simulateur DBE</h6>
                </div>
                <div class="card-body" x-data="dbeSimulator()">
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">Quantité d'ouvrage</label>
                        <div class="input-group input-group-sm">
                            <input type="number" x-model="quantity" class="form-control" step="0.001" min="0"
                                   placeholder="Ex: 10">
                            <span class="input-group-text">{{ $dosage->output_unit }}</span>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label form-label-sm text-muted">FG %</label>
                            <input type="number" x-model="fg" class="form-control form-control-sm" step="0.1" min="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label form-label-sm text-muted">Marge %</label>
                            <input type="number" x-model="margin" class="form-control form-control-sm" step="0.1" min="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label form-label-sm text-muted">Aléas %</label>
                            <input type="number" x-model="alea" class="form-control form-control-sm" step="0.1" min="0">
                        </div>
                    </div>

                    <button @click="calculate" class="btn btn-primary btn-sm w-100 mb-3"
                            :disabled="loading || !quantity">
                        <span x-show="!loading"><i class="bi bi-play me-1"></i>Calculer</span>
                        <span x-show="loading">Calcul...</span>
                    </button>

                    <template x-if="result">
                        <div>
                            <hr class="my-2">
                            <div class="small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">DBE Matériaux</span>
                                    <span x-text="fmt(result.dbe_materials)"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">DBE Main d'œuvre</span>
                                    <span x-text="fmt(result.dbe_labor)"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">DBE Matériel</span>
                                    <span x-text="fmt(result.dbe_equipment)"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">DBE Sous-traitance</span>
                                    <span x-text="fmt(result.dbe_subcontract)"></span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold border-top pt-1 mb-2">
                                    <span>DBE Total</span>
                                    <span x-text="fmt(result.dbe_total)"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">DBE unitaire</span>
                                    <span x-text="fmt(result.dbe_unit)"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Coefficient K</span>
                                    <span x-text="result.coefficient"></span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-6 border-top pt-1 text-primary">
                                    <span>PV unitaire</span>
                                    <span x-text="fmt(result.unit_price)"></span>
                                </div>
                            </div>
                            <template x-if="result.missing_prices && result.missing_prices.length > 0">
                                <div class="alert alert-warning small mt-2 py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Prix manquants :
                                    <span x-text="result.missing_prices.join(', ')"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
function dbeSimulator() {
    return {
        quantity: null,
        fg: 0,
        margin: 0,
        alea: 0,
        loading: false,
        result: null,
        async calculate() {
            if (!this.quantity) return;
            this.loading = true;
            this.result = null;
            try {
                const res = await fetch('/dosage/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        dosage_model_id: {{ $dosage->id }},
                        quantity: parseFloat(this.quantity),
                        fg_rate: parseFloat(this.fg) || 0,
                        margin_rate: parseFloat(this.margin) || 0,
                        alea_rate: parseFloat(this.alea) || 0,
                    }),
                });
                this.result = await res.json();
            } catch(e) {
                alert('Erreur lors du calcul.');
            } finally {
                this.loading = false;
            }
        },
        fmt(val) {
            if (val === null || val === undefined) return '—';
            return new Intl.NumberFormat('fr-MG').format(Math.round(val));
        }
    }
}
</script>
@endpush
</x-layouts.app>
