<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

use App\Services\FinancialAnalyticsService;

class DashboardController extends Controller
{
    protected $analytics;

    public function __construct(FinancialAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index()
    {
        $company = auth()->user()->company;

        // Projets par statut
        $projectStats = $company->projects()->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // KPIs financiers du mois en cours
        $month = now()->month;
        $year  = now()->year;

        $monthlyExpenses = $company->expenses()->where('status', 'validee')
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('total_amount');

        $monthlyPayments = $company->payments()
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->sum('amount');

        // Nouveaux KPIs Proactifs via Service
        $cashFlow = $this->analytics->getCashFlowForecast($company);
        
        // Santé des chantiers actifs
        $activeProjectsHealth = $company->projects()
            ->where('status', 'en_cours')
            ->get()
            ->map(function($project) {
                return array_merge(
                    ['id' => $project->id, 'name' => $project->name, 'reference' => $project->reference],
                    $this->analytics->getProjectHealth($project)
                );
            });

        // Factures en retard
        $overdueInvoices = $company->invoices()
            ->whereNotIn('status', ['soldee', 'annulee', 'brouillon'])
            ->where('due_date', '<', now());
        
        $overdueInvoicesCount = $overdueInvoices->count();
        $totalOverdueAmount = $overdueInvoices->sum('amount_remaining');

        // Devis en attente
        $pendingQuotesCount = $company->quotes()->where('status', 'envoye')->count();

        // Alertes Stocks (utilisant la méthode existante du modèle Project)
        $allStockAlerts = collect();
        foreach ($company->projects()->where('status', 'en_cours')->get() as $project) {
            $allStockAlerts = $allStockAlerts->merge($project->getLowStockMaterials());
        }

        // 5 projets récents
        $recentProjects = $company->projects()->with('client')
            ->latest()
            ->take(5)
            ->get();

        // 5 dépenses en attente de validation
        $pendingExpenses = $company->expenses()->with(['project', 'category'])
            ->where('status', 'saisie')
            ->latest()
            ->take(5)
            ->get();

        // Totaux globaux
        $totalClients  = $company->clients()->count();
        $totalProjects = $company->projects()->count();
        $activeProjectsCount = $activeProjectsHealth->count();

        return view('dashboard', compact(
            'projectStats',
            'monthlyExpenses',
            'monthlyPayments',
            'overdueInvoicesCount',
            'totalOverdueAmount',
            'pendingQuotesCount',
            'recentProjects',
            'pendingExpenses',
            'totalClients',
            'totalProjects',
            'activeProjectsCount',
            'cashFlow',
            'activeProjectsHealth',
            'allStockAlerts'
        ));
    }
}
