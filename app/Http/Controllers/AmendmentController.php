<?php

namespace App\Http\Controllers;

use App\Models\Amendment;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AmendmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Amendment::with(['project', 'createdBy'])->latest();

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $amendments = $query->paginate(25)->withQueryString();
        $projects   = Project::orderBy('name')->get();

        return view('amendments.index', compact('amendments', 'projects'));
    }

    public function create(Request $request)
    {
        $projects  = Project::orderBy('name')->get();
        $quotes    = collect();
        $selected  = $request->input('project_id');
        $unitTypes = \App\Models\UnitType::where('company_id', currentCompany()->id)->where('is_active', true)->orderBy('name')->get();

        if ($selected) {
            $quotes = Quote::where('project_id', $selected)->whereIn('status', ['accepte'])->get();
        }
        return view('amendments.form', compact('projects', 'quotes', 'selected', 'unitTypes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAmendment($request);
        $data['created_by'] = Auth::id();

        // Generate reference AVN-YYYY-NNN
        $company = Auth::user()->company;
        $lastNum = $company->amendments()->max(DB::raw("CAST(SUBSTRING(reference, 10) AS UNSIGNED)")) ?? 0;
        $data['reference'] = 'AVN-' . now()->year . '-' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($data, $request) {
            $amendment = Amendment::create($data);
            $this->syncItems($amendment, $request->input('items', []));
        });

        return redirect()->route('amendments.index')
            ->with('success', 'Avenant créé.');
    }

    public function show(Amendment $amendment)
    {
        $amendment->load(['project', 'quote', 'createdBy', 'items']);
        return view('amendments.show', compact('amendment'));
    }

    public function edit(Amendment $amendment)
    {
        if (!in_array($amendment->status, ['brouillon'])) {
            return back()->with('error', 'Seul un avenant en brouillon peut être modifié.');
        }
        $amendment->load('items');
        $projects  = Project::orderBy('name')->get();
        $quotes    = Quote::where('project_id', $amendment->project_id)->whereIn('status', ['accepte'])->get();
        $unitTypes = \App\Models\UnitType::where('company_id', currentCompany()->id)->where('is_active', true)->orderBy('name')->get();

        return view('amendments.form', compact('amendment', 'projects', 'quotes', 'unitTypes'));
    }

    public function update(Request $request, Amendment $amendment)
    {
        if (!in_array($amendment->status, ['brouillon'])) {
            return back()->with('error', 'Seul un avenant en brouillon peut être modifié.');
        }
        $data = $this->validateAmendment($request);

        DB::transaction(function () use ($data, $request, $amendment) {
            $amendment->update($data);
            $amendment->items()->delete();
            $this->syncItems($amendment, $request->input('items', []));
        });

        return redirect()->route('amendments.show', $amendment)
            ->with('success', 'Avenant mis à jour.');
    }

    public function destroy(Amendment $amendment)
    {
        $amendment->delete();
        return redirect()->route('amendments.index')
            ->with('success', 'Avenant supprimé.');
    }

    public function send(Amendment $amendment)
    {
        if ($amendment->status !== 'brouillon') {
            return back()->with('error', 'Seul un avenant en brouillon peut être envoyé.');
        }
        $amendment->update(['status' => 'envoye']);
        return back()->with('success', 'Avenant marqué comme envoyé.');
    }

    public function accept(Amendment $amendment)
    {
        if ($amendment->status !== 'envoye') {
            return back()->with('error', 'Seul un avenant envoyé peut être accepté.');
        }
        $amendment->update(['status' => 'accepte']);

        \App\Models\ProjectLog::log(
            $amendment->project_id,
            'amendment_accepted',
            "L'avenant {$amendment->reference} a été accepté."
        );

        return back()->with('success', 'Avenant accepté.');
    }

    public function refuse(Amendment $amendment)
    {
        if ($amendment->status !== 'envoye') {
            return back()->with('error', 'Seul un avenant envoyé peut être refusé.');
        }
        $amendment->update(['status' => 'refuse']);

        \App\Models\ProjectLog::log(
            $amendment->project_id,
            'amendment_refused',
            "L'avenant {$amendment->reference} a été refusé."
        );

        return back()->with('success', 'Avenant refusé.');
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    private function validateAmendment(Request $request): array
    {
        return $request->validate([
            'project_id'  => ['required', 'exists:projects,id'],
            'quote_id'    => ['nullable', 'exists:quotes,id'],
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'tva_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'valid_until' => ['nullable', 'date'],
            'notes'       => ['nullable', 'string'],
        ]);
    }

    private function syncItems(Amendment $amendment, array $items): void
    {
        foreach ($items as $i => $item) {
            if (empty($item['description'])) continue;
            $amendment->items()->create([
                'description'  => $item['description'],
                'quantity'     => (float) ($item['quantity'] ?? 1),
                'unit'         => $item['unit'] ?? null,
                'unit_price'   => (float) ($item['unit_price'] ?? 0),
                'is_deduction' => !empty($item['is_deduction']),
                'sort_order'   => $i,
            ]);
        }
        $amendment->recalcTotals();
    }
}
