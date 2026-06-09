<x-layouts.auth title="Mot de passe oublié">
    <h4 class="fw-bold text-center mb-2">Mot de passe oublié</h4>
    <p class="text-muted text-center small mb-4">
        Saisissez votre e-mail pour recevoir un lien de réinitialisation.
    </p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Adresse e-mail</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" autofocus required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-envelope me-2"></i>Envoyer le lien
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
            </a>
        </div>
    </form>
</x-layouts.auth>
