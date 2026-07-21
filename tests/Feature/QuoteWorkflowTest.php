<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Task;

/**
 * DEV-01 à DEV-25 du cahier de recette : cycle Devis → Chantier → Tâches → Facture.
 */
class QuoteWorkflowTest extends RecetteTestCase
{
    /** DEV-01, DEV-02 : création + ajout de lignes, totaux corrects */
    public function test_dev_01_02_creation_devis_et_lignes(): void
    {
        $company = $this->makeCompany();
        $user = $this->actingAsCompanyUser($company);
        $client = $this->makeClient($company);

        $resp = $this->post(route('quotes.store'), [
            'client_id' => $client->id,
            'title' => 'Construction mur de clôture',
            'quote_date' => now()->toDateString(),
            'tva_rate' => 20,
        ]);

        $quote = Quote::first();
        $resp->assertRedirect(route('quotes.show', $quote));
        $this->assertSame('brouillon', $quote->status);
        $this->assertMatchesRegularExpression('/^DEV-\d{4}$/', $quote->reference);

        $this->post(route('quotes.items.add', $quote), [
            'description' => 'Terrassement', 'quantity' => 10, 'unit' => 'm3', 'unit_price' => 20000, 'discount' => 10,
        ])->assertRedirect();

        $quote->refresh();
        $this->assertEquals(180000, $quote->subtotal_ht); // 10*20000*0.9
    }

    /** DEV-05 : remise globale en % */
    public function test_dev_05_remise_globale_pourcentage(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000);
        $this->addItem($quote, 50000);

        $quote->update(['discount_global' => 10, 'discount_type' => 'percent']);
        $quote->recalculateTotals();
        $quote->refresh();

