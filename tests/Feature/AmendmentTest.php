<?php

namespace Tests\Feature;

use App\Models\Amendment;
use App\Models\Project;

/**
 * AVN-01 à AVN-03 du cahier de recette : Avenants.
 */
class AmendmentTest extends RecetteTestCase
{
    /** AVN-01, AVN-02 : création, envoi, acceptation */
    public function test_avn_01_02_cycle_de_vie(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier AVN']);

        $resp = $this->post(route('amendments.store'), [
            'project_id' => $project->id, 'title' => 'Travaux supplémentaires', 'tva_rate' => 20,
            'items' => [['description' => 'Portail supplémentaire', 'quantity' => 1, 'unit_price' => 800000]],
        ]);
        $resp->assertRedirect(route('amendments.index'));

        $amendment = Amendment::first();
        $this->assertSame('brouillon', $amendment->status);
        $this->assertMatchesRegularExpression('/^AVN-\d{4}-\d{3}$/', $amendment->reference);

        $this->post(route('amendments.send', $amendment))->assertSessionHas('success');
        $this->assertSame('envoye', $amendment->fresh()->status);

        $this->post(route('amendments.accept', $amendment))->assertSessionHas('success');
        $amendment->refresh();
        $this->assertSame('accepte', $amendment->status);
        $this->assertTrue($project->projectLogs()->where('action', 'amendment_accepted')->exists());
    }

    /** AVN-03 : l'acceptation d'un avenant ajoute son montant au marché du chantier, comme un devis (DEV-17) */
    public function test_avn_03_impact_sur_montant_marche(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create([
            'company_id' => $company->id, 'client_id' => $this->makeClient($company)->id,
            'name' => 'Chantier AVN2', 'contract_amount' => 5_000_000,
        ]);

        $amendment = Amendment::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => auth()->id(),
            'reference' => 'AVN-TEST-' . uniqid(), 'title' => 'Avenant test', 'tva_rate' => 20, 'status' => 'envoye',
        ]);
        $amendment->items()->create(['description' => 'Supplément', 'quantity' => 1, 'unit_price' => 1_000_000, 'is_deduction' => false, 'sort_order' => 1]);
        $amendment->recalcTotals();
        $amendmentTtc = (float) $amendment->fresh()->total_ttc;

        $this->post(route('amendments.accept', $amendment));

        $this->assertEquals(5_000_000 + $amendmentTtc, (float) $project->fresh()->contract_amount);
        $this->assertTrue(
            $project->projectLogs()->where('description', 'like', '%Montant du marché porté%')->exists()
        );
    }

    /** AVN-03bis : un avenant en déduction diminue le montant du marché */
    public function test_avn_03bis_deduction_diminue_le_marche(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create([
            'company_id' => $company->id, 'client_id' => $this->makeClient($company)->id,
            'name' => 'Chantier AVN3', 'contract_amount' => 5_000_000,
        ]);

        $amendment = Amendment::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => auth()->id(),
            'reference' => 'AVN-TEST-' . uniqid(), 'title' => 'Avenant déduction', 'tva_rate' => 0, 'status' => 'envoye',
        ]);
        $amendment->items()->create(['description' => 'Moins-value', 'quantity' => 1, 'unit_price' => 300_000, 'is_deduction' => true, 'sort_order' => 1]);
        $amendment->recalcTotals();

        $this->post(route('amendments.accept', $amendment));

        $this->assertEquals(4_700_000, (float) $project->fresh()->contract_amount);
    }
}
