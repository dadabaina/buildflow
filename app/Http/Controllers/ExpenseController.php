<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['project', 'task', 'category', 'supplier', 'createdBy'])->latest();

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }
        if (Auth::user()->hasRole('chef_chantier')) {
            $query->whereIn('project_id', Auth::user()->managedProjects()->pluck('projects.id'));
        }

        $expenses   = $query->paginate(25)->withQueryString();
        $projects   = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'projects'));
    }

    public function create()
    {
        $projects = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();
        $categories  = ExpenseCategory::orderBy('name')->get();
        $suppliers   = Supplier::orderBy('name')->get();
        $tasks       = Task::orderBy('title')->get();
        return view('expenses.form', compact('projects', 'categories', 'suppliers', 'tasks'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateExpense($request);
        $this->authorizeProjectScope($validated['project_id']);
        $validated['created_by'] = Auth::id();
        Auth::user()->company->expenses()->create($validated);
        return redirect()->route('expenses.index')
            ->with('success', 'Dépense enregistrée.');
    }

    public function show(Expense $expense)
    {
        abort_if($expense->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($expense->project_id);
        $expense->load(['project', 'category', 'supplier', 'createdBy', 'validatedBy']);
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        abort_if($expense->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($expense->project_id);
        if ($expense->status !== 'saisie') {
            return back()->with('error', 'Seules les dépenses en attente peuvent être modifiées.');
        }
        $projects = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();
        $categories = ExpenseCategory::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();
        $tasks      = Task::orderBy('title')->get();
        return view('expenses.form', compact('expense', 'projects', 'categories', 'suppliers', 'tasks'));
    }

    public function update(Request $request, Expense $expense)
    {
        abort_if($expense->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($expense->project_id);
        if ($expense->status !== 'saisie') {
            return back()->with('error', 'Seules les dépenses en attente peuvent être modifiées.');
        }
        $validated = $this->validateExpense($request);
        $this->authorizeProjectScope($validated['project_id']);
        $expense->update($validated);
        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Dépense mise à jour.');
    }

    public function destroy(Expense $expense)
    {
        abort_if($expense->company_id !== Auth::user()->company_id, 403);
        $this->authorizeProjectScope($expense->project_id);
        $expense->delete();
        return redirect()->route('expenses.index')
            ->with('success', 'Dépense supprimée.');
    }

    public function validate(Expense $expense)
    {
        $this->authorize('expenses.validate');

        $expense->update([
            'status'       => 'validee',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);
        return back()->with('success', 'Dépense validée.');
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->authorize('expenses.reject');

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $expense->update([
            'status'           => 'rejetee',
            'rejection_reason' => $request->rejection_reason,
        ]);
        return back()->with('success', 'Dépense rejetée.');
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'project_id'          => ['required', 'exists:projects,id'],
            'task_id'             => ['nullable', 'exists:tasks,id'],
            'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
            'supplier_id'         => ['nullable', 'exists:suppliers,id'],
            'description'         => ['required', 'string', 'max:500'],
            'expense_date'        => ['required', 'date'],
            'quantity'            => ['nullable', 'numeric', 'min:0'],
            'unit'                => ['nullable', 'string', 'max:30'],
            'unit_price'          => ['required', 'numeric', 'min:0'],
            'payment_mode'        => ['nullable', 'string', 'max:50'],
            'payment_reference'   => ['nullable', 'string', 'max:100'],
            'notes'               => ['nullable', 'string'],
        ]);
    }
}
