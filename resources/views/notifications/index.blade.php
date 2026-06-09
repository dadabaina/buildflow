<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Notifications</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Notifications</h3>
            <p class="text-secondary small mt-1">Toutes vos alertes et mises à jour.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-check2-all me-1"></i>Tout marquer comme lu
            </button>
        </form>
    </div>

    <x-card>
        <div class="list-group list-group-flush">
            @forelse($notifications as $notif)
            @php
                $data  = is_array($notif->data) ? $notif->data : json_decode($notif->data, true);
                $icon  = $data['icon'] ?? 'bi-bell';
                $color = $data['color'] ?? 'primary';
            @endphp
            <div class="list-group-item d-flex gap-3 align-items-start p-4 {{ is_null($notif->read_at) ? 'bg-primary-subtle bg-opacity-25' : '' }}">
                <div class="bg-{{ $color }}-subtle rounded-circle p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="bi {{ $icon }} text-{{ $color }}"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="fw-semibold mb-1">{{ $data['title'] ?? 'Notification' }}</p>
                    <p class="mb-1 text-muted small">{{ $data['message'] ?? '' }}</p>
                    <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    @if(is_null($notif->read_at))
                    <span class="badge bg-primary rounded-pill">Nouveau</span>
                    @endif
                    @if(!empty($data['url']))
                    <a href="{{ route('notifications.read', $notif->id) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash display-5 d-block mb-2 opacity-25"></i>
                <p>Aucune notification pour le moment.</p>
            </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
        <div class="p-3">{{ $notifications->links() }}</div>
        @endif
    </x-card>
</x-layouts.app>
