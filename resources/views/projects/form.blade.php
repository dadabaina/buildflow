<x-layouts.app :title="isset($project) ? 'Modifier chantier' : 'Nouveau chantier'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none opacity-50 text-dark">Chantiers</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($project) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-building me-2"></i>
                        {{ isset($project) ? 'Modifier le chantier' : 'Nouveau chantier' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($project) ? route('projects.update', $project) : route('projects.store') }}">
                        @csrf
                        @isset($project) @method('PATCH') @endisset

                        <div class="row g-3">
                            {{-- Infos générales --}}
                            <div class="col-12">
                                <h6 class="text-muted text-uppercase small fw-semibold border-bottom pb-1">
                                    Informations générales
                                </h6>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Nom du chantier <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $project->name ?? '') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    @foreach(['prospection' => 'Prospection', 'devis_en_cours' => 'Devis en cours', 'devis_envoye' => 'Devis envoyé', 'en_cours' => 'En cours', 'en_pause' => 'En pause', 'termine' => 'Terminé', 'cloture' => 'Clôturé', 'annule' => 'Annulé'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('status', $project->status ?? 'prospection') === $val ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Région / Site</label>
                                <select name="region_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($regions as $region)
                                    <option value="{{ $region->id }}"
                                        {{ old('region_id', $project->region_id ?? '') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $project->description ?? '') }}</textarea>
                            </div>

                            {{-- Finances --}}
                            <div class="col-12 mt-2">
                                <h6 class="text-muted text-uppercase small fw-semibold border-bottom pb-1">Finances</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Budget (MGA)</label>
                                <input type="number" name="budget_total" class="form-control" step="1"
                                       value="{{ old('budget_total', $project->budget_total ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Montant marché</label>
                                <input type="number" name="contract_amount" class="form-control" step="1"
                                       value="{{ old('contract_amount', $project->contract_amount ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Avance reçue</label>
                                <input type="number" name="advance_received" class="form-control" step="0.01"
                                       value="{{ old('advance_received', $project->advance_received ?? '') }}">
                            </div>

                            {{-- Dates --}}
                            <div class="col-12 mt-2">
                                <h6 class="text-muted text-uppercase small fw-semibold border-bottom pb-1">Calendrier</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date de début</label>
                                <input type="date" name="start_date" class="form-control"
                                       value="{{ old('start_date', isset($project) ? $project->start_date?->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date de fin prévue</label>
                                <input type="date" name="end_date" class="form-control"
                                       value="{{ old('end_date', isset($project) ? $project->end_date?->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date de clôture</label>
                                <input type="date" name="actual_end_date" class="form-control"
                                       value="{{ old('actual_end_date', isset($project) ? $project->actual_end_date?->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes internes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $project->notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($project) ? 'Enregistrer' : 'Créer le chantier' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
