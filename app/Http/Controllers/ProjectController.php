<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['client', 'region', 'amendments'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        $projects = $query->paginate(20)->withQueryString();
        $statuses = array_keys(Project::$statusTransitions);
        $clients  = Client::orderBy('name')->get();

        $stats = [
            'total_count'    => Project::count(),
            'active_count'   => Project::where('status', 'en_cours')->count(),
            'total_contract' => Project::sum('contract_amount') + \App\Models\Amendment::where('status', 'accepte')->sum('total_ttc'),
        ];

        return view('projects.index', compact('projects', 'statuses', 'clients', 'stats'));
    }

    public function create()
    {
        $clients   = Client::orderBy('name')->get();
        $regions   = Region::orderBy('name')->get();
        $employees = Employee::with('jobTypes.category')->orderBy('last_name')->get();
        $jobTypes  = \App\Models\JobType::with('category')->where('company_id', currentCompany()->id)->orderBy('name')->get();
        return view('projects.form', compact('clients', 'regions', 'employees', 'jobTypes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);
        unset($validated['employee_ids']);

        $project = Project::create($validated);

        \App\Models\ProjectLog::log(
            $project->id,
            'project_created',
            "Le chantier a été initialisé."
        );

        return redirect()->route('projects.show', $project)
            ->with('success', 'Chantier créé. Vous pouvez maintenant définir les besoins et l\'équipe.');
    }

    public function show(Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $project->load([
            'client', 'region', 'employees.jobTypes', 'requirements.jobType.category',
            'expenses'       => fn($q) => $q->with(['category', 'task'])->latest()->take(10),
            'quotes'         => fn($q) => $q->latest()->take(5),
            'invoices'       => fn($q) => $q->latest()->take(5),
            'amendments'     => fn($q) => $q->latest()->take(10),
            'purchaseOrders' => fn($q) => $q->with('supplier')->latest()->take(10),
            'tasks'          => fn($q) => $q->with(['employees', 'quoteItem'])
                ->withSum(['expenses as validated_expenses_total' => fn($q2) => $q2->where('status', 'validee')], 'total_amount')
                ->orderBy('sort_order')->orderBy('id'),
            'attendances'    => fn($q) => $q->with('employee')->orderBy('work_date', 'desc')->take(30),
            'documents'      => fn($q) => $q->with('uploadedBy')->latest()->take(20),
            'warehouses',
            'projectLogs'    => fn($q) => $q->with('user')->latest()->take(50),
        ]);

        // Stock du chantier (mouvements liés soit via le dépôt du chantier, soit taggués project_id)
        $warehouseIds = $project->warehouses->pluck('id')->toArray();
        
        $stockByItem = \App\Models\StockMovement::where('company_id', $project->company_id)
            ->where(function($q) use ($project, $warehouseIds) {
                $q->where('project_id', $project->id)
                  ->orWhereIn('warehouse_id', $warehouseIds);
            })
            ->selectRaw('item_name, unit, SUM(CASE WHEN type="entree" THEN quantity WHEN type="sortie" THEN -quantity ELSE 0 END) as balance')
            ->groupBy('item_name', 'unit')
            ->having('balance', '>', 0)
            ->get();

        $jobTypes = \App\Models\JobType::with('category')->where('company_id', currentCompany()->id)->orderBy('name')->get();
        $stockAlerts = $project->getLowStockMaterials();
        $materials = \App\Models\Material::where('company_id', $project->company_id)->orderBy('name')->get();

        return view('projects.show', compact('project', 'stockByItem', 'jobTypes', 'stockAlerts', 'materials'));
    }

    public function updateThreshold(Request $request, Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $validated = $request->validate([
            'material_id'   => ['required', 'exists:materials,id'],
            'min_threshold' => ['required', 'numeric', 'min:0'],
        ]);

        $project->materialThresholds()->updateOrCreate(
            ['material_id' => $validated['material_id'], 'company_id' => $project->company_id],
            ['min_threshold' => $validated['min_threshold']]
        );

        return back()->with('success', 'Seuil d\'alerte mis à jour.');
    }

    public function assignEquipment(Request $request, Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $validated = $request->validate([
            'equipment_id' => ['required', 'exists:equipments,id'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes'        => ['nullable', 'string'],
        ]);

        $equipment = \App\Models\Equipment::findOrFail($validated['equipment_id']);
        
        $project->projectAssignments()->create([
            'company_id'   => $project->company_id,
            'equipment_id' => $equipment->id,
            'start_date'   => $validated['start_date'],
            'end_date'     => $validated['end_date'] ?? null,
            'daily_cost'   => $equipment->daily_rental_cost ?? 0,
            'notes'        => $validated['notes'] ?? null,
        ]);

        $equipment->update(['status' => 'affecte']);

        return back()->with('success', 'Matériel affecté au chantier.');
    }

    public function detachEquipment(Project $project, \App\Models\ProjectEquipment $assignment)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        abort_if($assignment->project_id !== $project->id, 404);

        $equipment = $assignment->equipment;
        $assignment->delete();
        
        // Remettre en disponible si plus aucune affectation en cours
        $equipment->update(['status' => 'disponible']);

        return back()->with('success', 'Matériel libéré.');
    }

    public function storeRequirement(Request $request, Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);

        $validated = $request->validate([
            'job_type_id'     => ['required', 'exists:job_types,id'],
            'needed_quantity' => ['required', 'integer', 'min:1'],
            'notes'           => ['nullable', 'string', 'max:255'],
        ]);

        $project->requirements()->updateOrCreate(
            ['job_type_id' => $validated['job_type_id']],
            $validated
        );

        return back()->with('success', 'Besoin en effectif mis à jour.');
    }

    public function destroyRequirement(Project $project, \App\Models\ProjectRequirement $requirement)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        abort_if($requirement->project_id !== $project->id, 404);

        $requirement->delete();

        return back()->with('success', 'Besoin supprimé.');
    }

    public function edit(Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $clients   = Client::orderBy('name')->get();
        $regions   = Region::orderBy('name')->get();
        $employees = Employee::with('jobTypes.category')->orderBy('last_name')->get();
        $jobTypes  = \App\Models\JobType::with('category')->where('company_id', currentCompany()->id)->orderBy('name')->get();
        return view('projects.form', compact('project', 'clients', 'regions', 'employees', 'jobTypes'));
    }

    public function update(Request $request, Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $validated = $this->validateProject($request);
        unset($validated['employee_ids']);

        // Le chef de chantier gère la fiche opérationnelle mais pas les champs financiers/contractuels.
        if (Auth::user()->hasRole('chef_chantier')) {
            unset(
                $validated['contract_amount'], $validated['budget_total'],
                $validated['tva_rate'], $validated['rg_rate'],
                $validated['start_date'], $validated['planned_end_date']
            );
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Chantier mis à jour.');
    }

    public function destroy(Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorize('projects.delete');
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', 'Chantier supprimé.');
    }

    public function updateStatus(Request $request, Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $request->validate(['status' => ['required', 'string']]);

        if (!$project->canTransitionTo($request->status)) {
            return back()->with('error', 'Transition de statut non autorisée.');
        }

        $oldStatus = $project->status;
        $project->update(['status' => $request->status]);

        \App\Models\ProjectLog::log(
            $project->id,
            'status_updated',
            "Le statut du chantier a été modifié de '{$oldStatus}' à '{$request->status}'."
        );

        return back()->with('success', 'Statut mis à jour.');
    }

    public function syncEmployees(Request $request, Project $project)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $request->validate([
            'employee_ids'   => ['nullable', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
        ]);

        $project->employees()->sync($request->employee_ids ?? []);

        \App\Models\ProjectLog::log(
            $project->id,
            'team_updated',
            "L'équipe du chantier a été mise à jour (" . count($request->employee_ids ?? []) . " collaborateurs)."
        );

        return back()->with('success', 'Équipe mise à jour avec succès.');
    }

    public function detachEmployee(Project $project, Employee $employee)
    {
        abort_if($project->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($project->id);
        $project->employees()->detach($employee->id);

        \App\Models\ProjectLog::log(
            $project->id,
            'employee_removed',
            "{$employee->full_name} a été retiré de l'équipe du chantier."
        );

        return back()->with('success', "{$employee->full_name} a été retiré du chantier.");
    }

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:191'],
            'reference'        => ['nullable', 'string', 'max:50'],
            'client_id'        => ['required', 'exists:clients,id'],
            'region_id'        => ['nullable', 'exists:regions,id'],
            'address'          => ['nullable', 'string', 'max:500'],
            'latitude'         => ['nullable', 'numeric'],
            'longitude'        => ['nullable', 'numeric'],
            'description'      => ['nullable', 'string'],
            'start_date'       => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_amount'  => ['nullable', 'numeric', 'min:0'],
            'budget_total'     => ['nullable', 'numeric', 'min:0'],
            'tva_rate'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rg_rate'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'            => ['nullable', 'string'],
            'status'           => ['sometimes', 'string'],
            'employee_ids'     => ['nullable', 'array'],
            'employee_ids.*'   => ['exists:employees,id'],
        ]);
    }
}
