<x-layouts.app :title="isset($attendance) ? 'Modifier pointage' : 'Saisir pointage'">
    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none opacity-50 text-dark">Accueil</a></li>
        <li class="breadcrumb-item text-decoration-none opacity-50 text-dark">Pointage</li>
        <li class="breadcrumb-item active fw-bold text-dark">{{ isset($attendance) ? 'Modifier' : 'Saisir' }}</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        {{ isset($attendance) ? 'Modifier le pointage' : 'Saisir un pointage' }}
                    </h5>
                </div>

                <form method="POST"
                      action="{{ isset($attendance) ? route('attendances.update', $attendance) : route('attendances.store') }}"
                      enctype="multipart/form-data"
                      x-data="{
                          checkIn: '{{ old('check_in', isset($attendance) && $attendance->check_in ? substr($attendance->check_in, 0, 5) : '') }}',
                          checkOut: '{{ old('check_out', isset($attendance) && $attendance->check_out ? substr($attendance->check_out, 0, 5) : '') }}',
                          breakHours: '{{ old('break_hours', $attendance->break_hours ?? '') }}',
                          photoPreview: '{{ isset($attendance) && $attendance->photo_path ? asset('storage/' . $attendance->photo_path) : '' }}',
                          get hours() {
                              if(!this.checkIn || !this.checkOut) return '';
                              const [h1,m1] = this.checkIn.split(':').map(Number);
                              const [h2,m2] = this.checkOut.split(':').map(Number);
                              const mins = (h2*60+m2) - (h1*60+m1) - (parseFloat(this.breakHours) || 0) * 60;
                              return mins > 0 ? (mins/60).toFixed(2) : '';
                          },
                          get days() {
                              return this.hours ? (this.hours / 8).toFixed(2) : '';
                          },
                          handlePhoto(e) {
                              const file = e.target.files[0];
                              if (file) {
                                  this.photoPreview = URL.createObjectURL(file);
                              }
                          }
                      }">
                    @csrf
                    @if(isset($attendance)) @method('PUT') @endif

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 text-center mb-3">
                                <label class="form-label d-block fw-bold">Photo de présence (Temps réel)</label>
                                <div class="mx-auto border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" 
                                     style="width: 200px; height: 150px; cursor: pointer;"
                                     onclick="document.getElementById('photo-input').click()">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-100 h-100 object-fit-cover">
                                    </template>
                                    <template x-if="!photoPreview">
                                        <div class="text-center p-3">
                                            <i class="bx bx-camera fs-1 opacity-25"></i>
                                            <p class="small text-muted mb-0">Prendre photo</p>
                                        </div>
                                    </template>
                                </div>
                                <input type="file" name="photo" id="photo-input" class="d-none" 
                                       accept="image/*" capture="camera" @change="handlePhoto">
                                @error('photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Chantier <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">— Choisir —</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}"
                                            @selected(old('project_id', $attendance->project_id ?? $selected) == $p->id)>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employé <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                    <option value="">— Choisir —</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            @selected(old('employee_id', $attendance->employee_id ?? '') == $emp->id)>
                                            {{ $emp->first_name }} {{ $emp->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="work_date" class="form-control @error('work_date') is-invalid @enderror"
                                       value="{{ old('work_date', isset($attendance) ? $attendance->work_date->format('Y-m-d') : today()->format('Y-m-d')) }}" required>
                                @error('work_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="present" @selected(old('status', $attendance->status ?? 'present') == 'present')>Présent</option>
                                    <option value="absent_justifie" @selected(old('status', $attendance->status ?? '') == 'absent_justifie')>Absent justifié</option>
                                    <option value="absent_non_justifie" @selected(old('status', $attendance->status ?? '') == 'absent_non_justifie')>Absent non justifié</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jours travaillés</label>
                                <input type="number" name="days_worked" class="form-control @error('days_worked') is-invalid @enderror"
                                       :value="days || '{{ old('days_worked', $attendance->days_worked ?? '') }}'"
                                       step="0.25" min="0" max="1" placeholder="0.5, 1…">
                                @error('days_worked')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12"><hr class="my-1"><small class="text-muted">Optionnel : saisir les heures pour calcul automatique</small></div>

                            <div class="col-md-3">
                                <label class="form-label">Heure d'arrivée</label>
                                <input type="time" name="check_in" class="form-control @error('check_in') is-invalid @enderror"
                                       x-model="checkIn">
                                @error('check_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Heure de départ</label>
                                <input type="time" name="check_out" class="form-control @error('check_out') is-invalid @enderror"
                                       x-model="checkOut">
                                @error('check_out')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pause déjeuner (Heures)</label>
                                <input type="number" name="break_hours" class="form-control @error('break_hours') is-invalid @enderror"
                                       x-model="breakHours" step="0.25" min="0" max="12" placeholder="1">
                                @error('break_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Heures calculées</label>
                                <div class="form-control-plaintext fw-semibold text-primary" x-text="hours ? hours + 'h' : '—'"></div>
                                <input type="hidden" name="hours_worked" :value="hours">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $attendance->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('attendances.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>{{ isset($attendance) ? 'Mettre à jour' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
