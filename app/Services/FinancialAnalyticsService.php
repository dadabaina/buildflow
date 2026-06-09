<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialAnalyticsService
{
    /**
     * Analyse de santé globale d'un projet (Niveau 1 & 2).
     */
    public function getProjectHealth(Project $project): array
    {
        $totalExpenses = (float) $project->total_expenses;
        $contractAmount = (float) $project->total_market_amount; // Devis + Avenants
        $progressPercent = (int) $project->progress_percent;

        // Consommation budgétaire
        $budgetConsumption = $contractAmount > 0 ? ($totalExpenses / $contractAmount) * 100 : 0;
        
        // Indicateur de dérive (CPI - Cost Performance Index simplifié)
        // Si l'avancement physique est inférieur à la consommation budgétaire, on dérive.
        $driftAlert = ($progressPercent > 0 && $budgetConsumption > $progressPercent);

        return [
            'total_expenses' => $totalExpenses,
            'contract_amount' => $contractAmount,
            'progress_percent' => $progressPercent,
            'budget_consumption_percent' => round($budgetConsumption, 2),
            'margin_nominal' => $contractAmount - $totalExpenses,
            'margin_percent' => $contractAmount > 0 ? (($contractAmount - $totalExpenses) / $contractAmount) * 100 : 0,
            'drift_alert' => $driftAlert,
        ];
    }

    /**
     * Ventilation des dépenses par catégorie pour un projet (Niveau 2).
     */
    public function getExpensesByCategory(Project $project): \Illuminate\Support\Collection
    {
        return $project->expenses()
            ->where('status', 'validee')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(total_amount) as total'))
            ->groupBy('expense_categories.name')
            ->pluck('total', 'name');
    }

    /**
     * Prévision de trésorerie à 30 jours pour une entreprise.
     */
    public function getCashFlowForecast(Company $company): array
    {
        $now = Carbon::now();
        $thirtyDaysLater = $now->copy()->addDays(30);

        // Entrées prévues (Factures à échéance)
        $expectedIncomes = $company->invoices()
            ->whereIn('status', ['envoye', 'partiellement_payee'])
            ->whereBetween('due_date', [$now, $thirtyDaysLater])
            ->sum('amount_remaining');

        // Sorties prévues (Dépenses validées non encore payées - Simulé ici par statut)
        // Note: Idéalement nécessite un champ 'is_paid' sur Expense.
        $expectedOutcomes = $company->expenses()
            ->where('status', 'validee')
            ->whereBetween('expense_date', [$now->copy()->subDays(30), $now]) // Dépenses récentes potentiellement non réglées
            ->sum('total_amount');

        return [
            'expected_incomes' => (float) $expectedIncomes,
            'expected_outcomes' => (float) $expectedOutcomes,
            'net_forecast' => (float) ($expectedIncomes - $expectedOutcomes),
        ];
    }

    /**
     * Analyse Prévu (Dosage) vs Réel (Dépenses) - Niveau 3.
     * Cette méthode compare les DBE des QuoteItems avec les dépenses réelles.
     */
    public function getPlannedVsReal(Project $project): array
    {
        // 1. Récupérer le prévu (DBE des devis acceptés)
        $planned = DB::table('quote_items')
            ->join('quotes', 'quote_items.quote_id', '=', 'quotes.id')
            ->where('quotes.project_id', $project->id)
            ->where('quotes.status', 'accepte')
            ->selectRaw('
                SUM(dbe_materials * quantity) as materials,
                SUM(dbe_labor * quantity) as labor,
                SUM(dbe_equipment * quantity) as equipment,
                SUM(dbe_subcontract * quantity) as subcontract
            ')
            ->first();

        // 2. Récupérer le réel (Dépenses par catégorie mappée)
        // Note: Nécessite un mapping entre Catégories de dépenses et types de DBE.
        $realExpenses = $project->expenses()
            ->where('status', 'validee')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(total_amount) as total'))
            ->groupBy('expense_categories.name')
            ->get();

        return [
            'planned' => [
                'Matériaux' => (float) ($planned->materials ?? 0),
                'Main d\'œuvre' => (float) ($planned->labor ?? 0),
                'Matériel' => (float) ($planned->equipment ?? 0),
                'Sous-traitance' => (float) ($planned->subcontract ?? 0),
            ],
            'real' => $realExpenses->pluck('total', 'name')->toArray(),
        ];
    }
}
