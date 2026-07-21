<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Supplier;

/**
 * BC-01 à BC-05 du cahier de recette : Bons de commande.
 */
class PurchaseOrderTest extends RecetteTestCase
{
    public function test_bc_01_a_05_cycle_complet(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier BC']);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'Quincaillerie Centrale']);

        // BC-01 : création
        $resp = $this->post(route('purchase-orders.store'), [
            'project_id' => $project->id, 'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(), 'tva_rate' => 20,
            'items' => [['description' => 'Ciment CPA 42.5', 'quantity' => 50, 'unit' => 'sac', 'unit_price' => 25000]],
        ]);
        $resp->assertRedirect(route('purchase-orders.index'));
        $po = PurchaseOrder::first();
        $this->assertSame('brouillon', $po->status);
        $this->assertTrue($project->projectLogs()->where('action', 'bc_created')->exists());

        // BC-02 : validation
        $this->patch(route('purchase-orders.status', $po), ['status' => 'valide'])->assertSessionHas('success');
        $this->assertSame('valide', $po->fresh()->status);
        $this->assertTrue($project->projectLogs()->where('action', 'bc_status_updated')->exists());

        // BC-03 : livraison
        $this->patch(route('purchase-orders.status', $po), ['status' => 'livre'])->assertSessionHas('success');
        $this->assertSame('livre', $po->fresh()->status);

        // BC-04 : conversion en dépense
        $this->post(route('purchase-orders.convert-expense', $po))->assertRedirect(route('purchase-orders.show', $po));
        $expenses = Expense::where('project_id', $project->id)->get();
        $this->assertCount(1, $expenses);
        $this->assertSame('saisie', $expenses->first()->status);

        // BC-05 : traçabilité complète dans le fil d'actualité
        $actions = $project->projectLogs()->pluck('action')->all();
        foreach (['bc_created', 'bc_status_updated', 'bc_converted'] as $expected) {
            $this->assertContains($expected, $actions, "BC-05 : log '$expected' manquant");
        }
    }

    /** Transition de statut invalide refusée (ex: brouillon -> livré directement) */
    public function test_bc_transition_invalide_refusee(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier BC2']);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'Fournisseur X']);
        $po = PurchaseOrder::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'supplier_id' => $supplier->id,
            'created_by' => auth()->id(), 'order_date' => now()->toDateString(), 'tva_rate' => 20, 'status' => 'brouillon',
        ]);

        $this->patch(route('purchase-orders.status', $po), ['status' => 'livre'])->assertSessionHas('error');
        $this->assertSame('brouillon', $po->fresh()->status);
    }
}
