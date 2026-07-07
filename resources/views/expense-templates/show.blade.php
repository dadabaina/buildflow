<x-layouts.app :title="$expenseTemplate->name">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expense-templates.index') }}" class="text-decoration-none opacity-50 text-dark">Modèles de dépense</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $expenseTemplate->name }}</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h5 class="mb-1 fw-bold">{{ $expenseTemplate->name }}</h5>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-info text-dark">{{ $expenseTemplate->output_quantity }} {{ $expenseTemplate->output_unit }} / application</span>
                <span class="badge {{ $expenseTemplate->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $expenseTemplate->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            @if($expenseTemplate->description)
            <p class="text-muted small mt-1 mb-0">{{ $expenseTemplate->description }}</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            @can('expense_templates.edit')
            <a href="{{ route('expense-templates.edit', $expenseTemplate) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            @endcan
            @can('expense_templates.delete')
            <form method="POST" action="{{ route('expense-templates.destroy', $expenseTemplate) }}"
                  onsubmit="return confirm('Supprimer ce modèle ?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
            </form>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        {{-- Lignes du modèle --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Composition du sous-détail de prix</h6>
                    @can('expense_templates.edit')
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addItem">
                        <i class="bi bi-plus"></i> Ajouter une ligne
                    </button>
                    @endcan
                </div>

                @can('expense_templates.edit')
                <div class="collapse" id="addItem">
                    <div class="card-body border-bottom bg-light">
                        <form method="POST" action="{{ route('expense-templates.items.store', $expenseTemplate) }}"
                              x-data="{
                                  type: 'material',
                                  get isMaterial()    { return this.type === 'material'; },
                                  get isLabor()       { return this.type === 'labor'; },
                                  get isPriced()      { return this.type === 'equipment' || this.type === 'subcontract' || this.type === 'other'; }
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
                                        <option value="other">Autre / Divers</option>
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

                                {{-- Description --}}
                                <div class="col-md-4">
                                    <label class="form-label form-label-sm">
                                        Description
                                        <span x-show="!isMaterial" class="text-danger">*</span>
                                        <span x-show="isMaterial" class="text-muted small">(optionnel si matériau sélectionné)</span>
                                    </label>
                                    <input type="text" name="description" class="form-control form-control-sm"
                                           :placeholder="isMaterial ? 'Laisser vide si matériau sélectionné' : 'Conducteur d\'engin, Location pelle…'">
                                </div>

                                {{-- Catégorie de dépense --}}
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">Catégorie de dépense</label>
                                    <select name="expense_category_id" class="form-select form-select-sm">
                                        <option value="">—</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Unité + Qté + Perte --}}
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Unité *</label>
                                    <input type="text" name="unit" class="form-control form-control-sm"
                                           placeholder="kg, j, h…" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">Qté / {{ $expenseTemplate->output_unit }} *</label>
                                    <input type="number" name="quantity_per_unit" class="form-control form-control-sm"
                                           step="0.0001" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label form-label-sm">Perte %</label>
                                    <input type="number" name="waste_rate" class="form-control form-control-sm"
                                           step="0.1" min="0" max="100" value="0">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
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
                                <th class="text-end">Qté / {{ $expenseTemplate->output_unit }}</th>
                                <th>Unité</th>
                                <th class="text-end">Perte %</th>
                                <th class="text-end">Qté effective</th>
                                <th>Source</th>
                                <th>Prix unitaire (Ar)</th>
                                <th>Catégorie</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenseTemplate->items as $item)
                            @php
                            $typeConfig = [
                                'material'   => ['label' => 'Matériau',      'class' => 'bg-primary'],
                                'labor'      => ['label' => 'M.O.',          'class' => 'bg-success'],
                                'equipment'  => ['label' => 'Matériel',      'class' => 'bg-warning text-dark'],
                                'subcontract'=> ['label' => 'S/Traitance',   'class' => 'bg-info text-dark'],
                                'other'      => ['label' => 'Autre',        'class' => 'bg-dark'],
                            ];
                            $tc = $typeConfig[$item->item_type] ?? ['label' => $item->item_type, 'class' => 'bg-secondary'];

                            if ($item->material_id && $item->material) {
                                $priceSource = '<i class="bi bi-boxes text-primary"></i> Bibliothèque';
                            } elseif ($item->item_type === 'labor' && $item->job_type_id && $item->jobType) {
                                $priceSource = '<i class="bi bi-person-badge text-success"></i> ' . e($item->jobType->name);
                            } else {
                                $priceSource = '<span class="text-muted"><i class="bi bi-pencil"></i> Manuel</span>';
                            }

                            $priceInfo = $prices[$item->id] ?? null;
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
                                    @if($priceInfo && $priceInfo['has_price'])
                                    <div class="fw-medium small mb-1 text-success">
                                        <i class="bi bi-check-circle"></i> {{ number_format($priceInfo['unit_price'], 0, ',', ' ') }} Ar
                                    </div>
                                    @else
                                    <div class="text-danger small mb-1">
                                        <i class="bi bi-exclamation-triangle"></i> Manquant
                                    </div>
                                    @endif
                                    @can('expense_templates.edit')
                                    <form method="POST" action="{{ route('expense-templates.items.update-price', [$expenseTemplate, $item]) }}" class="d-flex gap-1">
                                        @csrf @method('PATCH')
                                        <input type="number" name="unit_price" class="form-control form-control-sm" style="width: 100px"
                                               step="0.01" min="0" placeholder="Prix secours"
                                               value="{{ old('unit_price', $item->unit_price) }}"
                                               title="Utilisé uniquement si aucun prix n'est trouvé dans la bibliothèque/grille salariale">
                                        <button class="btn btn-sm btn-outline-primary" title="Enregistrer le prix de secours">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                                <td class="small">{{ $item->expenseCategory?->name ?? '—' }}</td>
                                <td>
                                    @can('expense_templates.edit')
                                    <form method="POST" action="{{ route('expense-templates.items.destroy', [$expenseTemplate, $item]) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-x"></i></button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3 small">
                                    Aucune ligne — ajoutez des matériaux, de la main d'œuvre, du matériel, de la sous-traitance ou une ligne « Autre »
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Simulateur de coût réel --}}
        <div class="col-lg-4">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-calculator me-2"></i>Simulateur de coût</h6>
                </div>
                <div class="card-body" x-data="costSimulator()">
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">Quantité réelle</label>
                        <div class="input-group input-group-sm">
                            <input type="number" x-model="quantity" class="form-control" step="0.001" min="0"
                                   placeholder="Ex: 12">
                            <span class="input-group-text">{{ $expenseTemplate->output_unit }}</span>
                        </div>
                        <div class="form-text">Ce simulateur n'applique aucune marge : c'est un coût réel, pas un prix de vente.</div>
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
                                <template x-for="line in (result.breakdown || [])" :key="line.expense_template_item_id">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted" x-text="line.description"></span>
                                        <span x-text="fmt(line.line_cost)"></span>
                                    </div>
                                </template>
                                <div class="d-flex justify-content-between fw-bold fs-6 border-top pt-1 mt-2 text-primary">
                                    <span>Coût total estimé</span>
                                    <span x-text="fmt(result.total)"></span>
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
function costSimulator() {
    return {
        quantity: null,
        loading: false,
        result: null,
        async calculate() {
            if (!this.quantity) return;
            this.loading = true;
            this.result = null;
            try {
                const res = await fetch('/expense-templates/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        expense_template_id: {{ $expenseTemplate->id }},
                        quantity: parseFloat(this.quantity),
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
