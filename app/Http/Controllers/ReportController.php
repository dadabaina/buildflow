<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;

use App\Services\FinancialAnalyticsService;

class ReportController extends Controller
{
    protected $analytics;

    public function __construct(FinancialAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index()
    {
        $company = currentCompany();
        
        $stats = [
            'projects_count' => $company->projects()->count(),
            'active_projects'=> $company->projects()->where('status', 'en_cours')->count(),
            'total_quoted'   => $company->quotes()->where('status', 'accepte')->sum('total_ttc'),
            'total_invoiced' => $company->invoices()->whereNotIn('status', ['brouillon', 'annulee'])->sum('total_ttc'),
            'total_paid'     => $company->payments()->sum('amount'),
            'total_expenses' => $company->expenses()->where('status', 'validee')->sum('total_amount'),
            'pending_expenses_count' => $company->expenses()->where('status', 'saisie')->count(),
            'overdue_invoices_count' => $company->invoices()
                ->where('due_date', '<', now())
                ->whereNotIn('status', ['soldee', 'annulee', 'brouillon'])
                ->count(),
            'employees_count' => $company->employees()->count(),
        ];

        $stats['balance'] = $stats['total_paid'] - $stats['total_expenses'];
        $stats['outstanding'] = $stats['total_invoiced'] - $stats['total_paid'];

        return view('reports.index', compact('stats'));
    }

    /**
     * Rapport Prévu (Dosage) vs Réel (Dépenses).
     */
    public function plannedVsReal(Request $request)
    {
        $company = currentCompany();
        $projectId = $request->get('project_id');
        $project = null;
        $analysis = null;

        if ($projectId) {
            $project = $company->projects()->findOrFail($projectId);
            $analysis = $this->analytics->getPlannedVsReal($project);
        }

        $projects = $company->projects()->where('status', '!=', 'annule')->orderBy('name')->get();

        return view('reports.planned-vs-real', compact('project', 'analysis', 'projects', 'projectId'));
    }

    public function financial(Request $request)
    {
        $company = currentCompany();
        $year    = $request->get('year', now()->year);

        // CA mensuel (factures soldées)
        $monthly = $company->invoices()
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_ttc) as total')
            ->where('status', 'soldee')
            ->whereYear('invoice_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        // Dépenses mensuelles validées
        $expenses = $company->expenses()
            ->selectRaw('MONTH(expense_date) as month, SUM(total_amount) as total')
            ->where('status', 'validee')
            ->whereYear('expense_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        // Top projets par CA
        $topProjects = $company->projects()
            ->withSum(['invoices as total_invoiced' => fn($q) => $q->whereIn('status', ['envoyee', 'soldee', 'en_retard'])], 'total_ttc')
            ->withSum(['invoices as total_paid' => fn($q) => $q->where('status', 'soldee')], 'total_ttc')
            ->orderByDesc('total_invoiced')
            ->take(10)
            ->get();

        $years = range(now()->year, now()->year - 4);

        if ($request->get('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report-financial', compact('monthly', 'expenses', 'topProjects', 'year'));
            return $pdf->download('rapport-financier-' . $year . '.pdf');
        }

        return view('reports.financial', compact('monthly', 'expenses', 'topProjects', 'year', 'years'));
    }

    public function projects(Request $request)
    {
        $company = currentCompany();
        $status  = $request->get('status');

        $projects = $company->projects()
            ->with(['client', 'region'])
            ->withSum(['expenses as expenses_total' => fn($q) => $q->where('status', 'validee')], 'total_amount')
            ->withSum(['invoices as invoiced_total' => fn($q) => $q->whereNotIn('status', ['brouillon', 'annulee'])], 'total_ttc')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('name')
            ->get();

        if ($request->get('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report-projects', compact('projects'));
            return $pdf->download('rapport-chantiers-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('reports.projects', compact('projects', 'status'));
    }

    public function expenses(Request $request)
    {
        $company   = currentCompany();
        $year      = $request->get('year', now()->year);
        $projectId = $request->get('project_id');

        $expenses = $company->expenses()
            ->with(['category', 'project'])
            ->where('status', 'validee')
            ->whereYear('expense_date', $year)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->orderBy('expense_date')
            ->get();

        $byCategory = $expenses->groupBy(fn($e) => $e->category->name ?? 'Autres')
                               ->map(fn($g) => $g->sum('total_amount'));

        $projects = $company->projects()->orderBy('name')->get();
        $years    = range(now()->year, now()->year - 4);

        if ($request->get('export') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report-expenses', compact('expenses', 'byCategory', 'year'));
            return $pdf->download('journal-depenses-' . $year . '.pdf');
        }

        return view('reports.expenses', compact('expenses', 'byCategory', 'year', 'years', 'projects', 'projectId'));
    }

    public function attendance(Request $request)
    {
        $company   = currentCompany();
        $month     = $request->get('month', now()->month);
        $year      = $request->get('year', now()->year);
        $projectId = $request->get('project_id');

        $query = $company->attendances()
            ->with(['employee.jobType', 'project'])
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->orderBy('work_date');

        $projects = $company->projects()->orderBy('name')->get();
        $months   = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => \Carbon\Carbon::create()->month($m)->locale('fr')->monthName]);

        if ($request->get('export') === 'pdf') {
            $attendances = $query->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report-attendance', compact('attendances', 'month', 'year'));
            return $pdf->download('pointage-' . $year . '-' . $month . '.pdf');
        }

        $attendances = $query->paginate(50)->withQueryString();

        return view('reports.attendance', compact('attendances', 'projects', 'month', 'year', 'months', 'projectId'));
    }
}
