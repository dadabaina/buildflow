<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SiteReport;
use App\Models\SiteReportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteReportController extends Controller
{
    public function index(Request $request)
    {
        $company = currentCompany();
        $query   = $company->siteReports()->with(['project', 'author'])->latest('report_date');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $siteReports = $query->paginate(20)->withQueryString();
        $projects    = $company->projects()->orderBy('name')->get();

        return view('site-reports.index', compact('siteReports', 'projects'));
    }

    public function create()
    {
        $company  = currentCompany();
        $projects = $company->projects()->whereIn('status', ['en_cours', 'planifie'])->orderBy('name')->get();
        return view('site-reports.form', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:200',
            'report_date'      => 'required|date',
            'location'         => 'nullable|string|max:200',
            'weather'          => 'nullable|string|max:100',
            'content'          => 'nullable|string',
            'next_meeting_date'=> 'nullable|date',
            'participants'     => 'nullable|array',
        ]);

        $company   = currentCompany();
        $report    = $company->siteReports()->create(array_merge($validated, [
            'created_by' => Auth::id(),
        ]));

        return redirect()->route('site-reports.show', $report)
                         ->with('success', 'Compte-rendu créé avec succès.');
    }

    public function show(SiteReport $siteReport)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        $siteReport->load(['project', 'author', 'items']);
        return view('site-reports.show', compact('siteReport'));
    }

    public function edit(SiteReport $siteReport)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        abort_if($siteReport->status === 'finalise', 403, 'Un CR finalisé ne peut plus être modifié.');
        $projects = currentCompany()->projects()->orderBy('name')->get();
        return view('site-reports.form', compact('siteReport', 'projects'));
    }

    public function update(Request $request, SiteReport $siteReport)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        abort_if($siteReport->status === 'finalise', 403);

        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:200',
            'report_date'      => 'required|date',
            'location'         => 'nullable|string|max:200',
            'weather'          => 'nullable|string|max:100',
            'content'          => 'nullable|string',
            'next_meeting_date'=> 'nullable|date',
        ]);

        $siteReport->update($validated);

        return redirect()->route('site-reports.show', $siteReport)
                         ->with('success', 'Compte-rendu mis à jour.');
    }

    public function destroy(SiteReport $siteReport)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        $siteReport->delete();
        return redirect()->route('site-reports.index')->with('success', 'Compte-rendu supprimé.');
    }

    public function finalize(SiteReport $siteReport)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        $siteReport->update(['status' => 'finalise']);
        return back()->with('success', 'Compte-rendu finalisé.');
    }

    public function storeItem(Request $request, SiteReport $siteReport)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        $validated = $request->validate([
            'description' => 'required|string',
            'responsible' => 'nullable|string|max:150',
            'due_date'    => 'nullable|date',
        ]);
        $siteReport->items()->create($validated);
        return back()->with('success', 'Action ajoutée.');
    }

    public function updateItem(Request $request, SiteReport $siteReport, SiteReportItem $item)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        $item->update($request->validate([
            'status' => 'required|in:ouvert,en_cours,clos',
        ]));
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroyItem(SiteReport $siteReport, SiteReportItem $item)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        $item->delete();
        return back()->with('success', 'Action supprimée.');
    }

    public function exportPdf(SiteReport $siteReport)
    {
        abort_if($siteReport->company_id !== currentCompany()->id, 403);
        $siteReport->load(['project', 'author', 'items', 'company']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.site-report', compact('siteReport'));
        return $pdf->download('CR-' . $siteReport->reference . '.pdf');
    }
}
