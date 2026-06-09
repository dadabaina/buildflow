<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ReceptionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceptionReportController extends Controller
{
    public function index(Request $request)
    {
        $company = currentCompany();
        $query   = $company->receptionReports()->with(['project', 'author'])->latest('reception_date');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $receptionReports = $query->paginate(20)->withQueryString();
        $projects         = $company->projects()->orderBy('name')->get();

        return view('reception-reports.index', compact('receptionReports', 'projects'));
    }

    public function create()
    {
        $company  = currentCompany();
        $projects = $company->projects()->orderBy('name')->get();
        return view('reception-reports.form', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'reception_date' => 'required|date',
            'client_name'    => 'nullable|string|max:200',
            'reserves'       => 'nullable|string',
            'rg_amount'      => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $company = currentCompany();
        $report  = $company->receptionReports()->create(array_merge($validated, [
            'created_by' => Auth::id(),
            'rg_amount'  => $validated['rg_amount'] ?? 0,
        ]));

        return redirect()->route('reception-reports.show', $report)
                         ->with('success', 'PV de réception créé.');
    }

    public function show(ReceptionReport $receptionReport)
    {
        abort_if($receptionReport->company_id !== currentCompany()->id, 403);
        $receptionReport->load(['project', 'author', 'company']);
        return view('reception-reports.show', compact('receptionReport'));
    }

    public function edit(ReceptionReport $receptionReport)
    {
        abort_if($receptionReport->company_id !== currentCompany()->id, 403);
        $projects = currentCompany()->projects()->orderBy('name')->get();
        return view('reception-reports.form', compact('receptionReport', 'projects'));
    }

    public function update(Request $request, ReceptionReport $receptionReport)
    {
        abort_if($receptionReport->company_id !== currentCompany()->id, 403);
        $validated = $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'reception_date' => 'required|date',
            'client_name'    => 'nullable|string|max:200',
            'reserves'       => 'nullable|string',
            'rg_amount'      => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);
        $receptionReport->update($validated);
        return redirect()->route('reception-reports.show', $receptionReport)
                         ->with('success', 'PV mis à jour.');
    }

    public function destroy(ReceptionReport $receptionReport)
    {
        abort_if($receptionReport->company_id !== currentCompany()->id, 403);
        $receptionReport->delete();
        return redirect()->route('reception-reports.index')->with('success', 'PV supprimé.');
    }

    public function accept(ReceptionReport $receptionReport)
    {
        abort_if($receptionReport->company_id !== currentCompany()->id, 403);
        $receptionReport->update(['status' => 'signe']);
        
        // SPEC-14-01: Auto-close project after PV signed
        if ($receptionReport->project && $receptionReport->project->status !== 'cloture') {
            $receptionReport->project->update([
                'status' => 'cloture',
                'actual_end_date' => $receptionReport->reception_date ?? now(),
            ]);
        }

        return back()->with('success', 'PV signé/accepté. Le chantier a été clôturé.');
    }

    public function releaseRg(Request $request, ReceptionReport $receptionReport)
    {
        abort_if($receptionReport->company_id !== currentCompany()->id, 403);
        $receptionReport->update([
            'status'          => 'rg_libere',
            'rg_release_date' => $request->validate(['rg_release_date' => 'required|date'])['rg_release_date'],
        ]);
        return back()->with('success', 'Retenue de garantie libérée.');
    }

    public function exportPdf(ReceptionReport $receptionReport)
    {
        abort_if($receptionReport->company_id !== currentCompany()->id, 403);
        $receptionReport->load(['project', 'author', 'company']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.reception-report', compact('receptionReport'));
        return $pdf->download('PV-' . $receptionReport->reference . '.pdf');
    }
}
