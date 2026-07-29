<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Project;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project', 'employees', 'createdBy'])->latest();

        if ($s = $request->input('search')) {
            $query->where('title', 'like', "%{$s}%");
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }
        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }
        if (Auth::user()->hasRole('chef_chantier')) {
            $query->whereIn('project_id', Auth::user()->managedProjects()->pluck('projects.id'));
        }

        $tasks    = $query->paginate(25)->withQueryString();
        $projects = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'projects'));
    }

    public function kanban(Request $request)
    {
        $projectId = $request->input('project_id');
        $query     = Task::with(['employees', 'project'])->orderBy('sort_order')->orderBy('id');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if (Auth::user()->hasRole('chef_chantier')) {
            $query->whereIn('project_id', Auth::user()->managedProjects()->pluck('projects.id'));
        }

        $allTasks = $query->get();
        $columns  = [
            'a_faire'  => $allTasks->where('status', 'a_faire'),
            'en_cours' => $allTasks->where('status', 'en_cours'),
            'en_pause' => $allTasks->where('status', 'en_pause'),
            'termine'  => $allTasks->where('status', 'termine'),
        ];
        $projects = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();

        return view('tasks.kanban', compact('columns', 'projects', 'projectId'));
    }

    public function create(Request $request)
    {
        $projects = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();
        $employees = Employee::orderBy('last_name')->get();
        $selected  = $request->input('project_id');
        return view('tasks.form', compact('projects', 'employees', 'selected'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTask($request);
        $this->authorizeProjectScope($data['project_id']);
        $data['created_by'] = Auth::id();

        $task = Task::create($data);
        $employeeIds = $request->input('employee_ids', []);
        $task->employees()->sync($employeeIds);

        // Notify assigned users
        if (!empty($employeeIds)) {
            $this->notifyAssignedEmployees($task, $employeeIds);
        }

        \App\Models\ProjectLog::log(
            $task->project_id,
            'task_created',
            "Nouvelle tâche créée : '{$task->title}'."
        );

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tâche créée.');
    }

    public function show(Task $task)
    {
        $this->authorizeProjectScope($task->project_id);
        $task->load(['project', 'employees', 'comments.user', 'createdBy', 'expenses.category', 'quoteItem']);
        $expenseTemplates = \App\Models\ExpenseTemplate::where('is_active', true)->orderBy('name')->get();
        return view('tasks.show', compact('task', 'expenseTemplates'));
    }

    public function edit(Task $task)
    {
        $this->authorizeProjectScope($task->project_id);
        $task->load('employees');
        $projects = Auth::user()->hasRole('chef_chantier')
            ? Auth::user()->managedProjects()->orderBy('name')->get()
            : Project::orderBy('name')->get();
        $employees = Employee::orderBy('last_name')->get();
        return view('tasks.form', compact('task', 'projects', 'employees'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeProjectScope($task->project_id);
        $data = $this->validateTask($request);
        $this->authorizeProjectScope($data['project_id']);
        $task->update($data);
        
        $oldEmployees = $task->employees()->pluck('employees.id')->toArray();
        $newEmployees = $request->input('employee_ids', []);
        
        $task->employees()->sync($newEmployees);

        // Notify only NEWLY assigned employees
        $newlyAssigned = array_diff($newEmployees, $oldEmployees);
        if (!empty($newlyAssigned)) {
            $this->notifyAssignedEmployees($task, $newlyAssigned);
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Tâche mise à jour.');
    }

    private function notifyAssignedEmployees(Task $task, array $employeeIds): void
    {
        try {
            $emails = Employee::whereIn('id', $employeeIds)
                ->whereNotNull('email')
                ->pluck('email')
                ->toArray();

            if (empty($emails)) return;

            $users = User::whereIn('email', $emails)
                ->where('company_id', $task->company_id)
                ->where('id', '!=', Auth::id())
                ->get();

            if ($users->isNotEmpty()) {
                Notification::send($users, new TaskAssigned($task));
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }
    }

    public function destroy(Task $task)
    {
        $this->authorizeProjectScope($task->project_id);
        $projectId = $task->project_id;
        $title = $task->title;

        // Détacher les dépenses de la tâche (elles restent rattachées au chantier) :
        // Task utilise SoftDeletes, donc la contrainte nullOnDelete() de la FK ne se
        // déclenche jamais sur un simple $task->delete() — on le fait explicitement.
        $task->expenses()->update(['task_id' => null]);

        $task->delete();

        \App\Models\ProjectLog::log(
            $projectId,
            'task_deleted',
            "La tâche '{$title}' a été supprimée."
        );

        return redirect()->route('tasks.index')
            ->with('success', 'Tâche supprimée.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeProjectScope($task->project_id);
        $request->validate(['status' => 'required|in:a_faire,en_cours,en_pause,termine,annule']);
        $oldStatus = $task->status;
        $newStatus = $request->input('status');
        $task->update(['status' => $newStatus]);

        if ($oldStatus !== $newStatus) {
            \App\Models\ProjectLog::log(
                $task->project_id,
                'task_status_updated',
                "La tâche '{$task->title}' est passée de '{$oldStatus}' à '{$newStatus}'."
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'status'      => 'required|in:a_faire,en_cours,en_pause,termine,annule',
            'task_ids'    => 'required|array',
            'task_ids.*'  => 'integer|exists:tasks,id',
        ]);

        foreach ($data['task_ids'] as $index => $taskId) {
            Task::where('id', $taskId)->update([
                'status'     => $data['status'],
                'sort_order' => $index,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function storeComment(Request $request, Task $task)
    {
        $this->authorizeProjectScope($task->project_id);
        $request->validate(['body' => 'required|string|max:2000']);
        $task->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $request->input('body'),
        ]);
        return back()->with('success', 'Commentaire ajouté.');
    }

    public function updateChecklist(Request $request, Task $task)
    {
        $this->authorizeProjectScope($task->project_id);
        $request->validate(['checklist' => 'nullable|array']);
        $task->update(['checklist' => $request->input('checklist', [])]);
        return response()->json(['ok' => true]);
    }

    private function validateTask(Request $request): array
    {
        $data = $request->validate([
            'project_id'  => ['required', 'exists:projects,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'in:a_faire,en_cours,en_pause,termine,annule'],
            'priority'    => ['required', 'in:basse,normale,haute,urgente'],
            'weight'      => ['required', 'integer', 'min:1'],
            'due_date'    => ['nullable', 'date'],
            'checklist'   => ['nullable', 'array'],
            'checklist.*.label' => ['required', 'string', 'max:255'],
            'checklist.*.done'  => ['nullable'],
        ]);

        // Normalize checklist: ensure 'done' is boolean even when checkbox not submitted
        if (!empty($data['checklist'])) {
            $data['checklist'] = array_values(array_map(function ($item) {
                return [
                    'label' => $item['label'],
                    'done'  => !empty($item['done']),
                ];
            }, $data['checklist']));
        }

        return $data;
    }
}
