<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['project', 'employee'])->orderByDesc('work_date');

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

        $attendances = $query->paginate(25)->withQueryString();
        $projects    = Project::orderBy('name')->get();
        $employees   = Employee::orderBy('last_name')->get();

        return view('attendances.index', compact('attendances', 'projects', 'employees'));
    }

    public function create(Request $request)
    {
        $projects  = Project::orderBy('name')->get();
        $employees = Employee::orderBy('last_name')->get();
        $selected  = $request->input('project_id');
        return view('attendances.form', compact('projects', 'employees', 'selected'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAttendance($request);
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
        $projects  = Project::orderBy('name')->get();
        $employees = Employee::orderBy('last_name')->get();
        return view('attendances.form', compact('attendance', 'projects', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $this->validateAttendance($request);
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
        if ($attendance->photo_path) {
            Storage::disk('public')->delete($attendance->photo_path);
        }
        $attendance->delete();
        return redirect()->route('attendances.index')
            ->with('success', 'Pointage supprimé.');
    }

    public function recap(Request $request)
    {
        $month     = $request->input('month', now()->format('Y-m'));
        $projectId = $request->input('project_id');

        [$year, $mon] = explode('-', $month);

        $query = Attendance::with('employee')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $mon)
            ->where('status', 'present');

        if ($projectId) {
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

        $projects = Project::orderBy('name')->get();

        return view('attendances.recap', compact('rows', 'month', 'projects', 'projectId'));
    }

    public function exportCsv(Request $request)
    {
        $month     = $request->input('month', now()->format('Y-m'));
        $projectId = $request->input('project_id');

        [$year, $mon] = explode('-', $month);

        $query = Attendance::with('employee')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $mon)
            ->where('status', 'present');

        if ($projectId) {
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
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date'   => ['required', 'date'],
            'photo'       => ['nullable', 'image', 'max:5120'],
            'check_in'    => ['nullable', 'date_format:H:i'],
            'check_out'   => ['nullable', 'date_format:H:i', 'after:check_in'],
            'break_hours' => ['nullable', 'numeric', 'min:0', 'max:12'],
            'hours_worked'=> ['nullable', 'numeric', 'min:0', 'max:24'],
            'days_worked' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'status'      => ['required', 'in:present,absent_justifie,absent_non_justifie'],
            'notes'       => ['nullable', 'string'],
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
            [$h1, $m1] = explode(':', $data['check_in']);
            [$h2, $m2] = explode(':', $data['check_out']);
            $minutes = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
            $minutes -= (float) ($data['break_hours'] ?? 0) * 60;
            if ($minutes > 0) {
                $data['hours_worked'] = round($minutes / 60, 2);
                $data['days_worked']  = round($minutes / 60 / 8, 2);
            }
        }
    }
}
