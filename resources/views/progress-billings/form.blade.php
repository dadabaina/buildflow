<x-layouts.app :title="isset($progressBilling) ? 'Modifier situation' : 'Nouvelle situation'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('progress-billings.index') }}" class="text-decoration-none opacity-50 text-dark">Situations</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($progressBilling) ? ($progressBilling ?? null)?->reference : 'Nouvelle' }}</li>
    </x-slot>

    <script>
        function billingLines() {
            return {
                lines: {!! json_encode(isset($progressBilling) 
                    ? ($progressBilling ?? null)?->lines?->map(fn($l) => [
                        'description' => $l->description,
                        'quote_quantity' => $l->quote_quantity,
                        'unit' => $l->unit,
                        'unit_price' => $l->unit_price,
                        'previous_pct' => $l->previous_pct,
                        'current_pct' => $l->current_pct,
                        'amount' => $l->current_amount
                    ]) 
                    : [['description' => '', 'quote_quantity' => 0, 'unit' => '', 'unit_price' => 0, 'previous_pct' => 0, 'current_pct' => 0, 'amount' => 0]]
                ) !!},
                add()  { this.lines.push({description:'',quote_quantity:0,unit:'',unit_price:0,previous_pct:0,current_pct:0,amount:0}); },
                remove(i) { this.lines.splice(i, 1); },
                calcAmount(i) {
                    const l = this.lines[i];
                    l.amount = Math.round((l.current_pct / 100) * l.quote_quantity * l.unit_price * 100) / 100;
                },
            };
        }
    </script>

    <form method="POST" action="{{ isset($progressBilling) ? route('progress-billings.update', $progressBilling) : route('progress-billings.store') }}">
        @csrf
        @if(isset($progressBilling)) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <x-card title="Informations" icon="bi bi-bar-chart-steps">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Chantier <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                <option value="">— Sélectionner —</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ old('project_id', ($progressBilling ?? null)?->project_id ?? $selected) == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Devis de référence</label>
                            <select name="quote_id" class="form-select @error('quote_id') is-invalid @enderror">
                                <option value="">— Aucun —</option>
                                @foreach($quotes as $q)
                                    <option value="{{ $q->id }}" {{ old('quote_id', ($progressBilling ?? null)?->quote_id ?? '') == $q->id ? 'selected' : '' }}>{{ $q->reference }}</option>
                                @endforeach
                            </select>
                            @error('quote_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Titre <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', ($progressBilling ?? null)?->title ?? '') }}" required maxlength="200">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Date de situation <span class="text-danger">*</span></label>
                            <input type="date" name="billing_date" class="form-control @error('billing_date') is-invalid @enderror"
                                   value="{{ old('billing_date', ($progressBilling ?? null)?->billing_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                            @error('billing_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Date d'échéance</label>
                            <input type="date" name="due_date" class="form-control"
                                   value="{{ old('due_date', ($progressBilling ?? null)?->due_date?->format('Y-m-d') ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium">TVA (%)</label>
                            <input type="number" name="tva_rate" class="form-control" step="0.01" min="0" max="100"
                                   value="{{ old('tva_rate', ($progressBilling ?? null)?->tva_rate ?? 20) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium">RG (%)</label>
                            <input type="number" name="rg_rate" class="form-control" step="0.01" min="0" max="100"
                                   value="{{ old('rg_rate', ($progressBilling ?? null)?->rg_rate ?? 5) }}"
                                   title="Retenue de garantie">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', ($progressBilling ?? null)?->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </x-card>
            </div>
            <div class="col-lg-4">
                <x-card title="Actions" icon="bi bi-gear">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm-app">
                            <i class="bi bi-check-lg me-2"></i>{{ isset($progressBilling) ? 'Enregistrer' : 'Créer la situation' }}
                        </button>
                        <a href="{{ route('progress-billings.index') }}" class="btn btn-light border">Annuler</a>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Lignes d'avancement --}}
        <div class="mt-4" x-data="billingLines()">
            <x-card title="Tableau d'avancement" icon="bi bi-table">
                <p class="text-muted small mb-3">Indiquez le % d'avancement pour cette situation. Le montant est calculé automatiquement.</p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">Description</th>
                                <th class="border-0" style="width:80px">Qté</th>
                                <th class="border-0" style="width:60px">Unité</th>
                                <th class="border-0" style="width:110px">P.U. HT</th>
                                <th class="border-0" style="width:90px">% préc.</th>
                                <th class="border-0" style="width:90px">% cette sit.</th>
                                <th class="border-0" style="width:110px">Montant HT</th>
                                <th class="border-0" style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, i) in lines" :key="i">
                                <tr>
                                    <td><input type="text" :name="`lines[${i}][description]`" x-model="line.description" class="form-control form-control-sm" required></td>
                                    <td><input type="number" :name="`lines[${i}][quote_quantity]`" x-model="line.quote_quantity" class="form-control form-control-sm" step="0.001" @input="calcAmount(i)"></td>
                                    <td><input type="text" :name="`lines[${i}][unit]`" x-model="line.unit" class="form-control form-control-sm"></td>
                                    <td><input type="number" :name="`lines[${i}][unit_price]`" x-model="line.unit_price" class="form-control form-control-sm" step="0.01" @input="calcAmount(i)"></td>
                                    <td><input type="number" :name="`lines[${i}][previous_pct]`" x-model="line.previous_pct" class="form-control form-control-sm" step="0.01" min="0" max="100" readonly></td>
                                    <td><input type="number" :name="`lines[${i}][current_pct]`" x-model="line.current_pct" class="form-control form-control-sm" step="0.01" min="0" max="100" @input="calcAmount(i)"></td>
                                    <td><span class="fw-bold text-primary small" x-text="Number(line.amount).toLocaleString('fr-FR', {minimumFractionDigits:0}) + ' Ar'"></span></td>
                                    <td><button type="button" @click="remove(i)" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="button" @click="add()" class="btn btn-light border btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                    </button>
                </div>
            </x-card>
        </div>
    </form>
</x-layouts.app>
