<x-layouts.app :title="isset($client) ? 'Modifier client' : 'Nouveau client'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-decoration-none opacity-50 text-dark">Clients</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($client) ? 'Modifier' : 'Nouveau' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-person-plus me-2"></i>
                        {{ isset($client) ? 'Modifier le client' : 'Nouveau client' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($client) ? route('clients.update', $client) : route('clients.store') }}">
                        @csrf
                        @isset($client)
                            @method('PATCH')
                        @endisset

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $client->name ?? '') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="particulier" {{ old('type', $client->type ?? '') === 'particulier' ? 'selected' : '' }}>Particulier</option>
                                    <option value="entreprise" {{ old('type', $client->type ?? '') === 'entreprise' ? 'selected' : '' }}>Entreprise</option>
                                    <option value="administration" {{ old('type', $client->type ?? '') === 'administration' ? 'selected' : '' }}>Administration</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone', $client->phone ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $client->email ?? '') }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="address" class="form-control"
                                       value="{{ old('address', $client->address ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Ville</label>
                                <input type="text" name="city" class="form-control"
                                       value="{{ old('city', $client->city ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Région</label>
                                <select name="region_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($regions as $region)
                                    <option value="{{ $region->id }}"
                                        {{ old('region_id', $client->region_id ?? '') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">NIF</label>
                                <input type="text" name="nif" class="form-control"
                                       value="{{ old('nif', $client->nif ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">STAT</label>
                                <input type="text" name="stat" class="form-control"
                                       value="{{ old('stat', $client->stat ?? '') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">RCS</label>
                                <input type="text" name="rcs" class="form-control"
                                       value="{{ old('rcs', $client->rcs ?? '') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $client->notes ?? '') }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($client) ? 'Enregistrer' : 'Créer le client' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
