<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PointageKioskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $projects = $user->hasRole('chef_chantier')
            ? $user->managedProjects()->orderBy('name')->get()
            : Project::where('status', 'en_cours')->orderBy('name')->get();

        $project = null;
        $employees = collect();
        $todayByEmployee = collect();

        $projectId = $request->input('project_id') ?? $projects->first()?->id;
        if ($projectId) {
            $project = $projects->firstWhere('id', (int) $projectId);
            if ($project) {
                $employees = $project->employees()->wherePivot('is_active', true)->orderBy('last_name')->get();
                $todayByEmployee = Attendance::where('work_date', today()->toDateString())
                    ->whereIn('employee_id', $employees->pluck('id'))
                    ->get()
                    ->keyBy('employee_id');
            }
        }

        return view('pointage.kiosque', compact('projects', 'project', 'employees', 'todayByEmployee'));
    }

    public function entree(Request $request, Project $project, Employee $employee)
    {
        $this->authorizeProjectScope($project->id);
        $this->authorizeEmployeeOnProject($project, $employee);

        $request->validate([
            'photo'       => ['required', 'image', 'max:5120'],
            'captured_at' => ['nullable', 'date'],
        ]);

        // captured_at : horodatage pris côté appareil au moment de la photo (JS).
        // Utilisé tel quel s'il est fourni — notamment pour une entrée mise en
        // file d'attente hors-ligne et rejouée plus tard, où now() correspondrait
        // au moment de la synchronisation, pas au moment réel du pointage.
        $capturedAt = $request->filled('captured_at') ? \Carbon\Carbon::parse($request->input('captured_at')) : now();

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'work_date'   => $capturedAt->toDateString(),
        ]);

        if (!$attendance->exists) {
            $attendance->company_id = $project->company_id;
            $attendance->created_by = Auth::id();
        }

        $attendance->project_id      = $project->id;
        $attendance->status          = 'present';
        $attendance->check_in        = $capturedAt->format('H:i');
        $attendance->photo_path_in   = $this->processPhoto($request->file('photo'));
        $attendance->checked_in_by   = Auth::id();
        $attendance->save();

        return redirect()->route('pointage.kiosque', ['project_id' => $project->id])
            ->with('success', "Entrée pointée pour {$employee->full_name}.");
    }

    public function sortie(Request $request, Project $project, Employee $employee)
    {
        $this->authorizeProjectScope($project->id);
        $this->authorizeEmployeeOnProject($project, $employee);

        $request->validate([
            'photo'       => ['required', 'image', 'max:5120'],
            'captured_at' => ['nullable', 'date'],
        ]);

        $capturedAt = $request->filled('captured_at') ? \Carbon\Carbon::parse($request->input('captured_at')) : now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('work_date', $capturedAt->toDateString())
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return back()->with('error', "{$employee->full_name} n'a pas encore pointé son entrée aujourd'hui.");
        }

        $attendance->check_out      = $capturedAt->format('H:i');
        $attendance->photo_path_out = $this->processPhoto($request->file('photo'));
        $attendance->checked_out_by = Auth::id();

        [$h1, $m1] = explode(':', $attendance->check_in);
        [$h2, $m2] = explode(':', $attendance->check_out);
        $minutes = ($h2 * 60 + $m2) - ($h1 * 60 + $m1) - ((float) ($attendance->break_hours ?? 0) * 60);
        if ($minutes > 0) {
            $attendance->hours_worked = round($minutes / 60, 2);
            $attendance->days_worked  = round($minutes / 60 / 8, 2);
        }

        $attendance->save();

        return redirect()->route('pointage.kiosque', ['project_id' => $project->id])
            ->with('success', "Sortie pointée pour {$employee->full_name}.");
    }

    private function authorizeEmployeeOnProject(Project $project, Employee $employee): void
    {
        abort_unless(
            $project->employees()->where('employees.id', $employee->id)->wherePivot('is_active', true)->exists(),
            404
        );
    }

    private function processPhoto(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = 'pointage_' . uniqid() . '.webp';
        $path = 'pointages/' . now()->format('Y/m/d') . '/' . $filename;

        $img = Image::read($file->getRealPath())
            ->scaleDown(800, 800)
            ->toWebp(80);

        Storage::disk('public')->put($path, (string) $img);

        return $path;
    }
}
