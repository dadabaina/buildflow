<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\JobType;
use App\Models\Region;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $company = auth()->user()->company;
        $query = $company->employees()->with(['jobTypes', 'region'])->latest();

        if ($request->input('archived')) {
            $query->onlyTrashed();
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }
        if ($jobType = $request->input('job_type_id')) {
            $query->where('job_type_id', $jobType);
        }
        if ($regionId = $request->input('region_id')) {
            $query->where('region_id', $regionId);
        }

        $employees = $query->paginate(20)->withQueryString();
        $jobTypes  = $company->jobTypes()->orderBy('name')->get();
        $regions   = $company->regions()->orderBy('name')->get();

        $stats = [
            'total_count' => $company->employees()->count(),
            'internal_count' => $company->employees()->whereNull('supplier_id')->count(),
            'external_count' => $company->employees()->whereNotNull('supplier_id')->count(),
        ];

        return view('employees.index', compact('employees', 'jobTypes', 'regions', 'stats'));
    }

    public function create()
    {
        $jobTypes  = JobType::orderBy('name')->get();
        $regions   = Region::orderBy('name')->get();
        $suppliers = Supplier::where('type', '!=', 'fournisseur')->orderBy('name')->get();
        return view('employees.form', compact('jobTypes', 'regions', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request);
        $jobTypeIds = $validated['job_type_ids'] ?? [];

        if (!empty($validated['job_type_id']) && !in_array($validated['job_type_id'], $jobTypeIds)) {
            $jobTypeIds[] = $validated['job_type_id'];
        }

        unset($validated['job_type_ids'], $validated['photo']);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $this->uploadAndProcessPhoto($request->file('photo'));
        }

        $employee = Employee::create($validated);
        $employee->jobTypes()->sync($jobTypeIds);

        return redirect()->route('employees.index')
            ->with('success', 'Employé créé.');
    }

    public function show(Employee $employee, Request $request)
    {
        $employee->load([
            'jobTypes',
            'region',
            'supplier',
            'projects' => fn($q) => $q->latest()->take(10),
            'attendances' => fn($q) => $q->with('project')->latest()->take(15),
        ]);

        $stats = [
            'projects_count' => $employee->projects()->count(),
            'total_days'     => $employee->attendances()->sum('days_worked'),
            'total_hours'    => $employee->attendances()->sum('hours_worked'),
        ];

        // Estimation gains (si journalier)
        $stats['total_earned'] = $stats['total_days'] * ($employee->daily_rate ?? 0);

        $year = (int) $request->get('year', now()->year);
        $monthlyHours = $employee->attendances()
            ->selectRaw('MONTH(work_date) as month, SUM(hours_worked) as total')
            ->whereYear('work_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');
        $years = range(now()->year, now()->year - 4);

        return view('employees.show', compact('employee', 'stats', 'monthlyHours', 'year', 'years'));
    }

    public function edit(Employee $employee)
    {
        $jobTypes  = JobType::orderBy('name')->get();
        $regions   = Region::orderBy('name')->get();
        $suppliers = Supplier::where('type', '!=', 'fournisseur')->orderBy('name')->get();
        return view('employees.form', compact('employee', 'jobTypes', 'regions', 'suppliers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request);
        $jobTypeIds = $validated['job_type_ids'] ?? [];

        if (!empty($validated['job_type_id']) && !in_array($validated['job_type_id'], $jobTypeIds)) {
            $jobTypeIds[] = $validated['job_type_id'];
        }

        unset($validated['job_type_ids'], $validated['photo']);

        // Un champ photo vide = on garde la photo actuelle (comme partout ailleurs dans l'app).
        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $validated['photo_path'] = $this->uploadAndProcessPhoto($request->file('photo'));
        }

        $employee->update($validated);
        $employee->jobTypes()->sync($jobTypeIds);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employé mis à jour.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', 'Employé archivé.');
    }

    public function restore(int $id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();
        return redirect()->route('employees.index')
            ->with('success', 'Employé restauré.');
    }

    /**
     * Redimensionne la photo en miniature carrée (300x300, recadrage centré) et
     * l'encode en WebP pour un poids réduit — même approche que les photos de pointage.
     */
    private function uploadAndProcessPhoto(\Illuminate\Http\UploadedFile $file): string
    {
        $filename = 'employee_' . uniqid() . '.webp';
        $path = 'employees/' . $filename;

        $img = Image::read($file->getRealPath())
            ->cover(300, 300)
            ->toWebp(85);

        Storage::disk('public')->put($path, (string) $img);

        return $path;
    }

    private function validateEmployee(Request $request): array
    {
        return $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'photo'           => ['nullable', 'image', 'max:5120'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:191'],
            'address'         => ['nullable', 'string', 'max:500'],
            'job_type_id'     => ['nullable', 'exists:job_types,id'],
            'job_type_ids'    => ['nullable', 'array'],
            'job_type_ids.*'  => ['exists:job_types,id'],
            'region_id'       => ['nullable', 'exists:regions,id'],
            'supplier_id'     => ['nullable', 'exists:suppliers,id'],
            'matricule'       => ['nullable', 'string', 'max:50'],
            'contract_type'   => ['nullable', 'in:cdi,cdd,interim,journalier,mensuel,sous_traitant'],
            'daily_rate'      => ['nullable', 'numeric', 'min:0'],
            'monthly_salary'  => ['nullable', 'numeric', 'min:0'],
            'rib'             => ['nullable', 'string', 'max:100'],
            'hire_date'       => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date'],
            'notes'           => ['nullable', 'string'],
        ]);
    }
}
