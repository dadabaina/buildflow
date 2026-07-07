<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseTemplate;
use App\Models\ExpenseTemplateItem;
use App\Models\Material;
use App\Models\Task;
use App\Models\UnitType;
use App\Services\ExpenseTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseTemplateController extends Controller
{
    public function __construct(private ExpenseTemplateService $calculator) {}

    public function index()
    {
        $templates = ExpenseTemplate::where('company_id', currentCompany()->id)
            ->withCount('items')
            ->orderBy('name')
            ->get();

        return view('expense-templates.index', compact('templates'));
    }

    public function create()
    {
        $materials = Material::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $unitTypes = UnitType::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expense-templates.form', compact('materials', 'unitTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'description'     => 'nullable|string',
            'output_unit'     => 'required|string|max:30',
            'output_quantity' => 'required|numeric|min:0.001',
        ]);

        $template = ExpenseTemplate::create([
            ...$data,
            'company_id' => currentCompany()->id,
        ]);

        return redirect()->route('expense-templates.show', $template)->with('success', 'Modèle de dépense créé.');
    }

    public function show(ExpenseTemplate $expenseTemplate)
    {
        $this->authorizeCompany($expenseTemplate);
        $expenseTemplate->load(['items.material', 'items.jobType', 'items.expenseCategory']);

        $materials = Material::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $jobTypes = \App\Models\JobType::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = ExpenseCategory::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Prix unitaires actuellement résolus (catalogue ou prix de secours), pour affichage
        $prices = [];
        if ($expenseTemplate->items->isNotEmpty()) {
            $preview = $this->calculator->calculate($expenseTemplate->id, (float) $expenseTemplate->output_quantity);
            foreach ($preview['breakdown'] as $line) {
                $prices[$line['expense_template_item_id']] = $line;
            }
        }

        return view('expense-templates.show', [
            'expenseTemplate' => $expenseTemplate,
            'materials'       => $materials,
            'jobTypes'        => $jobTypes,
            'categories'      => $categories,
            'prices'          => $prices,
        ]);
    }

    public function edit(ExpenseTemplate $expenseTemplate)
    {
        $this->authorizeCompany($expenseTemplate);

        $materials = Material::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $unitTypes = UnitType::where('company_id', currentCompany()->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expense-templates.form', [
            'expenseTemplate' => $expenseTemplate,
            'materials'       => $materials,
            'unitTypes'       => $unitTypes,
        ]);
    }

    public function update(Request $request, ExpenseTemplate $expenseTemplate)
    {
        $this->authorizeCompany($expenseTemplate);

        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'description'     => 'nullable|string',
            'output_unit'     => 'required|string|max:30',
            'output_quantity' => 'required|numeric|min:0.001',
            'is_active'       => 'boolean',
        ]);

        $expenseTemplate->update($data);

        return redirect()->route('expense-templates.show', $expenseTemplate)->with('success', 'Modèle mis à jour.');
    }

    public function destroy(ExpenseTemplate $expenseTemplate)
    {
        $this->authorizeCompany($expenseTemplate);
        $expenseTemplate->delete();

        return redirect()->route('expense-templates.index')->with('success', 'Modèle supprimé.');
    }

    // ── Items management ──────────────────────────────────────────────────────

    public function storeItem(Request $request, ExpenseTemplate $expenseTemplate)
    {
        $this->authorizeCompany($expenseTemplate);

        $data = $request->validate([
            'material_id'         => 'nullable|exists:materials,id',
            'job_type_id'         => 'nullable|exists:job_types,id',
            'item_type'           => 'required|in:material,labor,equipment,subcontract,other',
            'description'         => 'nullable|string|max:150',
            'unit'                => 'required|string|max:30',
            'quantity_per_unit'   => 'required|numeric|min:0',
            'waste_rate'          => 'nullable|numeric|min:0|max:100',
            'unit_price'          => 'nullable|numeric|min:0',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
        ]);

        $lastOrder = $expenseTemplate->items()->max('sort_order') ?? 0;

        if (!empty($data['material_id'])) {
            $mat = Material::find($data['material_id']);
            $data['description'] = $data['description'] ?: ($mat?->name ?? '');
            $data['unit']        = $data['unit'] ?: ($mat?->unit ?? '');
        }

        if (empty($data['description'])) {
            return back()->withErrors(['description' => 'La description est obligatoire.'])->withInput();
        }

        $expenseTemplate->items()->create([
            ...$data,
            'waste_rate' => $data['waste_rate'] ?? 0,
            'sort_order' => $lastOrder + 1,
        ]);

        return back()->with('success', 'Ligne ajoutée.');
    }

    public function destroyItem(ExpenseTemplate $expenseTemplate, ExpenseTemplateItem $item)
    {
        $this->authorizeCompany($expenseTemplate);
        abort_unless($item->expense_template_id === $expenseTemplate->id, 404);
        $item->delete();

        return back()->with('success', 'Ligne supprimée.');
    }

    /**
     * Édition rapide du prix unitaire d'une ligne — sert notamment à corriger
     * un prix manquant (matériau sans tarif, métier sans grille salariale) sans
     * devoir retirer le lien vers le catalogue.
     */
    public function updateItemPrice(Request $request, ExpenseTemplate $expenseTemplate, ExpenseTemplateItem $item)
    {
        $this->authorizeCompany($expenseTemplate);
        abort_unless($item->expense_template_id === $expenseTemplate->id, 404);

        $data = $request->validate([
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $item->update(['unit_price' => $data['unit_price']]);

        return back()->with('success', 'Prix unitaire mis à jour.');
    }

    // ── AJAX — Aperçu du calcul ───────────────────────────────────────────────

    public function calculate(Request $request)
    {
        $data = $request->validate([
            'expense_template_id' => 'required|exists:expense_templates,id',
            'quantity'             => 'required|numeric|min:0',
            'region_id'            => 'nullable|exists:regions,id',
        ]);

        $template = ExpenseTemplate::where('company_id', currentCompany()->id)
            ->findOrFail($data['expense_template_id']);

        $result = $this->calculator->calculate(
            $template->id,
            (float) $data['quantity'],
            $data['region_id'] ?? null
        );

        return response()->json($result);
    }

    // ── Application à une tâche : génère de vraies dépenses ──────────────────

    public function applyToTask(Request $request, Task $task)
    {
        abort_unless($task->company_id === currentCompany()->id, 403);

        $data = $request->validate([
            'expense_template_id' => 'required|exists:expense_templates,id',
            'quantity'             => 'required|numeric|min:0.001',
        ]);

        $template = ExpenseTemplate::where('company_id', currentCompany()->id)
            ->findOrFail($data['expense_template_id']);

        $regionId = $task->project?->region_id;
        $result = $this->calculator->calculate($template->id, (float) $data['quantity'], $regionId);

        DB::transaction(function () use ($result, $task, $template) {
            foreach ($result['breakdown'] as $line) {
                Expense::create([
                    'company_id'           => $task->company_id,
                    'project_id'           => $task->project_id,
                    'task_id'              => $task->id,
                    'expense_category_id'  => $line['expense_category_id'],
                    'created_by'           => Auth::id(),
                    'description'          => $line['description'],
                    'expense_date'         => now()->toDateString(),
                    'quantity'             => $line['quantity'],
                    'unit'                 => $line['unit'],
                    'unit_price'           => $line['unit_price'],
                    'status'               => 'saisie',
                    'notes'                => "Généré depuis le modèle de dépense « {$template->name} » pour la tâche « {$task->title} ».",
                ]);
            }
        });

        \App\Models\ProjectLog::log(
            $task->project_id,
            'expense_template_applied',
            "Modèle de dépense « {$template->name} » appliqué à la tâche « {$task->title} » (" . count($result['breakdown']) . " ligne(s))."
        );

        return back()->with('success', count($result['breakdown']) . ' dépense(s) générée(s) depuis le modèle, à valider.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function authorizeCompany(ExpenseTemplate $template): void
    {
        abort_unless($template->company_id === currentCompany()->id, 403);
    }
}
