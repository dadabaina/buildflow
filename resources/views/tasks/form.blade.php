<x-layouts.app :title="isset($task) ? 'Modifier : ' . $task->title : 'Nouvelle tâche'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Tâches</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($task) ? 'Modifier' : 'Nouvelle' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-kanban me-2"></i>
                        {{ isset($task) ? 'Modifier la tâche' : 'Nouvelle tâche' }}
                    </h5>
                </div>

                <form method="POST"
                      action="{{ isset($task) ? route('tasks.update', $task) : route('tasks.store') }}"
                      x-data="{
                          checklistItems: {{ json_encode(
                              old('checklist',
                                  isset($task) && $task->checklist
                                      ? $task->checklist
                                      : [['label'=>'','done'=>false]]
                              )
                          ) }},
                          addItem() { this.checklistItems.push({label:'',done:false}); },
                          removeItem(i) { if(this.checklistItems.length>1) this.checklistItems.splice(i,1); }
                      }">
                    @csrf
                    @if(isset($task)) @method('PUT') @endif

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $task->title ?? '') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Chantier <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">— Choisir —</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}"
                                            @selected(old('project_id', $task->project_id ?? $selected) == $p->id)>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    @foreach(['a_faire'=>'À faire','en_cours'=>'En cours','en_pause'=>'En pause','termine'=>'Terminée','annule'=>'Annulée'] as $val=>$lbl)
                                        <option value="{{ $val }}" @selected(old('status', $task->status ?? 'a_faire') == $val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priorité</label>
                                <select name="priority" class="form-select">
                                    @foreach(['basse'=>'Basse','normale'=>'Normale','haute'=>'Haute','urgente'=>'Urgente'] as $val=>$lbl)
                                        <option value="{{ $val }}" @selected(old('priority', $task->priority ?? 'normale') == $val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date d'échéance</label>
                                <input type="date" name="due_date" class="form-control"
                                       value="{{ old('due_date', isset($task) && $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-flex align-items-center">
                                    Poids / Importance
                                    <i class="bi bi-info-circle ms-1 text-primary cursor-pointer" 
                                       data-bs-toggle="tooltip" 
                                       title="Définit l'importance de cette tâche dans le calcul de l'avancement global du chantier. Une tâche de poids 5 compte 5 fois plus qu'une tâche de poids 1."></i>
                                </label>
                                <input type="number" name="weight" class="form-control @error('weight') is-invalid @enderror"
                                       value="{{ old('weight', $task->weight ?? 1) }}" min="1" required>
                                <div class="form-text">Valeur par défaut : 1. Augmentez pour les tâches majeures.</div>
                                @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $task->description ?? '') }}</textarea>
                            </div>

                            {{-- Assignés --}}
                            <div class="col-12">
                                <label class="form-label">Assigné(e)s</label>
                                <div class="row g-1">
                                    @foreach($employees as $emp)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="employee_ids[]" value="{{ $emp->id }}"
                                                       id="emp_{{ $emp->id }}"
                                                       @checked(in_array($emp->id, old('employee_ids',
                                                           isset($task) ? $task->employees->pluck('id')->toArray() : []
                                                       )))>
                                                <label class="form-check-label" for="emp_{{ $emp->id }}">
                                                    {{ $emp->first_name }} {{ $emp->last_name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Checklist --}}
                            <div class="col-12">
                                <label class="form-label">Checklist</label>
                                <template x-for="(ci, idx) in checklistItems" :key="idx">
                                    <div class="input-group input-group-sm mb-1">
                                        <div class="input-group-text">
                                            <input type="checkbox" :name="`checklist[${idx}][done]`"
                                                   x-model="ci.done" value="1">
                                        </div>
                                        <input type="text" :name="`checklist[${idx}][label]`"
                                               x-model="ci.label" class="form-control" placeholder="Élément…">
                                        <button type="button" class="btn btn-outline-danger" @click="removeItem(idx)">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-1" @click="addItem()">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>{{ isset($task) ? 'Mettre à jour' : 'Créer la tâche' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
