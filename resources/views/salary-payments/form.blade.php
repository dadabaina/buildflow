<x-layouts.app title="Nouveau paiement salarié">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('salary-payments.index') }}" class="text-decoration-none opacity-50 text-dark">Paiements Salariés</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Nouveau</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2"></i>Enregistrer un paiement salarié</h6>
                </div>

                <form method="POST" action="{{ route('salary-payments.store') }}">
                    @csrf

                    <div class="card-body"
                         x-data="{
                             employeeId: '{{ old('employee_id', $selectedEmployeeId ?? '') }}',
                             periodStart: '{{ old('period_start', now()->startOfMonth()->format('Y-m-d')) }}',
                             periodEnd: '{{ old('period_end', now()->format('Y-m-d')) }}',
                             amount: {{ old('amount', 0) }},
                             allocations: {{ json_encode(old('allocations', ($selectedProjectId ?? null) ? [['project_id' => $selectedProjectId, 'amount' => 0]] : [['project_id' => '', 'amount' => 0]])) }},
                             employeeProjects: {{ json_encode($employeeProjectsMap) }},
                             recapRows: [],
                             recapLoading: false,
                             get availableProjectsForAllocation() { return this.employeeProjects[this.employeeId] || []; },
                             get totalAllocated() {
                                 return this.allocations.reduce((s, a) => s + (parseFloat(a.amount) || 0), 0);
                             },
                             get diff() {
                                 return Math.round(((parseFloat(this.amount) || 0) - this.totalAllocated) * 100) / 100;
                             },
                             addAllocation() { this.allocations.push({ project_id: '', amount: 0 }); },
                             removeAllocation(idx) { if (this.allocations.length > 1) this.allocations.splice(idx, 1); },
                             async loadRecap() {
                                 if (!this.employeeId || !this.periodStart || !this.periodEnd) return;
                                 this.recapLoading = true;
                                 try {
                                     const res = await fetch(`{{ route('salary-payments.attendance-recap') }}?employee_id=${this.employeeId}&from=${this.periodStart}&to=${this.periodEnd}`);
                                     const data = await res.json();
                                     this.recapRows = data.rows;
                                 } finally {
                                     this.recapLoading = false;
                                 }
                             },
                             applyRecap() {
                                 if (!this.recapRows.length) return;
                                 this.allocations = this.recapRows.map(r => ({ project_id: r.project_id, amount: r.estimated_amount }));
                                 this.amount = Math.round(this.totalAllocated * 100) / 100;
                             },
                             onEmployeeChange() {
                                 this.allocations = this.allocations.map(a => ({ ...a, project_id: '' }));
                                 this.loadRecap();
                             }
                         }"
                         x-init="loadRecap()">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Salarié <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror"
                                        x-model="employeeId" @change="onEmployeeChange()" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->payment_frequency_libelle }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Seuls les salariés actuellement affectés à au moins un chantier sont proposés.</div>
                                @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Date du paiement <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date"
                                       class="form-control @error('payment_date') is-invalid @enderror"
                                       value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                                @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Mode de paiement</label>
                                <select name="payment_mode" class="form-select">
                                    <option value="">Sélectionner...</option>
                                    @foreach(['Espèces', 'Virement bancaire', 'Mobile Money', 'Chèque', 'Autre'] as $m)
                                    <option value="{{ $m }}" {{ old('payment_mode') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Période — début</label>
                                <input type="date" name="period_start" class="form-control"
                                       x-model="periodStart" @change="loadRecap()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Période — fin</label>
                                <input type="date" name="period_end" class="form-control"
                                       x-model="periodEnd" @change="loadRecap()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Référence</label>
                                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Montant total versé (MGA) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="0.01" min="0.01"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       x-model="amount" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <hr>

                        {{-- Récap pointage : purement indicatif, pré-remplit la ventilation sans rien imposer --}}
                        <div class="mb-3">
                            <h6 class="mb-2"><i class="bi bi-calendar-check me-1"></i>Pointage de référence pour la période</h6>
                            <div class="alert alert-light border small mb-2">
                                Ce récapitulatif est purement indicatif : il aide à répartir le paiement entre chantiers, mais vous restez libre d'ajuster chaque montant (avance, régularisation, impayé...).
                            </div>
                            <template x-if="recapLoading">
                                <div class="text-muted small">Chargement du pointage...</div>
                            </template>
                            <template x-if="!recapLoading && recapRows.length === 0">
                                <div class="text-muted small">Aucun pointage trouvé sur cette période pour ce salarié.</div>
                            </template>
                            <template x-if="!recapLoading && recapRows.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-2">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Chantier</th>
                                                <th class="text-end">Jours pointés</th>
                                                <th class="text-end">Heures</th>
                                                <th>Modalité</th>
                                                <th class="text-end">Montant théorique</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="row in recapRows" :key="row.project_id">
                                                <tr>
                                                    <td x-text="row.project_name"></td>
                                                    <td class="text-end" x-text="row.total_days"></td>
                                                    <td class="text-end" x-text="row.total_hours"></td>
                                                    <td x-text="row.frequency"></td>
                                                    <td class="text-end" x-text="row.estimated_amount.toLocaleString('fr-MG')"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="applyRecap()">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Pré-remplir la ventilation depuis le pointage
                                </button>
                            </template>
                        </div>

                        <hr>

                        {{-- Ventilation réelle par chantier : librement modifiable, validée côté serveur --}}
                        <h6 class="mb-2">Ventilation du paiement par chantier</h6>
                        <div class="table-responsive mb-2">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Chantier</th>
                                        <th style="width:200px">Montant (MGA)</th>
                                        <th style="width:40px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(alloc, idx) in allocations" :key="idx">
                                        <tr>
                                            <td>
                                                <select :name="`allocations[${idx}][project_id]`" x-model="alloc.project_id"
                                                        x-init="$nextTick(() => { $el.value = alloc.project_id })"
                                                        class="form-select form-select-sm" :disabled="!employeeId" required>
                                                    <option value="">Sélectionner...</option>
                                                    <template x-for="p in availableProjectsForAllocation" :key="p.id">
                                                        <option :value="p.id" x-text="p.label"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" :name="`allocations[${idx}][amount]`"
                                                       x-model="alloc.amount" class="form-control form-control-sm text-end"
                                                       step="0.01" min="0.01" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm" @click="removeAllocation(idx)">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-outline-primary btn-sm" @click="addAllocation()">
                                                <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                                            </button>
                                        </td>
                                        <td class="text-end fw-semibold" x-text="totalAllocated.toLocaleString('fr-MG')"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="text-end fw-semibold">Écart avec le montant total</td>
                                        <td class="text-end fw-bold" :class="diff === 0 ? 'text-success' : 'text-danger'" x-text="diff.toLocaleString('fr-MG')"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="alert alert-warning small" x-show="diff !== 0" x-cloak>
                            La somme des ventilations doit être égale au montant total versé avant l'enregistrement.
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('salary-payments.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer le paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
