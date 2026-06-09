<x-layouts.app>
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Compte-rendus</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ $siteReport->reference ?? $siteReport->title }}</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="fw-bold mb-1">{{ $siteReport->title }}</h3>
            <div class="d-flex gap-2 align-items-center text-secondary small">
                <span><i class="bi bi-calendar3 me-1"></i>{{ $siteReport->report_date->format('d/m/Y') }}</span>
                <span>·</span>
                <span><i class="bi bi-building me-1"></i>{{ $siteReport->project->name ?? '—' }}</span>
                @if($siteReport->location)
                <span>·</span>
                <span><i class="bi bi-geo-alt me-1"></i>{{ $siteReport->location }}</span>
                @endif
                <span>·</span>
                <span class="badge bg-{{ $siteReport->status === 'finalise' ? 'success' : 'warning text-dark' }}">
                    {{ $siteReport->status === 'finalise' ? 'Finalisé' : 'Brouillon' }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('site-reports.export', $siteReport) }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </a>
            @if($siteReport->status !== 'finalise')
            <a href="{{ route('site-reports.edit', $siteReport) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            @can('site_reports.edit')
            <form method="POST" action="{{ route('site-reports.finalize', $siteReport) }}">
                @csrf
                <button class="btn btn-success btn-sm" onclick="return confirm('Finaliser ce CR ? Il ne sera plus modifiable.')">
                    <i class="bi bi-check-circle me-1"></i>Finaliser
                </button>
            </form>
            @endcan
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Contenu CR --}}
        <div class="col-lg-8">
            <x-card title="Contenu du compte-rendu">
                @if($siteReport->content)
                    <div class="prose" style="white-space: pre-wrap; line-height: 1.7;">{{ $siteReport->content }}</div>
                @else
                    <p class="text-muted fst-italic">Aucun contenu rédigé.</p>
                @endif
            </x-card>

            {{-- Points d'action --}}
            <x-card title="Points d'action" subtitle="Tâches à suivre issues de ce CR" class="mt-4">
                @can('site_reports.edit')
                <form method="POST" action="{{ route('site-reports.items.store', $siteReport) }}" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <input type="text" name="description" class="form-control form-control-sm"
                                   placeholder="Description de l'action *" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="responsible" class="form-control form-control-sm" placeholder="Responsable">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="due_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                </form>
                @endcan

                @forelse($siteReport->items as $item)
                <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 mb-2 {{ $item->status === 'clos' ? 'bg-light opacity-75' : 'bg-white' }}">
                    <div class="d-flex gap-3 align-items-center">
                        <span class="badge bg-{{ $item->status === 'clos' ? 'success' : ($item->status === 'en_cours' ? 'warning text-dark' : 'secondary') }}">
                            {{ ['ouvert' => 'Ouvert', 'en_cours' => 'En cours', 'clos' => 'Clos'][$item->status] ?? $item->status }}
                        </span>
                        <div>
                            <p class="mb-0 fw-semibold {{ $item->status === 'clos' ? 'text-decoration-line-through text-muted' : '' }}">{{ $item->description }}</p>
                            <small class="text-muted">
                                {{ $item->responsible ? 'Responsable: '.$item->responsible : '' }}
                                {{ $item->due_date ? ' · Échéance: '.$item->due_date->format('d/m/Y') : '' }}
                            </small>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        @can('site_reports.edit')
                        <form method="POST" action="{{ route('site-reports.items.update', [$siteReport, $item]) }}">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:120px">
                                <option value="ouvert" {{ $item->status === 'ouvert' ? 'selected' : '' }}>Ouvert</option>
                                <option value="en_cours" {{ $item->status === 'en_cours' ? 'selected' : '' }}>En cours</option>
                                <option value="clos" {{ $item->status === 'clos' ? 'selected' : '' }}>Clos</option>
                            </select>
                        </form>
                        <form method="POST" action="{{ route('site-reports.items.destroy', [$siteReport, $item]) }}"
                              onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-light btn-sm text-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">Aucun point d'action.</p>
                @endforelse
            </x-card>
        </div>

        {{-- Info rapide --}}
        <div class="col-lg-4">
            <x-card title="Informations">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted">Référence</dt>
                    <dd class="col-7 fw-mono">{{ $siteReport->reference ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Chantier</dt>
                    <dd class="col-7">
                        <a href="{{ route('projects.show', $siteReport->project) }}" class="text-decoration-none">
                            {{ $siteReport->project->name ?? '—' }}
                        </a>
                    </dd>
                    <dt class="col-5 text-muted">Rédacteur</dt>
                    <dd class="col-7">{{ $siteReport->author->name ?? '—' }}</dd>
                    @if($siteReport->weather)
                    <dt class="col-5 text-muted">Météo</dt>
                    <dd class="col-7">{{ $siteReport->weather }}</dd>
                    @endif
                    @if($siteReport->next_meeting_date)
                    <dt class="col-5 text-muted">Prochaine réunion</dt>
                    <dd class="col-7">{{ $siteReport->next_meeting_date->format('d/m/Y') }}</dd>
                    @endif
                    <dt class="col-5 text-muted">Points d'action</dt>
                    <dd class="col-7">{{ $siteReport->items->count() }} ({{ $siteReport->items->where('status', 'clos')->count() }} clos)</dd>
                </dl>
            </x-card>

            @can('site_reports.delete')
            <div class="mt-3">
                <form method="POST" action="{{ route('site-reports.destroy', $siteReport) }}"
                      onsubmit="return confirm('Supprimer ce CR ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100 btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
                </form>
            </div>
            @endcan
        </div>
    </div>
</x-layouts.app>
