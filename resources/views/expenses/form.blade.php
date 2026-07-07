<x-layouts.app :title="isset($expense) ? 'Modifier dépense' : 'Nouvelle dépense'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}" class="text-decoration-none opacity-50 text-dark">Dépenses</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($expense) ? 'Modifier' : 'Nouvelle' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-receipt me-2"></i>
                        {{ isset($expense) ? 'Modifier la dépense' : 'Nouvelle dépense' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($expense) ? route('expenses.update', $expense) : route('expenses.store') }}"
                          enctype="multipart/form-data">
                        @csrf
                        @isset($expense) @method('PATCH') @endisset

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" name="description"
                                       class="form-control @error('description') is-invalid @enderror"
                                       value="{{ old('description', $expense->description ?? '') }}" required>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Chantier <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}"
                                        {{ old('project_id', request('project_id', $expense->project_id ?? '')) == $proj->id ? 'selected' : '' }}>
                                        {{ $proj->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tâche <span class="text-muted small">(optionnel)</span></label>
                                <select name="task_id" id="task_id" class="form-select @error('task_id') is-invalid @enderror">
                                    <option value="">— Dépense générale du chantier —</option>
                                    @foreach($tasks as $t)
                                    <option value="{{ $t->id }}" data-project="{{ $t->project_id }}"
                                        {{ old('task_id', request('task_id', $expense->task_id ?? '')) == $t->id ? 'selected' : '' }}>
                                        {{ $t->title }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Précise à quelle tâche du chantier cette dépense se rattache.</div>
                                @error('task_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Catégorie</label>
                                <select name="expense_category_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('expense_category_id', $expense->expense_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fournisseur</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}"
                                        {{ old('supplier_id', $expense->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date de la dépense <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date"
                                       class="form-control @error('expense_date') is-invalid @enderror"
                                       value="{{ old('expense_date', isset($expense) ? $expense->expense_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Prix unitaire (MGA) <span class="text-danger">*</span></label>
                                <input type="number" name="unit_price" class="form-control @error('unit_price') is-invalid @enderror"
                                       step="1" min="0" value="{{ old('unit_price', $expense->unit_price ?? '') }}" required>
                                @error('unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Unité</label>
                                <input type="text" name="unit" class="form-control"
                                       placeholder="m³, kg, j…"
                                       value="{{ old('unit', $expense->unit ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="quantity" class="form-control"
                                       step="0.01" min="0"
                                       value="{{ old('quantity', $expense->quantity ?? 1) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mode de paiement</label>
                                <select name="payment_mode" class="form-select">
                                    <option value="">—</option>
                                    @foreach(['Espèces','Virement','Chèque','Mobile Money','Autre'] as $pm)
                                    <option value="{{ $pm }}" {{ old('payment_mode', $expense->payment_mode ?? '') === $pm ? 'selected' : '' }}>
                                        {{ $pm }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Référence paiement</label>
                                <input type="text" name="payment_reference" class="form-control"
                                       placeholder="N° reçu, réf. virement…"
                                       value="{{ old('payment_reference', $expense->payment_reference ?? '') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes ?? '') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Pièce jointe</label>
                                <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                @isset($expense)
                                @if($expense->attachment_path)
                                <div class="form-text">
                                    Fichier actuel : <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank">voir</a>
                                </div>
                                @endif
                                @endisset
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($expense) ? 'Enregistrer' : 'Créer la dépense' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const projectSelect = document.getElementById('project_id');
            const taskSelect = document.getElementById('task_id');

            function filterTasks() {
                const pid = projectSelect.value;
                Array.from(taskSelect.options).forEach(function (opt) {
                    if (!opt.value) return;
                    opt.hidden = !!pid && opt.dataset.project !== pid;
                });
                if (taskSelect.value && taskSelect.selectedOptions[0]?.hidden) {
                    taskSelect.value = '';
                }
            }

            projectSelect.addEventListener('change', filterTasks);
            filterTasks();
        });
    </script>
    @endpush
</x-layouts.app>
