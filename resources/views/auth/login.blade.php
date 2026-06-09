<x-layouts.auth title="Connexion">
    <h4 class="mb-1">Bon retour ! 👋</h4>
    <p class="mb-6">Veuillez vous connecter à votre compte.</p>

    @if(session('status'))
        <div class="alert alert-success small mb-4">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger small mb-4">
            @foreach($errors->all() as $error)
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form id="formAuthentication" class="mb-6" action="{{ route('login') }}" method="POST">
        @csrf
        <div class="mb-6">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Entrez votre email" autofocus required />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-6 form-password-toggle" x-data="{ show: false }">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Mot de passe</label>
                <a href="{{ route('password.request') }}">
                    <small>Mot de passe oublié ?</small>
                </a>
            </div>
            <div class="input-group input-group-merge">
                <input :type="show ? 'text' : 'password'" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" required />
                <span class="input-group-text cursor-pointer" @click="show = !show">
                    <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                </span>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="mb-8">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" />
                <label class="form-check-label" for="remember"> Rester connecté </label>
            </div>
        </div>
        <div class="mb-6">
            <button class="btn btn-primary d-grid w-100" type="submit">Se connecter</button>
        </div>
    </form>
</x-layouts.auth>
