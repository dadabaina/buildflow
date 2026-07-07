<x-layouts.app title="Modèles de dépense">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Modèles de dépense</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-receipt-cutoff me-2 text-primary"></i>Modèles de dépense
            <span class="badge bg-secondary ms-2">{{ $templates->count() }}</span>
        </h5>
        @can('expense_templates.create')
        <a href="{{ route('expense-templates.create') }}" id="tour-expense-templates-new" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau modèle
        </a>
        @endcan
    </div>

    <p class="text-muted small mb-4">
        Sous-détails de prix (matériaux, main-d'œuvre, matériel, sous-traitance) applicables directement à une
        <strong>tâche</strong> pour générer des dépenses réelles — à ne pas confondre avec les
        <a href="{{ route('dosage.index') }}">Modèles de Dosage</a>, réservés aux formules techniques de mélange
        (béton, mortier) utilisées sur les devis.
    </p>

    <div class="row g-3">
        @forelse($templates as $template)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-semibold mb-0">
                            <a href="{{ route('expense-templates.show', $template) }}" class="text-decoration-none">
                                {{ $template->name }}
                            </a>
                        </h6>
                        <span class="badge bg-info text-dark small">{{ $template->output_unit }}</span>
                    </div>
                    @if($template->description)
                    <p class="text-muted small mb-2">{{ Str::limit($template->description, 80) }}</p>
                    @endif
                    <div class="d-flex gap-3 text-muted small mt-2">
                        <span><i class="bi bi-list-ul me-1"></i>{{ $template->items_count }} ligne(s)</span>
                        <span><i class="bi bi-boxes me-1"></i>{{ $template->output_quantity }} {{ $template->output_unit }} / application</span>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('expense-templates.show', $template) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye"></i>
                    </a>
                    @can('expense_templates.edit')
                    <a href="{{ route('expense-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endcan
                    @can('expense_templates.delete')
                    <form method="POST" action="{{ route('expense-templates.destroy', $template) }}"
                          onsubmit="return confirm('Supprimer ce modèle ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="bi bi-receipt-cutoff fs-1 d-block mb-2"></i>
                Aucun modèle de dépense.<br>
                <a href="{{ route('expense-templates.create') }}" class="btn btn-primary mt-3">Créer le premier modèle</a>
            </div>
        </div>
        @endforelse
    </div>
</x-layouts.app>
