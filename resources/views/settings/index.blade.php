<x-layouts.app title="Paramètres — Entreprise">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Paramètres</li>
    </x-slot>

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Paramètres</h3>
        <p class="text-secondary small">Configurez votre entreprise et personnalisez votre espace de travail.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-3" id="tour-settings-list">
            @include('settings._nav')
        </div>

        <div class="col-lg-9">  
            <x-card title="Informations de l'entreprise" subtitle="Ces informations apparaîtront sur vos documents officiels (devis, factures).">
                <form method="POST" action="{{ route('settings.company') }}">
                    @csrf @method('PATCH')
                    <div class="row g-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-dark small">Nom de l'entreprise <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required placeholder="Ex: BuildFlow Madagascar">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark small">TVA par défaut (%)</label>
                            <div class="input-group">
                                <input type="number" name="tva_rate" class="form-control" step="0.01" value="{{ old('tva_rate', $company->tva_rate ?? 20) }}">
                                <span class="input-group-text bg-light">%</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Email de contact</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $company->email) }}" placeholder="contact@entreprise.mg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Téléphone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}" placeholder="+261 ...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small">Adresse du siège</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Numéro, Rue, Ville...">{{ old('address', $company->address) }}</textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-secondary text-uppercase fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 0.1em;">Identifiants Fiscaux</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small">NIF</label>
                                    <input type="text" name="nif" class="form-control" value="{{ old('nif', $company->nif) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small">STAT</label>
                                    <input type="text" name="stat" class="form-control" value="{{ old('stat', $company->stat) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-dark small">RCS</label>
                                    <input type="text" name="rcs" class="form-control" value="{{ old('rcs', $company->rcs) }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-secondary text-uppercase fw-bold mb-3" style="font-size: 0.75rem; letter-spacing: 0.1em;">Préfixes de numérotation</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark small">Devis</label>
                                    <input type="text" name="quote_prefix" class="form-control" value="{{ old('quote_prefix', $company->quote_prefix) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark small">Facture</label>
                                    <input type="text" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $company->invoice_prefix) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark small">Avoir</label>
                                    <input type="text" name="credit_note_prefix" class="form-control" value="{{ old('credit_note_prefix', $company->credit_note_prefix) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark small">Chantier</label>
                                    <input type="text" name="project_prefix" class="form-control" value="{{ old('project_prefix', $company->project_prefix) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 shadow-app">
                            <i class="bi bi-check2-circle me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
