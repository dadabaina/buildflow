<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'BuildFlow' }} — Connexion</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    
    <style>
        body.auth-wrapper {
            background-color: #f1f5f9;
            background-image: radial-gradient(#4f46e5 0.5px, transparent 0.5px), radial-gradient(#4f46e5 0.5px, #f1f5f9 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            background-attachment: fixed;
        }
    </style>
</head>
<body class="auth-wrapper min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="w-100" style="max-width: 440px;">
        <div class="text-center mb-4">
            <div class="bg-primary rounded-4 d-inline-flex align-items-center justify-content-center shadow-app mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-stack text-white fs-1"></i>
            </div>
            <h2 class="fw-bold text-dark tracking-tight">BuildFlow</h2>
            <p class="text-secondary small">Solution moderne de gestion BTP</p>
        </div>

        <div class="card border-0 shadow-app p-4 rounded-4">
            {{ $slot }}
        </div>

        <div class="text-center mt-4">
            <p class="text-secondary small">
                &copy; {{ date('Y') }} BuildFlow — Propulsé par l'innovation.
            </p>
        </div>
    </div>
</body>
</html>