        $this->assertEquals(150000, $quote->subtotal_ht);
        $this->assertEquals(15000, $quote->discount_amount);
        $this->assertEquals(135000, $quote->taxable_ht);
        $this->assertEquals(162000, $quote->total_ttc); // 135000 * 1.2
    }

    /** DEV-08, DEV-11, DEV-12, DEV-13 : acceptation crée le chantier et les tâches, montants corrects */
    public function test_dev_08_11_12_13_acceptation_cree_chantier_et_taches(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000, dbeTotal: 60000);
        $this->addItem($quote, 50000, dbeTotal: 30000);
        $quote->refresh();
        $expectedTtc = $quote->total_ttc;

        $this->post(route('quotes.accept', $quote))->assertRedirect(route('quotes.show', $quote));

        $quote->refresh();
        $this->assertSame('accepte', $quote->status);
        $this->assertNotNull($quote->project_id);

        $project = Project::find($quote->project_id);
        $this->assertEquals($expectedTtc, $project->contract_amount, 'DEV-11 : montant initial = total TTC du devis');
        $this->assertEquals(90000, $project->budget_total, 'DEV-12 : budget initial = somme des DBE');

        $tasks = Task::where('project_id', $project->id)->get();
        $this->assertCount(2, $tasks, 'DEV-13 : une tâche par ligne de devis');
    }

    /** DEV-14 : deux lignes au même libellé génèrent deux tâches distinctes */
    public function test_dev_14_lignes_identiques_ne_fusionnent_pas(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000, description: 'Béton dosé à 350');
        $this->addItem($quote, 50000, description: 'Béton dosé à 350');

        $this->post(route('quotes.accept', $quote));
        $quote->refresh();

        $this->assertCount(2, Task::where('project_id', $quote->project_id)->get());
    }

    /** DEV-15 : régénérer les tâches sur un devis déjà accepté ne crée pas de doublons */
    public function test_dev_15_regeneration_sans_doublon(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000);
        $this->post(route('quotes.accept', $quote));
        $quote->refresh();
        $this->assertCount(1, Task::where('project_id', $quote->project_id)->get());

        $this->post(route('quotes.tasks', $quote));

        $this->assertCount(1, Task::where('project_id', $quote->project_id)->get(), 'DEV-15 : pas de doublon à la régénération');
    }

    /** DEV-16 : la redirection après génération pointe vers l'onglet Tâches, et cet onglet s'ouvre réellement */
    public function test_dev_16_redirection_onglet_taches(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000);
        $this->post(route('quotes.accept', $quote));
        $quote->refresh();

        $resp = $this->post(route('quotes.tasks', $quote));
        $resp->assertRedirect(route('projects.show', ['project' => $quote->project_id, 'tab' => 'tasks']));

        $page = $this->get($resp->headers->get('Location'));
        $page->assertSee("activeTab: 'tasks'", false);
    }

    /** DEV-17 : devis accepté sur un chantier existant cumule le marché et le budget */
    public function test_dev_17_cumul_sur_chantier_existant(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote1 = $this->createDraftQuote($company);
        $this->addItem($quote1, 100000, dbeTotal: 60000);
        $this->post(route('quotes.accept', $quote1));
        $quote1->refresh();
        $project = Project::find($quote1->project_id);
        $initialContract = (float) $project->contract_amount;
        $initialBudget = (float) $project->budget_total;

        $quote2 = $this->createDraftQuote($company, ['project_id' => $project->id]);
        $this->addItem($quote2, 80000, dbeTotal: 50000);
        $quote2->refresh();
        $q2Ttc = (float) $quote2->total_ttc;

        $this->post(route('quotes.accept', $quote2));
        $project->refresh();

        $this->assertEquals($initialContract + $q2Ttc, (float) $project->contract_amount);
        $this->assertEquals($initialBudget + 50000, (float) $project->budget_total);
        $this->assertTrue(
            $project->projectLogs()->where('description', 'like', '%Montant du marché porté%')->exists(),
            'DEV-17 : un log de cumul doit être ajouté'
        );
    }

    /** DEV-18, DEV-19 : duplication + versionnage */
    public function test_dev_18_19_duplication_et_versionnage(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000);

        $resp = $this->post(route('quotes.duplicate', $quote), ['title' => 'Copie']);
        $resp->assertRedirect();
        $this->assertSame(2, Quote::count());
        $dup = Quote::where('id', '!=', $quote->id)->first();
        $this->assertSame('brouillon', $dup->status);
        $this->assertSame(1, $dup->version);
        $this->assertCount(1, $dup->items);

        $quote->update(['status' => 'envoye']);
        $this->put(route('quotes.update', $quote), [
            'client_id' => $quote->client_id, 'title' => 'Titre modifié', 'quote_date' => now()->toDateString(),
        ]);
        $this->assertSame(2, $quote->fresh()->version, 'DEV-19 : version incrémentée après modification d\'un devis envoyé');
    }

    /** DEV-21, DEV-22 : conversion en facture + garde anti-double-facturation */
    public function test_dev_21_22_conversion_facture_et_anti_doublon(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000);
        $this->post(route('quotes.accept', $quote));
        $quote->refresh();

        $resp = $this->post(route('quotes.convert', $quote));
        $this->assertSame(1, Invoice::where('quote_id', $quote->id)->count());
        $invoice = Invoice::where('quote_id', $quote->id)->first();
        $resp->assertRedirect(route('invoices.show', $invoice));

        $this->post(route('quotes.convert', $quote));
        $this->assertSame(1, Invoice::where('quote_id', $quote->id)->count(), 'DEV-22 : pas de 2e facture');
    }

    /** DEV-23 : la remise globale devient une ligne négative sur la facture, cohérente avec le sous-total */
    public function test_dev_23_remise_globale_sur_facture(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company, ['discount_global' => 10, 'discount_type' => 'percent']);
        $this->addItem($quote, 100000);
        $this->addItem($quote, 50000);
        $this->post(route('quotes.accept', $quote));
        $quote->refresh();

        $this->post(route('quotes.convert', $quote));
        $invoice = Invoice::where('quote_id', $quote->id)->first();

        $this->assertEquals($invoice->subtotal_ht, $invoice->items()->sum('total_ht'));
        $this->assertTrue($invoice->items()->where('total_ht', '<', 0)->exists());
    }

    /** DEV-24 : garde anti-dépassement du montant du marché */
    public function test_dev_24_garde_depassement_marche(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $quote1 = $this->createDraftQuote($company);
        $this->addItem($quote1, 100000);
        $this->post(route('quotes.accept', $quote1));
        $quote1->refresh();
        $this->post(route('quotes.convert', $quote1));

        $quote2 = $this->createDraftQuote($company, ['project_id' => $quote1->project_id]);
        $this->addItem($quote2, 500000);
        $quote2->update(['status' => 'accepte']);

        $resp = $this->post(route('quotes.convert', $quote2));
        $resp->assertSessionHas('error');
        $this->assertSame(0, Invoice::where('quote_id', $quote2->id)->count());
    }

    /** DEV-09 : refus depuis le portail public ne crée pas de chantier */
    public function test_dev_09_refus_public(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $quote = $this->createDraftQuote($company);
        $this->addItem($quote, 100000);
        $quote->generateClientToken();
        $quote->update(['status' => 'envoye']);

        $resp = $this->post(route('quotes.public.validate', $quote->client_token), [
            'decision' => 'refuse', 'note' => 'Trop cher',
        ]);

        $resp->assertRedirect();
        $quote->refresh();
        $this->assertSame('refuse', $quote->status);
        $this->assertNull($quote->project_id);
    }

    /** DEV-25 (partiel) : deux créations successives obtiennent des références distinctes et séquentielles */
    public function test_dev_25_references_sequentielles(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $q1 = $this->createDraftQuote($company);
        $q2 = $this->createDraftQuote($company);

        $this->assertNotEquals($q1->reference, $q2->reference);
        $this->assertSame('DEV-0001', $q1->reference);
        $this->assertSame('DEV-0002', $q2->reference);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function createDraftQuote($company, array $attrs = []): Quote
    {
        $client = $this->makeClient($company);
        $resp = $this->post(route('quotes.store'), array_merge([
            'client_id' => $client->id,
            'title' => 'Devis test',
            'quote_date' => now()->toDateString(),
            'tva_rate' => 20,
        ], $attrs));

        return Quote::whereKey(
            (int) (string) str($resp->headers->get('Location'))->afterLast('/')
        )->firstOrFail();
    }

    private function addItem(Quote $quote, float $unitPrice, string $description = 'Ligne test', ?float $dbeTotal = null): void
    {
        $this->post(route('quotes.items.add', $quote), [
            'description' => $description, 'quantity' => 1, 'unit' => 'u', 'unit_price' => $unitPrice, 'discount' => 0,
        ]);

        if ($dbeTotal !== null) {
            // reorder() efface le tri par défaut de la relation (sort_order asc) avant d'imposer id desc.
            $quote->items()->reorder('id', 'desc')->first()->update(['dbe_total' => $dbeTotal]);
        }
    }
}
