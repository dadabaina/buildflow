@php
$navItems = [
    ['route' => 'settings.index',                 'icon' => 'bi-building',        'label' => 'Entreprise'],
    ['route' => 'settings.regions.index',          'icon' => 'bi-geo-alt',         'label' => 'Régions'],
    ['route' => 'settings.job_types.index',        'icon' => 'bi-person-badge',    'label' => 'Postes & Fonctions'],
    ['route' => 'settings.unit_types.index',       'icon' => 'bi-rulers',          'label' => 'Unités de mesure'],
    ['route' => 'settings.expense_categories.index','icon'=> 'bi-tags',            'label' => 'Catégories dépenses'],
    ['route' => 'settings.salary_rates.index',     'icon' => 'bi-currency-dollar', 'label' => 'Grille salariale'],
    ['route' => 'settings.material_categories.index','icon'=> 'bi-boxes',          'label' => 'Catégories matériaux'],
    ['route' => 'settings.notification_emails.index','icon'=> 'bi-envelope',       'label' => 'Notifications par email'],
];
@endphp

<div class="card border-0 shadow-sm-app rounded-4 p-3 bg-white sticky-top" style="top: 90px; z-index: 100;">
    <nav class="nav flex-column settings-nav">
        @foreach($navItems as $item)
        <a href="{{ route($item['route']) }}"
           class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
            <i class="bi {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach
    </nav>
</div>
