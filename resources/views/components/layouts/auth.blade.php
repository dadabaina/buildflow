<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="layout-menu-fixed layout-compact"
    dir="ltr"
    data-skin="default"
    data-assets-path="{{ asset('assets') . '/' }}"
    data-base-url="{{ url('/') }}"
    data-framework="laravel"
    data-bs-theme="light"
    data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'BuildFlow' }} — Connexion</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite([
        'resources/assets/vendor/fonts/iconify/iconify.css',
        'resources/assets/vendor/scss/core.scss',
        'resources/assets/vendor/scss/pages/page-auth.scss',
        'resources/css/app.scss'
    ])

    @stack('styles')

    @stack('styles')
</head>
<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-6">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <g transform="translate(3.000000, 1.000000)">
                                                <polygon fill="#696CFF" opacity="0.2" points="18.1 -0.3 1.2 15.6 0 23.4 18.1 24.5"></polygon>
                                                <polygon fill="#696CFF" points="18.1 -0.3 1.2 15.6 12.7 18.6"></polygon>
                                                <polygon fill="#696CFF" opacity="0.5" points="11.7 20.2 8.6 17.4 19.3 3.4"></polygon>
                                                <polygon fill="#696CFF" points="18.1 -0.3 19.3 3.4 11.7 20.2 0 23.4 18.1 24.5"></polygon>
                                                <polygon fill="#696CFF" opacity="0.6" points="0 23.4 11.7 20.2 18.1 24.5"></polygon>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">BuildFlow</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        {{ $slot }}
                    </div>
                </div>
                <!-- /Register -->
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
