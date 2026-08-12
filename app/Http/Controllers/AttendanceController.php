<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['project.tasks', 'employee'])->orderByDesc('work_date');
        if (Auth::user()->hasRole('chef_chantier')) {
            $query->whereIn('project_id', Auth::user()->managedProjects()->pluck('projects.id'));
        }

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($employeeId = $request->input('employee_id')) {
            $query->where('employee_id', $employeeId);
        }
        if ($from = $request->input('date_from')) {
            $query->where('work_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->where('work_date', '<=', $to);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $attendances = $query->with('task')->paginate(25)->withQueryString();
        $projects    = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();
        $employees   = Auth::user()->company->employees()->orderBy('last_name')->get();

        return view('attendances.index', compact('attendances', 'projects', 'employees'));
    }

    public function create(Request $request)
    {
        [$projects, $projectEmployeesMap, $projectTasksMap] = $this->projectsWithMaps();
        $selected = $request->input('project_id');

        return view('attendances.form', compact('projects', 'selected', 'projectEmployeesMap', 'projectTasksMap'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAttendance($request);
        $this->authorizeProjectScope($data['project_id']);
        $data['created_by'] = Auth::id();
        $this->calcHours($data);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->uploadAndProcessPhoto($request->file('photo'));
        }

        Attendance::create($data);

        return redirect()->route('attendances.index')
            ->with('success', 'Pointage enregistré.');
    }

    public function edit(Attendance $attendance)
    {
        $this->authorize('attendances.edit');
        $this->authorizeProjectScope($attendance->project_id);
        [$projects, $projectEmployeesMap, $projectTasksMap] = $this->projectsWithMaps();

        // Union défensive : si le salarié/la tâche de ce pointage historique n'est plus
        // dans la liste filtrée (désaffecté depuis, tâche supprimée...), on l'ajoute quand
        // même pour ce chantier afin que le select ne soit pas vide à l'édition.
        if ($attendance->employee && !collect($projectEmployeesMap[$attendance->project_id] ?? [])->contains('id', $attendance->employee_id)) {
            $projectEmployeesMap[$attendance->project_id][] = ['id' => $attendance->employee->id, 'label' => $attendance->employee->full_name];
        }
        if ($attendance->task && !collect($projectTasksMap[$attendance->project_id] ?? [])->contains('id', $attendance->task_id)) {
            $projectTasksMap[$attendance->project_id][] = ['id' => $attendance->task->id, 'label' => $attendance->task->title];
        }

        return view('attendances.form', compact('attendance', 'projects', 'projectEmployeesMap', 'projectTasksMap'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('attendances.edit');
        $this->authorizeProjectScope($attendance->project_id);
        $data = $this->validateAttendance($request);
        $this->authorizeProjectScope($data['project_id']);
        $this->calcHours($data);

        if ($request->hasFile('photo')) {
            if ($attendance->photo_path) {
                Storage::disk('public')->delete($attendance->photo_path);
            }
            $data['photo_path'] = $this->uploadAndProcessPhoto($request->file('photo'));
        }

        $attendance->update($data);

        return redirect()->route('attendances.index')
            ->with('success', 'Pointage mis à jour.');
    }

    public function destroy(Attendance $attendance)
    {
        $this->authorize('attendances.delete');
        $this->authorizeProjectScope($attendance->project_id);
        if ($attendance->photo_path) {
            Storage::disk('public')->delete($attendance->photo_path);
        }
        $attendance->delete();
        return redirect()->route('attendances.index')
            ->with('success', 'Pointage supprimé.');
    }

    /**
     * Endpoint étroit : modification de la seule tâche d'un pointage.
     * Réservé au jour même — passé ce délai, seule l'édition complète
     * (attendances.edit) permet de corriger un pointage historique, quel
     * que soit le rôle (y compris admin/manager, par choix de design).
     */
    public function updateTask(Request $request, Attendance $attendance)
    {
        $this->authorize('attendances.view');
        $this->authorizeProjectScope($attendance->project_id);
        abort_unless($attendance->work_date->isToday(), 403, "Ce pointage ne peut plus être modifié : la journée est passée.");

        $data = $request->validate([
            'task_id'   => ['nullable', Rule::exists('tasks', 'id')->where(fn ($q) => $q->where('project_id', $attendance->project_id))],
            'task_note' => ['nullable', 'string', 'max:255'],
        ], [
            'task_id.exists' => "Cette tâche n'appartient pas au chantier de ce pointage.",
        ]);

        $attendance->update($data);

        return back()->with('success', 'Tâche mise à jour.');
    }

    public function recap(Request $request)
    {
        $month     = $request->input('month', now()->format('Y-m'));
        $projectId = $request->input('project_id');
        $isChefChantier = Auth::user()->hasRole('chef_chantier');

        [$year, $mon] = explode('-', $month);

        $query = Attendance::with('employee')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $mon)
            ->where('status', 'present');

        if ($isChefChantier) {
            $query->whereIn('project_id', Auth::user()->managedProjects()->pluck('projects.id'));
        }
        if ($projectId) {
            $this->authorizeProjectScope((int) $projectId);
            $query->where('project_id', $projectId);
        }

        $rows = $query->get()
            ->groupBy('employee_id')
            ->map(function ($items) {
                $emp = $items->first()->employee;
                return [
                    'employee'     => $emp,
                    'total_hours'  => $items->sum('hours_worked'),
                    'total_days'   => $items->sum('days_worked'),
                    'daily_rate'   => $emp->daily_rate ?? 0,
                    'salary_est'   => $items->sum('days_worked') * ($emp->daily_rate ?? 0),
                ];
            })->values();

        $projects = $isChefChantier
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();

        return view('attendances.recap', compact('rows', 'month', 'projects', 'projectId'));
    }

    public function exportCsv(Request $request)
    {
        $month     = $request->input('month', now()->format('Y-m'));
        $projectId = $request->input('project_id');
        $isChefChantier = Auth::user()->hasRole('chef_chantier');

        [$year, $mon] = explode('-', $month);

        $query = Attendance::with('employee')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $mon)
            ->where('status', 'present');

        if ($isChefChantier) {
            $query->whereIn('project_id', Auth::user()->managedProjects()->pluck('projects.id'));
        }
        if ($projectId) {
            $this->authorizeProjectScope((int) $projectId);
            $query->where('project_id', $projectId);
        }

        $rows = $query->get()
            ->groupBy('employee_id')
            ->map(function ($items) {
                $emp = $items->first()->employee;
                return [
                    'employee'     => $emp,
                    'total_hours'  => $items->sum('hours_worked'),
                    'total_days'   => $items->sum('days_worked'),
                    'daily_rate'   => $emp->daily_rate ?? 0,
                    'salary_est'   => $items->sum('days_worked') * ($emp->daily_rate ?? 0),
                ];
            })->values();

        $filename = 'recap-pointage-' . $month . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows, $month) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($handle, ['Mois', $month], ';');
            fputcsv($handle, ['Nom', 'Prénom', 'Heures totales', 'Jours travaillés', 'Taux journalier (MGA)', 'Salaire estimé (MGA)'], ';');
            foreach ($rows as $row) {
                $emp = $row['employee'];
                fputcsv($handle, [
                    $emp->last_name ?? '',
                    $emp->first_name ?? '',
                    number_format($row['total_hours'], 2, '.', ''),
                    number_format($row['total_days'], 2, '.', ''),
                    number_format($row['daily_rate'], 2, '.', ''),
                    number_format($row['salary_est'], 2, '.', ''),
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    /**
     * Chantiers visibles (scopés chef_chantier) avec, pour chacun, la liste des
     * salariés activement affectés et des tâches du chantier — sert à la fois à
     * peupler les selects du formulaire et à embarquer les maps JSON de filtrage
     * dynamique côté client (comme dans salary-payments/form.blade.php).
     */
    private function projectsWithMaps(): array
    {
        $projects = (Auth::user()->hasRole('chef_chantier') ? Auth::user()->managedProjects() : Project::query())
            ->orderBy('name')
            ->with([
                'employees' => fn ($q) => $q->wherePivot('is_active', true)->orderBy('last_name'),
                'tasks' => fn ($q) => $q->orderBy('title'),
            ])
            ->get();

        $employeesMap = $projects->mapWithKeys(fn ($p) => [
            $p->id => $p->employees->map(fn ($e) => ['id' => $e->id, 'label' => $e->full_name])->values()->toArray(),
        ])->toArray();

        $tasksMap = $projects->mapWithKeys(fn ($p) => [
            $p->id => $p->tasks->map(fn ($t) => ['id' => $t->id, 'label' => $t->title])->values()->toArray(),
        ])->toArray();

        return [$projects, $employeesMap, $tasksMap];
    }

    private function validateAttendance(Request $request): array
    {
        // Strip seconds if browser or DB sends HH:MM:SS
        if ($request->check_in) {
            $request->merge(['check_in' => substr($request->check_in, 0, 5)]);
        }
        if ($request->check_out) {
            $request->merge(['check_out' => substr($request->check_out, 0, 5)]);
        }

        return $request->validate([
            'project_id'  => ['required', 'exists:projects,id'],
            'employee_id' => [
                'required',
                Rule::exists('project_employees', 'employee_id')->where(function ($q) use ($request) {
                    $q->where('project_id', $request->input('project_id'))->where('is_active', true);
                }),
            ],
            'task_id'     => [
                'nullable',
                Rule::exists('tasks', 'id')->where(fn ($q) => $q->where('project_id', $request->input('project_id'))),
            ],
            'task_note'   => ['nullable', 'string', 'max:255'],
            'work_date'   => ['required', 'date'],
            'photo'       => ['nullable', 'image', 'max:5120'],
            'check_in'    => ['nullable', 'date_format:H:i'],
            'check_out'   => ['nullable', 'date_format:H:i', 'after:check_in'],
            'break_hours' => ['nullable', 'numeric', 'min:0', 'max:12'],
            'hours_worked'=> ['nullable', 'numeric', 'min:0', 'max:24'],
            'days_worked' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'status'      => ['required', 'in:present,absent_justifie,absent_non_justifie'],
            'notes'       => ['nullable', 'string'],
        ], [
            'employee_id.exists' => "Ce salarié n'est pas affecté (ou plus actif) sur le chantier sélectionné.",
            'task_id.exists' => "Cette tâche n'appartient pas au chantier sélectionné.",
        ]);
    }

    private function uploadAndProcessPhoto(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = 'attendance_' . uniqid() . '.webp';
        $path = 'pointages/' . now()->format('Y/m/d') . '/' . $filename;

        // scaleDown : réduit pour tenir dans 800x800 en conservant les proportions,
        // sans jamais agrandir une image plus petite (équivalent du "upsize" v2).
        $img = Image::read($file->getRealPath())
            ->scaleDown(800, 800)
            ->toWebp(80);

        Storage::disk('public')->put($path, (string) $img);

        return $path;
    }

    private function calcHours(array &$data): void
    {
        if (!empty($data['check_in']) && !empty($data['check_out'])) {
            $data['break_hours'] ??= Attendance::DEFAULT_BREAK_HOURS;
            $computed = Attendance::computeHours($data['check_in'], $data['check_out'], $data['break_hours']);
            if ($computed['hours_worked'] !== null) {
                $data['hours_worked'] = $computed['hours_worked'];
                $data['days_worked']  = $computed['days_worked'];
            }
        }
    }
}
