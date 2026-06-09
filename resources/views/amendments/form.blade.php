<x-layouts.app :title="isset($amendment) ? 'Modifier avenant' : 'Nouvel avenant'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('amendments.index') }}" class="text-decoration-none opacity-50 text-dark">Avenants</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($amendment) ? ($amendment ?? null)?->reference : 'Nouveau' }}</li>
    </x-slot>

    <script>
        function amendmentItems() {
            return {
                items: {!! json_encode(isset($amendment) 
                    ? ($amendment ?? null)?->items?->map(fn($i) => [
                        'description' => $i->description,
                        'quantity' => (float)$i->quantity,
                        'unit' => $i->unit,
                        'unit_price' => (float)$i->unit_price,
                        'is_deduction' => (bool)$i->is_deduction
                    ]) 
                    : [['description' => '', 'quantity' => 1, 'unit' => '', 'unit_price' => 0, 'is_deduction' => false]]
                ) !!},
                add()  { 
                    this.items.push({description:'',quantity:1,unit:'',unit_price:0,is_deduction:false}); 
                },
                remove(i) { 
                    this.items.splice(i, 1); 
                },
            };
        }
    </script>

    <div x-data="amendmentItems()">
        <form method="POST" action="{{ isset($amendment) ? route('amendments.update', $amendment) : route('amendments.store') }}">
            @csrf
            @if(isset($amendment)) @method('PUT') @endif

            <div class="row g-4">
                <div class="col-lg-8">
                    <x-card title="Informations" icon="bi bi-file-earmark-plus">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Chantier <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">— Sélectionner —</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ old('project_id', ($amendment ?? null)?->project_id ?? $selected) == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                    @endforeach
                                </select>
                                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Devis de référence <span class="text-muted small">(facultatif)</span></label>
                                <select name="quote_id" class="form-select @error('quote_id') is-invalid @enderror">
                                    <option value="">— Aucun —</option>
                                    @foreach($quotes as $q)
                                        <option value="{{ $q->id }}" {{ old('quote_id', ($amendment ?? null)?->quote_id ?? '') == $q->id ? 'selected' : '' }}>{{ $q->reference }}</option>
                                    @endforeach
                                </select>
                                @error('quote_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Titre <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', ($amendment ?? null)?->title ?? '') }}" required maxlength="200">
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">TVA (%)</label>
                                <input type="number" name="tva_rate" class="form-control" step="0.01" min="0" max="100"
                                       value="{{ old('tva_rate', ($amendment ?? null)?->tva_rate ?? 20) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Valable jusqu'au</label>
                                <input type="date" name="valid_until" class="form-control"
                                       value="{{ old('valid_until', ($amendment ?? null)?->valid_until?->format('Y-m-d') ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', ($amendment ?? null)?->description ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Notes internes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', ($amendment ?? null)?->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </x-card>
                </div>

                <div class="col-lg-4">
                    <x-card title="Actions" icon="bi bi-gear">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold shadow-sm-app">
                                <i class="bi bi-check-lg me-2"></i>{{ isset($amendment) ? 'Enregistrer' : 'Créer l\'avenant' }}
                            </button>
                            <a href="{{ route('amendments.index') }}" class="btn btn-light border">Annuler</a>
                        </div>
                    </x-card>
                </div>
            </div>

            {{-- Lignes --}}
            <div class="mt-4">
                <x-card title="Lignes de l'avenant" icon="bi bi-list-ul">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Description</th>
                                    <th class="border-0" style="width:110px">Qté</th>
                                    <th class="border-0" style="width:120px">Unité</th>
                                    <th class="border-0" style="width:150px">P.U. HT</th>
                                    <th class="border-0" style="width:80px">Déduction</th>
                                    <th class="border-0" style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, i) in items" :key="i">
                                    <tr>
                                        <td><input type="text" :name="`items[${i}][description]`" x-model="item.description" class="form-control form-control-sm" placeholder="Description" required></td>
                                        <td><input type="number" :name="`items[${i}][quantity]`" x-model="item.quantity" class="form-control form-control-sm" step="0.001" min="0"></td>
                                        <td>
                                            <select :name="`items[${i}][unit]`" x-model="item.unit" class="form-select form-select-sm">
                                                <option value="">—</option>
                                                @foreach($unitTypes as $ut)
                                                    <option value="{{ $ut->name }}">{{ $ut->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" :name="`items[${i}][unit_price]`" x-model="item.unit_price" class="form-control form-control-sm text-end" step="0.01" min="0">
                                                <span class="input-group-text">Ar</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" :name="`items[${i}][is_deduction]`" x-model="item.is_deduction" class="form-check-input" value="1">
                                        </td>
                                        <td>
                                            <button type="button" @click="remove(i)" class="btn btn-outline-danger btn-sm border-0"><i class="bi bi-trash"></i></button>
                                        </td>
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
    </div>
</x-layouts.app>
