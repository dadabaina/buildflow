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
    <title>{{ $title ?? config('app.name', 'BuildFlow') }}</title>

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
        'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
        'resources/assets/vendor/scss/core.scss',
        'resources/css/app.scss'
    ])

    @stack('styles')
</head>

<body data-page="{{ optional(request()->route())->getName() }}">

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <!-- Sidebar / Menu -->
        @include('layouts.partials.sidebar')
        <!-- / Sidebar -->

        <!-- Layout page -->
        <div class="layout-page">

            <!-- Navbar -->
            <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">

                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                        <i class="icon-base bx bx-menu icon-md"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

                    <!-- Breadcrumb -->
                    <div class="d-none d-xl-flex align-items-center me-auto">
                        @if(isset($breadcrumb))
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    {{ $breadcrumb }}
                                </ol>
                            </nav>
                        @endif
                    </div>

                    <ul class="navbar-nav flex-row align-items-center ms-auto gap-2">

                        <!-- Aide -->
                        <li class="nav-item">
                            <a href="{{ route('help.index') }}" id="help-launch-btn" data-hub-url="{{ route('help.index') }}"
                               class="nav-link btn btn-label-primary btn-icon rounded-circle" title="Lancer le guide de cette page">
                                <i class="icon-base bx bx-help-circle icon-md"></i>
                            </a>
                        </li>
                        <!-- /Aide -->

                        <!-- Notifications -->
                        @php $unreadCount = auth()->user()?->unreadNotifications()->count(); @endphp
                        <li class="nav-item dropdown">
                            <a class="nav-link hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <i class="icon-base bx bx-bell icon-md"></i>
                                @if($unreadCount > 0)
                                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" style="font-size:.6rem;padding:2px 5px;">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0" style="width:340px;max-height:400px;overflow-y:auto;">
                                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                    <span class="fw-semibold small">Notifications</span>
                                    <a href="{{ route('notifications.index') }}" class="small text-primary">Voir tout</a>
                                </div>
                                @forelse(auth()->user()?->notifications()->latest()->take(5)->get() ?? [] as $notif)
                                @php $nd = is_array($notif->data) ? $notif->data : json_decode($notif->data, true); @endphp
                                <a href="{{ route('notifications.read', $notif->id) }}"
                                   class="dropdown-item d-flex gap-2 align-items-start p-3 {{ is_null($notif->read_at) ? 'bg-primary bg-opacity-10' : '' }}">
                                    <i class="icon-base bx {{ $nd['icon'] ?? 'bx-bell' }} text-{{ $nd['color'] ?? 'primary' }} mt-1"></i>
                                    <div>
                                        <p class="mb-0 small fw-semibold">{{ $nd['title'] ?? '' }}</p>
                                        <p class="mb-0 text-muted" style="font-size:.75rem">{{ Str::limit($nd['message'] ?? '', 60) }}</p>
                                        <p class="mb-0 text-muted" style="font-size:.7rem">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                </a>
                                @empty
                                <div class="text-center text-muted py-4 small">
                                    <i class="icon-base bx bx-bell-off d-block mb-1 fs-4 opacity-25"></i>Aucune notification
                                </div>
                                @endforelse
                            </div>
                        </li>

                        <!-- User -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <div class="avatar avatar-online">
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-px-40 h-auto rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar avatar-online">
                                                    <img src="{{ auth()->user()->avatar_url }}" alt class="w-px-40 h-auto rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                                <small class="text-muted">{{ auth()->user()->company?->name }}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="icon-base bx bx-user icon-md me-2"></i> Mon profil
                                    </a>
                                </li>
                                @can('settings.view')
                                <li>
                                    <a class="dropdown-item" href="{{ route('settings.index') }}">
                                        <i class="icon-base bx bx-cog icon-md me-2"></i> Paramètres
                                    </a>
                                </li>
                                @endcan
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="icon-base bx bx-power-off icon-md me-2"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
            </nav>
            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">

                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible mb-4" role="alert">
                        <i class="icon-base bx bx-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible mb-4" role="alert">
                        <i class="icon-base bx bx-error-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    {{ $slot }}

                </div>
                <!-- / Content -->

                <!-- Footer -->
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                        <div class="mb-2 mb-md-0">
                            © <script>document.write(new Date().getFullYear());</script>
                            BuildFlow
                        </div>
                    </div>
                </footer>
                <!-- / Footer -->

                <div class="content-backdrop fade"></div>
            </div>
            <!-- / Content wrapper -->

        </div>
        <!-- / Layout page -->

    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    <!-- Drag Target -->
    <div class="drag-target"></div>
</div>
<!-- / Layout wrapper -->

@vite([
    'resources/assets/vendor/js/helpers.js',
    'resources/assets/js/config.js',
    'resources/assets/vendor/js/menu.js',
    'resources/assets/js/main.js',
    'resources/js/app.js'
])

@stack('scripts')
</body>
</html>
