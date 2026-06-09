<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Compte-rendus</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($siteReport) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <x-card title="{{ isset($siteReport) ? 'Modifier le compte-rendu' : 'Nouveau compte-rendu de chantier' }}">
                <form method="POST" action="{{ isset($siteReport) ? route('site-reports.update', $siteReport) : route('site-reports.store') }}">
                    @csrf @if(isset($siteReport)) @method('PUT') @endif

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Titre <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $siteReport->title ?? '') }}" required
                                   placeholder="Ex: Réunion de chantier N°3">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Date du CR <span class="text-danger">*</span></label>
                            <input type="date" name="report_date" class="form-control"
                                   value="{{ old('report_date', isset($siteReport) ? $siteReport->report_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Chantier <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                <option value="">— Sélectionner un chantier —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $siteReport->project_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Lieu</label>
                            <input type="text" name="location" class="form-control"
                                   value="{{ old('location', $siteReport->location ?? '') }}" placeholder="Ex: Bureau de chantier">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Météo</label>
                            <input type="text" name="weather" class="form-control"
                                   value="{{ old('weather', $siteReport->weather ?? '') }}" placeholder="Ex: Ensoleillé">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Contenu / Ordre du jour</label>
                            <textarea name="content" class="form-control" rows="8"
                                      placeholder="Compte-rendu détaillé de la réunion...">{{ old('content', $siteReport->content ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prochaine réunion</label>
                            <input type="date" name="next_meeting_date" class="form-control"
                                   value="{{ old('next_meeting_date', isset($siteReport) && $siteReport->next_meeting_date ? $siteReport->next_meeting_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                        <a href="{{ route('site-reports.index') }}" class="btn btn-light">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1"></i>{{ isset($siteReport) ? 'Mettre à jour' : 'Créer le CR' }}
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
