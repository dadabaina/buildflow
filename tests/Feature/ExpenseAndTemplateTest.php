<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseTemplate;
use App\Models\Project;
use App\Models\Task;

/**
 * BIB-01 à BIB-06 et EXP-01 à EXP-07 du cahier de recette.
 */
class ExpenseAndTemplateTest extends RecetteTestCase
{
    /** BIB-03, BIB-04, BIB-05 : modèle de dépense sans marge, application à une tâche, non-impact sur le modèle */
    public function test_bib_03_04_05_modele_de_depense_applique_a_une_tache(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier BIB']);
        $task = Task::create(['company_id' => $company->id, 'project_id' => $project->id, 'created_by' => auth()->id(), 'title' => 'Fondation', 'status' => 'a_faire', 'priority' => 'normale', 'weight' => 1]);
        $category = $this->makeExpenseCategory($company);

        $template = ExpenseTemplate::create([
            'company_id' => $company->id, 'name' => 'Béton 350 (coût réel)',
            'output_unit' => 'm3', 'output_quantity' => 1,
        ]);
        $template->items()->create([
            'item_type' => 'material', 'description' => 'Ciment', 'unit' => 'sac',
            'quantity_per_unit' => 6, 'waste_rate' => 0, 'unit_price' => 25000,
            'expense_category_id' => $category->id, 'sort_order' => 1,
        ]);
        $template->items()->create([
            'item_type' => 'labor', 'description' => 'Main-d\'oeuvre', 'unit' => 'h',
            'quantity_per_unit' => 4, 'waste_rate' => 0, 'unit_price' => 5000,
            'expense_category_id' => $category->id, 'sort_order' => 2,
        ]);

        // BIB-04 : application à la tâche pour 2 m3 réels
        $this->post(route('tasks.apply-expense-template', $task), [
            'expense_template_id' => $template->id, 'quantity' => 2,
        ])->assertSessionHas('success');

        $expenses = Expense::where('task_id', $task->id)->get();
        $this->assertCount(2, $expenses, 'BIB-04 : une dépense par ligne du modèle');
        $this->assertTrue($expenses->every(fn ($e) => $e->status === 'saisie'));
        $ciment = $expenses->firstWhere('description', 'Ciment');
        $this->assertEquals(12, (float) $ciment->quantity); // 6 * facteur(2/1)
        $this->assertEquals(25000, (float) $ciment->unit_price);

        // BIB-05 : supprimer la dépense générée ne touche pas le modèle source
        $expenses->first()->delete();
        $template->refresh();
        $this->assertCount(2, $template->items, 'BIB-05 : le modèle source reste intact après suppression d\'une dépense générée');
    }

    /** EXP-01, EXP-02 : saisie + validation */
    public function test_exp_01_02_saisie_et_validation(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier EXP']);

        $resp = $this->post(route('expenses.store'), [
            'project_id' => $project->id, 'description' => 'Sable', 'expense_date' => now()->toDateString(),
            'quantity' => 5, 'unit_price' => 20000,
        ]);
        $resp->assertRedirect(route('expenses.index'));

        $expense = Expense::first();
        $this->assertSame('saisie', $expense->status);
        $this->assertEquals(100000, (float) $expense->total_amount);

        $this->patch(route('expenses.validate', $expense))->assertSessionHas('success');
        $expense->refresh();
        $this->assertSame('validee', $expense->status);
        $this->assertNotNull($expense->validated_at);
    }

    /** EXP-03, EXP-04 : rejet avec motif obligatoire */
    public function test_exp_03_04_rejet_avec_motif_obligatoire(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier EXP2']);
        $expense = Expense::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => auth()->id(), 'description' => 'Ciment',
            'expense_date' => now()->toDateString(), 'quantity' => 1, 'unit_price' => 10000, 'status' => 'saisie',
        ]);

        // EXP-04 : rejet sans motif -> erreur de validation
        $this->patch(route('expenses.reject', $expense), [])->assertSessionHasErrors('rejection_reason');
        $this->assertSame('saisie', $expense->fresh()->status);

        // EXP-03 : rejet avec motif -> OK
        $this->patch(route('expenses.reject', $expense), ['rejection_reason' => 'Facture manquante'])
            ->assertSessionHas('success');
        $expense->refresh();
        $this->assertSame('rejetee', $expense->status);
        $this->assertSame('Facture manquante', $expense->rejection_reason);
    }

    /** EXP-07 : suppression */
    public function test_exp_07_suppression(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier EXP3']);
        $expense = Expense::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => auth()->id(), 'description' => 'Location bétonnière',
            'expense_date' => now()->toDateString(), 'quantity' => 1, 'unit_price' => 15000, 'status' => 'saisie',
        ]);

        $this->delete(route('expenses.destroy', $expense))->assertRedirect(route('expenses.index'));
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }
}
