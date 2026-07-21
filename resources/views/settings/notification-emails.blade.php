<x-layouts.app title="Paramètres — Notifications par email">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none opacity-50 text-dark">Paramètres</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Notifications par email</li>
    </x-slot>

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Paramètres</h3>
        <p class="text-secondary small">Configurez votre entreprise et personnalisez votre espace de travail.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-3">
            @include('settings._nav')
        </div>

        <div class="col-lg-9">
            <x-card title="Notifications par email" subtitle="Pour chaque type d'événement, définissez les adresses email qui recevront un récapitulatif quotidien (une fois par jour maximum, regroupant tous les événements du jour).">
                @foreach($types as $t)
                <div class="border rounded-4 p-3 px-4 mb-3">
                    <h6 class="fw-bold text-dark mb-2">{{ $t['label'] }}</h6>

                    @if(count($t['emails']))
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($t['emails'] as $email)
                        <span class="badge bg-label-primary d-flex align-items-center gap-2 py-2 px-3">
                            {{ $email }}
                            <form method="POST" action="{{ route('settings.notification_emails.destroy', $t['setting_id']) }}" class="d-inline" onsubmit="return confirm('Retirer cette adresse ?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="email" value="{{ $email }}">
                                <button type="submit" class="btn-close btn-close-sm" style="font-size: 0.6rem;" aria-label="Retirer"></button>
                            </form>
                        </span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted small mb-3">Aucune adresse configurée — aucun email envoyé pour ce type.</p>
                    @endif

                    <form method="POST" action="{{ route('settings.notification_emails.store') }}" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="notification_type" value="{{ $t['type'] }}">
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="email@exemple.mg" required style="max-width: 280px;">
                        <button class="btn btn-sm btn-outline-primary px-3"><i class="bi bi-plus-lg me-1"></i>Ajouter</button>
                    </form>
                </div>
                @endforeach
            </x-card>

            <x-card title="Configuration SMTP d'envoi" subtitle="Paramétrez le serveur d'envoi utilisé pour les emails de cette société. Si désactivé, la configuration par défaut du serveur est utilisée.">
                <form method="POST" action="{{ route('settings.mail.update') }}">
                    @csrf
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="mail_is_enabled" name="is_enabled" value="1"
                               {{ old('is_enabled', $mailSettings?->is_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="mail_is_enabled">Activer un serveur SMTP personnalisé pour cette société</label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small text-muted">Hôte SMTP</label>
                            <input type="text" name="host" class="form-control" placeholder="smtp.gmail.com"
                                   value="{{ old('host', $mailSettings?->host) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Port</label>
                            <input type="number" name="port" class="form-control" placeholder="587"
                                   value="{{ old('port', $mailSettings?->port) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Utilisateur</label>
                            <input type="text" name="username" class="form-control" placeholder="contact@masociete.mg"
                                   value="{{ old('username', $mailSettings?->username) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Mot de passe</label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="{{ $mailSettings?->password ? '•••••••• (laisser vide pour conserver)' : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Chiffrement</label>
                            <select name="encryption" class="form-select">
                                <option value="tls" {{ old('encryption', $mailSettings?->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('encryption', $mailSettings?->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ !old('encryption', $mailSettings?->encryption) ? 'selected' : '' }}>Aucun</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Email expéditeur</label>
                            <input type="email" name="from_address" class="form-control" placeholder="noreply@masociete.mg"
                                   value="{{ old('from_address', $mailSettings?->from_address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Nom expéditeur</label>
                            <input type="text" name="from_name" class="form-control" placeholder="{{ Auth::user()->company->name }}"
                                   value="{{ old('from_name', $mailSettings?->from_name) }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm-app">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </button>
                        @if($mailSettings?->host)
                        <button type="submit" formaction="{{ route('settings.mail.test') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-send me-1"></i>Envoyer un email de test
                        </button>
                        @endif
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
