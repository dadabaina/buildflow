<x-layouts.app :title="isset($purchaseOrder) ? 'Modifier BC ' . $purchaseOrder->reference : 'Nouveau bon de commande'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}" class="text-decoration-none opacity-50 text-dark">Bons de commande</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($purchaseOrder) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-check me-2"></i>
                        {{ isset($purchaseOrder) ? 'Modifier le BC ' . $purchaseOrder->reference : 'Nouveau bon de commande' }}
                    </h5>
                </div>

                <form method="POST"
                      action="{{ isset($purchaseOrder) ? route('purchase-orders.update', $purchaseOrder) : route('purchase-orders.store') }}">
                    @csrf
                    @if(isset($purchaseOrder)) @method('PUT') @endif

                    <div class="card-body"
                         x-data="{
                             tva: {{ old('tva_rate', $purchaseOrder->tva_rate ?? 20) }},
                             items: {{ json_encode(
                                 old('items',
                                     isset($purchaseOrder)
                                         ? $purchaseOrder->items->map(fn($i) => [
                                             'description' => $i->description,
                                             'quantity'    => $i->quantity,
                                             'unit'        => $i->unit,
                                             'unit_price'  => $i->unit_price,
                                         ])->toArray()
                                         : [['description'=>'','quantity'=>1,'unit'=>'','unit_price'=>0]]
                                 )
                             ) }},
                             get totalHt() {
                                 return this.items.reduce((s, i) => s + (parseFloat(i.quantity)||0) * (parseFloat(i.unit_price)||0), 0);
                             },
                             get totalTtc() { return this.totalHt * (1 + this.tva/100); },
                             addItem() { this.items.push({description:'',quantity:1,unit:'',unit_price:0}); },
                             removeItem(idx) { if(this.items.length > 1) this.items.splice(idx,1); }
                         }">

                        {{-- Entête --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Chantier <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">— Choisir —</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}"
                                            @selected(old('project_id', $purchaseOrder->project_id ?? $selected) == $p->id)>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">— Choisir —</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}"
                                            @selected(old('supplier_id', $purchaseOrder->supplier_id ?? '') == $sup->id)>
                                            {{ $sup->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date commande <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror"
                                       value="{{ old('order_date', isset($purchaseOrder) ? $purchaseOrder->order_date->format('Y-m-d') : today()->format('Y-m-d')) }}" required>
                                @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date livraison prévue</label>
                                <input type="date" name="delivery_date" class="form-control @error('delivery_date') is-invalid @enderror"
                                       value="{{ old('delivery_date', isset($purchaseOrder) && $purchaseOrder->delivery_date ? $purchaseOrder->delivery_date->format('Y-m-d') : '') }}">
                                @error('delivery_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">TVA (%)</label>
                                <input type="number" name="tva_rate" class="form-control @error('tva_rate') is-invalid @enderror"
                                       x-model="tva" step="0.01" min="0" max="100">
                                @error('tva_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Lignes --}}
                        <h6 class="mb-2">Lignes de commande</h6>
                        <div class="table-responsive mb-2">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th style="width:90px">Qté</th>
                                        <th style="width:80px">Unité</th>
                                        <th style="width:120px">Prix unit.</th>
                                        <th style="width:120px">Total HT</th>
                                        <th style="width:40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, idx) in items" :key="idx">
                                        <tr>
                                            <td>
                                                <input type="text" :name="`items[${idx}][description]`"
                                                       x-model="item.description" class="form-control form-control-sm" required>
                                            </td>
                                            <td>
                                                <input type="number" :name="`items[${idx}][quantity]`"
                                                       x-model="item.quantity" class="form-control form-control-sm text-end"
                                                       step="0.001" min="0">
                                            </td>
                                            <td>
                                                <input type="text" :name="`items[${idx}][unit]`"
                                                       x-model="item.unit" class="form-control form-control-sm">
                                            </td>
                                            <td>
                                                <input type="number" :name="`items[${idx}][unit_price]`"
                                                       x-model="item.unit_price" class="form-control form-control-sm text-end"
                                                       step="0.01" min="0">
                                            </td>
                                            <td class="text-end fw-semibold"
                                                x-text="((parseFloat(item.quantity)||0)*(parseFloat(item.unit_price)||0)).toLocaleString('fr-MG',{minimumFractionDigits:2})">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-xs"
                                                        @click="removeItem(idx)">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3">
                                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addItem()">
                                                <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                                            </button>
                                        </td>
                                        <td class="text-end fw-semibold">Total HT</td>
                                        <td class="text-end fw-bold" x-text="totalHt.toLocaleString('fr-MG',{minimumFractionDigits:2})"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-semibold">TVA (<span x-text="tva"></span> %)</td>
                                        <td class="text-end" x-text="(totalHt*tva/100).toLocaleString('fr-MG',{minimumFractionDigits:2})"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total TTC</td>
                                        <td class="text-end fw-bold text-primary" x-text="totalTtc.toLocaleString('fr-MG',{minimumFractionDigits:2})"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Notes --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Conditions de livraison</label>
                                <textarea name="delivery_conditions" class="form-control" rows="2">{{ old('delivery_conditions', $purchaseOrder->delivery_conditions ?? '') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes internes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchaseOrder->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>{{ isset($purchaseOrder) ? 'Mettre à jour' : 'Créer le BC' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
