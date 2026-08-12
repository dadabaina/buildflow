<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Project;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalaryPaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('salary_payments.view');

        $company = Auth::user()->company;
        $query   = $company->salaryPayments()->with(['employee', 'projects']);

        if (Auth::user()->hasRole('chef_chantier')) {
            $managedIds = Auth::user()->managedProjects()->pluck('projects.id');
            $query->whereHas('projects', fn ($q) => $q->whereIn('projects.id', $managedIds));
        }
        if ($employeeId = $request->employee_id) {
            $query->where('employee_id', $employeeId);
        }
        if ($projectId = $request->project_id) {
            $query->whereHas('projects', fn ($q) => $q->where('projects.id', $projectId));
        }
        if ($from = $request->date_from) {
            $query->where('payment_date', '>=', $from);
        }
        if ($to = $request->date_to) {
            $query->where('payment_date', '<=', $to);
        }

        $payments  = $query->orderByDesc('payment_date')->paginate(20)->withQueryString();
        $employees = $company->employees()->orderBy('last_name')->get();
        $projects  = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();

        return view('salary-payments.index', compact('payments', 'employees', 'projects'));
    }

    public function create(Request $request)
    {
        $this->authorize('salary_payments.create');

        $company   = Auth::user()->company;
        $employees = $company->employees()
            ->where('is_active', true)
            ->whereHas('projects', fn ($q) => $q->where('project_employees.is_active', true))
            ->with(['projects' => fn ($q) => $q->wherePivot('is_active', true)])
            ->orderBy('last_name')
            ->get();
        $projects  = Project::orderBy('name')->get();

        $employeeProjectsMap = $employees->mapWithKeys(fn ($e) => [
            $e->id => $e->projects->map(fn ($p) => ['id' => $p->id, 'label' => $p->name])->values()->toArray(),
        ])->toArray();

        $selectedEmployeeId = $request->employee_id;
        $selectedProjectId  = $request->project_id;

        return view('salary-payments.form', compact('employees', 'projects', 'employeeProjectsMap', 'selectedEmployeeId', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $this->authorize('salary_payments.create');

        $data = $request->validate([
            'employee_id'               => ['required', 'exists:employees,id'],
            'payment_date'              => ['required', 'date'],
            'period_start'              => ['nullable', 'date'],
            'period_end'                => ['nullable', 'date', 'after_or_equal:period_start'],
            'amount'                    => ['required', 'numeric', 'min:0.01'],
            'payment_mode'              => ['nullable', 'string', 'max:50'],
            'reference'                 => ['nullable', 'string', 'max:30'],
            'notes'                     => ['nullable', 'string'],
            'allocations'               => ['required', 'array', 'min:1'],
            'allocations.*.project_id'  => [
                'required',
                'distinct',
                Rule::exists('project_employees', 'project_id')->where(function ($q) use ($request) {
                    $q->where('employee_id', $request->input('employee_id'))->where('is_active', true);
                }),
            ],
            'allocations.*.amount'      => ['required', 'numeric', 'min:0.01'],
        ], [
            'allocations.*.project_id.exists' => "Ce salarié n'est pas affecté (ou plus actif) sur l'un des chantiers sélectionnés.",
        ]);

        $company  = Auth::user()->company;
        $employee = $company->employees()->findOrFail($data['employee_id']);

        $sumAllocations = collect($data['allocations'])->sum('amount');
        if (abs($sumAllocations - (float) $data['amount']) > 0.01) {
            return back()->withInput()->with('error', sprintf(
                "La somme des ventilations (%s) ne correspond pas au montant total saisi (%s).",
                number_format($sumAllocations, 0, ',', ' '),
                number_format((float) $data['amount'], 0, ',', ' ')
            ));
        }

        $payment = $company->salaryPayments()->create([
            'employee_id'  => $employee->id,
            'created_by'   => Auth::id(),
            'reference'    => $data['reference'] ?? null,
            'payment_date' => $data['payment_date'],
            'period_start' => $data['period_start'] ?? null,
            'period_end'   => $data['period_end'] ?? null,
            'amount'       => $data['amount'],
            'payment_mode' => $data['payment_mode'] ?? null,
            'notes'        => $data['notes'] ?? null,
        ]);

        foreach ($data['allocations'] as $alloc) {
            $payment->projects()->attach($alloc['project_id'], ['amount' => $alloc['amount']]);
        }

        return redirect()->route('salary-payments.index')
            ->with('success', 'Paiement salarié enregistré.');
    }

    public function destroy(SalaryPayment $salaryPayment)
    {
        $this->authorize('salary_payments.delete');
        abort_if($salaryPayment->company_id !== Auth::user()->company_id, 403);

        $salaryPayment->delete();

        return redirect()->route('salary-payments.index')
            ->with('success', 'Paiement salarié supprimé.');
    }

    /**
     * AJAX : récap de pointage par chantier pour un salarié sur une période,
     * utilisé pour pré-remplir indicativement le formulaire de ventilation.
     * Purement informatif : ne crée ni ne calcule rien d'officiel.
     */
    public function attendanceRecap(Request $request)
    {
        $this->authorize('salary_payments.create');

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'from'        => ['required', 'date'],
            'to'          => ['required', 'date', 'after_or_equal:from'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $rows = Attendance::with('project')
            ->where('employee_id', $employee->id)
            ->where('status', 'present')
            ->whereBetween('work_date', [$data['from'], $data['to']])
            ->get()
            ->groupBy('project_id')
            ->map(function ($items) use ($employee) {
                $project = $items->first()->project;
                $rate    = $employee->effectiveRateFor($project->id);
                $days    = (float) $items->sum('days_worked');

                $estimated = match ($rate['frequency']) {
                    'journalier'   => $days * (float) ($rate['daily_rate'] ?? 0),
                    'hebdomadaire' => $days * (float) ($rate['weekly_rate'] ?? 0) / 6,
                    'mensuel'      => (float) ($rate['monthly_salary'] ?? 0),
                    default        => 0,
                };

                return [
                    'project_id'       => $project->id,
                    'project_name'     => $project->name,
                    'total_days'       => $days,
                    'total_hours'      => (float) $items->sum('hours_worked'),
                    'frequency'        => $rate['frequency'],
                    'estimated_amount' => round($estimated, 2),
                ];
            })->values();

        return response()->json(['rows' => $rows]);
    }

    /**
     * Modifie le tarif négocié d'un salarié pour un chantier précis
     * (override sur le pivot project_employees).
     */
    public function updateEmployeeRate(Request $request, Project $project, Employee $employee)
    {
        $this->authorize('employees.edit');
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);

        $data = $request->validate([
            'payment_frequency' => ['nullable', 'in:journalier,hebdomadaire,mensuel'],
            'daily_rate'        => ['nullable', 'numeric', 'min:0'],
            'weekly_rate'       => ['nullable', 'numeric', 'min:0'],
            'monthly_salary'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $project->employees()->updateExistingPivot($employee->id, $data);

        return back()->with('success', "Tarif de {$employee->full_name} mis à jour pour ce chantier.");
    }
}
