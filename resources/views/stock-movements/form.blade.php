<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Stocks</li>
        <li class="breadcrumb-item active fw-bold text-dark">Nouveau mouvement</li>
    </x-slot>

    @php
        $materialsMap = $materials->mapWithKeys(fn($m) => [
            $m->id => [
                'name' => $m->name, 
                'unit' => $m->unit, 
                'price' => $m->currentPrice()
            ]
        ])->toJson();
    @endphp

    <div class="row justify-content-center" 
         x-data="{ 
            type: '{{ old('type', 'entree') }}',
            materialId: '{{ old('material_id', '') }}',
            materials: {{ $materialsMap }},
            itemName: '{{ old('item_name', '') }}',
            unit: '{{ old('unit', 'u') }}',
            unitCost: '{{ old('unit_cost', 0) }}',
            updateFromMaterial() {
                if (this.materialId && this.materials[this.materialId]) {
                    const m = this.materials[this.materialId];
                    this.itemName = m.name;
                    this.unit = m.unit;
                    // Auto-remplir le prix si c'est une entrée ou si le champ est à 0
                    if (this.type === 'entree' || this.unitCost == 0) {
                        this.unitCost = m.price || 0;
                    }
                }
            }
         }"
         x-init="updateFromMaterial()">
        <div class="col-lg-9">
            <x-card title="Nouveau mouvement de stock">
                <form method="POST" action="{{ route('stock-movements.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required x-model="type">
                                <option value="entree">Entrée (Achat/Stock)</option>
                                <option value="sortie">Sortie (Consommation)</option>
                                <option value="transfert">Transfert entre dépôts</option>
                                <option value="ajustement">Ajustement d'inventaire</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Date <span class="text-danger">*</span></label>
                            <input type="date" name="movement_date" class="form-control"
                                   value="{{ old('movement_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" x-text="type === 'transfert' ? 'Dépôt Source *' : 'Dépôt *'"></label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">— Sélectionner un dépôt —</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} {{ $wh->project_id ? '(Chantier)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" x-show="type === 'transfert'" x-cloak>
                            <label class="form-label fw-semibold small text-primary">Dépôt Destination <span class="text-danger">*</span></label>
                            <select name="destination_warehouse_id" class="form-select border-primary" :required="type === 'transfert'">
                                <option value="">— Sélectionner la destination —</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ old('destination_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} {{ $wh->project_id ? '(Chantier)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Les articles seront retirés de la source et ajoutés à la destination.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-info">Matériau du catalogue (Optionnel)</label>
                            <select name="material_id" class="form-select border-info" x-model="materialId" @change="updateFromMaterial()">
                                <option value="">— Hors catalogue —</option>
                                @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Article / Désignation <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control"
                                   x-model="itemName" required list="materials-list" placeholder="Nom de l'article">
                            <datalist id="materials-list">
                                @foreach($materials as $mat)
                                <option value="{{ $mat->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Unité <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" x-model="unit" required placeholder="Ex: m³, kg, u" :class="materialId ? 'bg-light' : ''">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Quantité <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" step="0.001" min="0.001"
                                   value="{{ old('quantity') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Prix unitaire (Ar)</label>
                            <input type="number" name="unit_cost" class="form-control" step="0.01" min="0"
                                   x-model="unitCost">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Référence</label>
                            <input type="text" name="reference" class="form-control"
                                   value="{{ old('reference') }}" placeholder="N° BL, BM...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Chantier associé</label>
                            <select name="project_id" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                        <a href="{{ route('stock-movements.index') }}" class="btn btn-light">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1"></i>Enregistrer le mouvement
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
