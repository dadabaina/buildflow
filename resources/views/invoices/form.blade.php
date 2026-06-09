<x-layouts.app :title="isset($invoice) ? 'Modifier facture' : 'Nouvelle facture'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-decoration-none opacity-50 text-dark">Factures</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($invoice) ? 'Modifier' : 'Nouvelle' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-file-earmark-check me-2"></i>
                        {{ isset($invoice) ? 'Modifier la facture' : 'Nouvelle facture' }}
                    </h6>
                </div>
                <div class="card-body" x-data="{
                    projectClientMap: {{ $projects->pluck('client_id', 'id')->toJson() }},
                    selectedProject: '{{ old('project_id', $invoice->project_id ?? '') }}',
                    selectedClient: '{{ old('client_id', $invoice->client_id ?? '') }}',
                    updateClient() {
                        if (this.selectedProject && this.projectClientMap[this.selectedProject]) {
                            this.selectedClient = this.projectClientMap[this.selectedProject];
                        }
                    }
                }">
                    <form method="POST"
                          action="{{ isset($invoice) ? route('invoices.update', $invoice) : route('invoices.store') }}">
                        @csrf
                        @isset($invoice) @method('PATCH') @endisset

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Titre / Objet <span class="text-danger">*</span></label>
                                <input type="text" name="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $invoice->title ?? '') }}" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="standard" {{ old('type', $invoice->type ?? 'standard') === 'standard' ? 'selected' : '' }}>Facture standard</option>
                                    <option value="acompte" {{ old('type', $invoice->type ?? '') === 'acompte' ? 'selected' : '' }}>Acompte</option>
                                    <option value="situation" {{ old('type', $invoice->type ?? '') === 'situation' ? 'selected' : '' }}>Situation</option>
                                    <option value="avoir" {{ old('type', $invoice->type ?? '') === 'avoir' ? 'selected' : '' }}>Avoir</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Chantier <span class="text-danger">*</span></label>
                                <select name="project_id" x-model="selectedProject" @change="updateClient()"
                                        class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}">
                                        {{ $proj->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select name="client_id" x-model="selectedClient"
                                        class="form-select @error('client_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}">
                                        {{ $client->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date de facturation <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date"
                                       class="form-control @error('invoice_date') is-invalid @enderror"
                                       value="{{ old('invoice_date', isset($invoice) ? $invoice->invoice_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                @error('invoice_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date d'échéance</label>
                                <input type="date" name="due_date" class="form-control"
                                       value="{{ old('due_date', isset($invoice) ? $invoice->due_date?->format('Y-m-d') : now()->addDays(30)->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">TVA (%)</label>
                                <input type="number" name="tva_rate" class="form-control"
                                       step="0.01" min="0" max="100"
                                       value="{{ old('tva_rate', $invoice->tva_rate ?? 20) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">RG / Retenue (%) </label>
                                <input type="number" name="rg_rate" class="form-control"
                                       step="0.01" min="0" max="100"
                                       value="{{ old('rg_rate', $invoice->rg_rate ?? 0) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($invoice) ? 'Enregistrer' : 'Créer la facture' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
