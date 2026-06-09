<x-layouts.app title="Catégories de dépense">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item active fw-bold text-dark">Catégories de dépense</li>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-tags me-2 text-primary"></i>Catégories de dépense
            <span class="badge bg-secondary ms-2">{{ $categories->count() }}</span>
        </h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"
                onclick="openCreate()">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle catégorie
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Couleur</th>
                        <th>Nom</th>
                        <th>Icône</th>
                        <th>Statut</th>
                        <th>Dépenses</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td>
                            <span class="d-inline-block rounded" style="width:24px;height:24px;background:{{ $cat->color ?? '#6c757d' }};border:1px solid rgba(0,0,0,.1)"></span>
                        </td>
                        <td class="fw-medium">{{ $cat->name }}</td>
                        <td>
                            @if($cat->icon)
                            <i class="bi {{ $cat->icon }}"></i>
                            <small class="text-muted ms-1">{{ $cat->icon }}</small>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $cat->expenses()->count() }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick='openEdit({{ json_encode(["id"=>$cat->id,"name"=>$cat->name,"color"=>$cat->color,"icon"=>$cat->icon,"is_active"=>$cat->is_active]) }})'
                                        title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('expense-categories.destroy', $cat) }}"
                                      onsubmit="return confirm('Supprimer « {{ addslashes($cat->name) }} » ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucune catégorie</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Catégorie --}}
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="categoryForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="categoryMethod" value="">

                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalTitle">Nouvelle catégorie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="catName" class="form-control" required maxlength="100">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Couleur</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" name="color" id="catColor" class="form-control form-control-color" value="#4e73df" style="width:50px;">
                                    <input type="text" id="catColorText" class="form-control form-control-sm" value="#4e73df" maxlength="7"
                                           oninput="document.getElementById('catColor').value=this.value">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icône Bootstrap <small class="text-muted">(ex: bi-tools)</small></label>
                                <input type="text" name="icon" id="catIcon" class="form-control" placeholder="bi-receipt" maxlength="50">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input type="checkbox" name="is_active" id="catActive" class="form-check-input" value="1" checked>
                            <label class="form-check-label" for="catActive">Catégorie active</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const colorInput = document.getElementById('catColor');
    const colorText  = document.getElementById('catColorText');
    colorInput.addEventListener('input', () => colorText.value = colorInput.value);

    function openCreate() {
        document.getElementById('categoryModalTitle').textContent = 'Nouvelle catégorie';
        document.getElementById('categoryForm').action = '{{ route('expense-categories.store') }}';
        document.getElementById('categoryMethod').value = '';
        document.getElementById('catName').value = '';
        colorInput.value = '#4e73df';
        colorText.value  = '#4e73df';
        document.getElementById('catIcon').value = '';
        document.getElementById('catActive').checked = true;
    }

    function openEdit(data) {
        document.getElementById('categoryModalTitle').textContent = 'Modifier la catégorie';
        document.getElementById('categoryForm').action = '/expense-categories/' + data.id;
        document.getElementById('categoryMethod').value = 'PATCH';
        document.getElementById('catName').value  = data.name ?? '';
        const c = data.color ?? '#4e73df';
        colorInput.value = c;
        colorText.value  = c;
        document.getElementById('catIcon').value  = data.icon ?? '';
        document.getElementById('catActive').checked = !!data.is_active;
        new bootstrap.Modal(document.getElementById('categoryModal')).show();
    }
    </script>
</x-layouts.app>
