<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">PV Réception</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($receptionReport) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-card title="{{ isset($receptionReport) ? 'Modifier le PV de réception' : 'Nouveau PV de réception' }}">
                <form method="POST" action="{{ isset($receptionReport) ? route('reception-reports.update', $receptionReport) : route('reception-reports.store') }}">
                    @csrf @if(isset($receptionReport)) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Chantier <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $receptionReport->project_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Date de réception <span class="text-danger">*</span></label>
                            <input type="date" name="reception_date" class="form-control"
                                   value="{{ old('reception_date', isset($receptionReport) ? $receptionReport->reception_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nom du client / Maître d'ouvrage</label>
                            <input type="text" name="client_name" class="form-control"
                                   value="{{ old('client_name', $receptionReport->client_name ?? '') }}" placeholder="Nom complet">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Montant RG (Ar)</label>
                            <input type="number" name="rg_amount" class="form-control" step="0.01" min="0"
                                   value="{{ old('rg_amount', $receptionReport->rg_amount ?? 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Réserves</label>
                            <textarea name="reserves" class="form-control" rows="4"
                                      placeholder="Listez les réserves émises lors de la réception...">{{ old('reserves', $receptionReport->reserves ?? '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notes complémentaires</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $receptionReport->notes ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                        <a href="{{ route('reception-reports.index') }}" class="btn btn-light">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1"></i>{{ isset($receptionReport) ? 'Mettre à jour' : 'Créer le PV' }}
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
