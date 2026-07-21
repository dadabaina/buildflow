<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProgressBilling;
use App\Models\Quote;

/**
 * FAC-01 à FAC-09 et PAY-01 à PAY-08 du cahier de recette.
 */
class InvoiceAndPaymentTest extends RecetteTestCase
{
    /** FAC-01, FAC-02 : création manuelle + ajout/suppression de lignes */
    public function test_fac_01_02_creation_et_lignes(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        $resp = $this->post(route('invoices.store'), [
            'project_id' => $project->id, 'client_id' => $project->client_id,
            'title' => 'Facture travaux', 'type' => 'standard',
            'invoice_date' => now()->toDateString(), 'tva_rate' => 20,
        ]);
        $invoice = Invoice::first();
        $resp->assertRedirect(route('invoices.show', $invoice));
        $this->assertSame('brouillon', $invoice->status);

        $this->post(route('invoices.items.add', $invoice), [
            'description' => 'Gros oeuvre', 'quantity' => 1, 'unit_price' => 500000,
        ]);
        $invoice->refresh();
        $this->assertEquals(500000, $invoice->subtotal_ht);
        $this->assertEquals(600000, $invoice->total_ttc);

        $item = $invoice->items()->first();
        $this->delete(route('invoices.items.remove', [$invoice, $item]));
        $invoice->refresh();
        $this->assertEquals(0, $invoice->subtotal_ht);
    }

    /** FAC-03 : envoi */
    public function test_fac_03_envoi(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice = $this->makeInvoice($company, ['status' => 'brouillon']);

        $this->post(route('invoices.send', $invoice))->assertSessionHas('success');
        $this->assertSame('envoye', $invoice->fresh()->status);
    }

    /** FAC-08 : annulation */
    public function test_fac_08_annulation(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice = $this->makeInvoice($company, ['status' => 'envoye']);

        $this->post(route('invoices.cancel', $invoice))->assertSessionHas('success');
        $this->assertSame('annulee', $invoice->fresh()->status);
    }

    /** FAC-09 : facture soldée non modifiable/supprimable */
    public function test_fac_09_facture_soldee_non_modifiable(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice = $this->makeInvoice($company, ['status' => 'soldee']);

        $this->get(route('invoices.edit', $invoice))->assertSessionHas('error');
        $this->delete(route('invoices.destroy', $invoice))->assertSessionHas('error');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'deleted_at' => null]);
    }

    /** FAC-04, FAC-05, FAC-06 : situation de travaux -> validation -> facturation */
    public function test_fac_04_05_06_situation_de_travaux(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company, ['contract_amount' => 10_000_000]);
        $quote = $this->makeAcceptedQuote($company, $project);

        $resp = $this->post(route('progress-billings.store'), [
            'project_id' => $project->id, 'quote_id' => $quote->id, 'title' => 'Situation n°1',
            'billing_date' => now()->toDateString(), 'rg_rate' => 5, 'tva_rate' => 20,
            'lines' => [[
                'description' => 'Terrassement', 'quote_quantity' => 100, 'unit_price' => 10000,
                'unit' => 'm3', 'previous_pct' => 0, 'current_pct' => 50,
            ]],
        ]);
        $resp->assertRedirect(route('progress-billings.index'));
        $billing = ProgressBilling::first();
        $this->assertSame('brouillon', $billing->status);
        $this->assertEquals(500000, $billing->subtotal_ht); // 100*10000*50%

        // FAC-05 : envoi puis validation
        $this->post(route('progress-billings.send', $billing));
        $this->assertSame('envoye', $billing->fresh()->status);
        $this->post(route('progress-billings.validate', $billing));
        $this->assertSame('valide', $billing->fresh()->status);

        // FAC-06 : génération de la facture
        $resp = $this->post(route('progress-billings.invoice', $billing));
        $invoiceId = Invoice::where('project_id', $project->id)->value('id');
        $resp->assertRedirect(route('invoices.show', $invoiceId));
        $billing->refresh();
        $this->assertSame('facture', $billing->status);
        $this->assertEquals($billing->net_to_pay, Invoice::find($invoiceId)->net_to_pay);
    }

    /** FAC-07 : garde anti-dépassement du marché sur les situations */
    public function test_fac_07_garde_depassement_marche_situation(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company, ['contract_amount' => 100000]);
        $quote = $this->makeAcceptedQuote($company, $project);

        $this->post(route('progress-billings.store'), [
            'project_id' => $project->id, 'quote_id' => $quote->id, 'title' => 'Situation trop élevée',
            'billing_date' => now()->toDateString(), 'rg_rate' => 0, 'tva_rate' => 20,
            'lines' => [[
                'description' => 'Terrassement', 'quote_quantity' => 100, 'unit_price' => 10000,
                'unit' => 'm3', 'previous_pct' => 0, 'current_pct' => 100,
            ]],
        ]);
        $billing = ProgressBilling::first();
        $billing->update(['status' => 'valide']);

        $resp = $this->post(route('progress-billings.invoice', $billing));
        $resp->assertSessionHas('error');
        $this->assertSame(0, Invoice::where('project_id', $project->id)->count());
    }

    /** PAY-01, PAY-02 : paiement partiel puis solde complet */
    public function test_pay_01_02_partiel_puis_solde(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice = $this->makeInvoice($company, ['status' => 'envoye', 'net_to_pay' => 100000, 'amount_remaining' => 100000]);

        $this->post(route('payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 40000, 'payment_date' => now()->toDateString(), 'method' => 'especes',
        ])->assertSessionDoesntHaveErrors();
        $invoice->refresh();
        $this->assertSame('partiellement_payee', $invoice->status);
        $this->assertEquals(40000, $invoice->amount_paid);
        $this->assertEquals(60000, $invoice->amount_remaining);

        $this->post(route('payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 60000, 'payment_date' => now()->toDateString(), 'method' => 'especes',
        ]);
        $invoice->refresh();
        $this->assertSame('soldee', $invoice->status);
        $this->assertEquals(0, $invoice->amount_remaining);
    }

    /** PAY-03 : paiement refusé sur facture brouillon */
    public function test_pay_03_refuse_sur_brouillon(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice = $this->makeInvoice($company, ['status' => 'brouillon', 'net_to_pay' => 50000, 'amount_remaining' => 50000]);

        $this->post(route('payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 10000, 'payment_date' => now()->toDateString(), 'method' => 'especes',
        ])->assertSessionHas('error');
        $this->assertSame(0, Payment::count());
    }

    /** PAY-04 : paiement refusé sur facture annulée */
    public function test_pay_04_refuse_sur_annulee(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice = $this->makeInvoice($company, ['status' => 'annulee', 'net_to_pay' => 50000, 'amount_remaining' => 50000]);

        $this->post(route('payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 10000, 'payment_date' => now()->toDateString(), 'method' => 'especes',
        ])->assertSessionHas('error');
        $this->assertSame(0, Payment::count());
    }

    /** PAY-05 : surpaiement refusé */
    public function test_pay_05_surpaiement_refuse(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice = $this->makeInvoice($company, ['status' => 'envoye', 'net_to_pay' => 50000, 'amount_remaining' => 50000]);

        $this->post(route('payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 90000, 'payment_date' => now()->toDateString(), 'method' => 'especes',
        ])->assertSessionHas('error');
        $this->assertSame(0, Payment::count());
    }

    /** PAY-07 : suppression d'un paiement recalcule toutes les factures liées */
    public function test_pay_07_suppression_recalcule_toutes_les_factures(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $invoice1 = $this->makeInvoice($company, ['status' => 'envoye', 'net_to_pay' => 50000, 'amount_remaining' => 50000]);
        $invoice2 = $this->makeInvoice($company, ['status' => 'envoye', 'net_to_pay' => 30000, 'amount_remaining' => 30000], $invoice1->project);

        $payment = Payment::create([
            'company_id' => $company->id, 'project_id' => $invoice1->project_id, 'client_id' => $invoice1->client_id,
            'created_by' => auth()->id(), 'amount' => 80000, 'payment_date' => now()->toDateString(), 'payment_mode' => 'especes',
        ]);
        $payment->invoices()->attach($invoice1->id, ['amount' => 50000]);
        $payment->invoices()->attach($invoice2->id, ['amount' => 30000]);
        $invoice1->updatePaymentStatus();
        $invoice2->updatePaymentStatus();
        $this->assertSame('soldee', $invoice1->fresh()->status);
        $this->assertSame('soldee', $invoice2->fresh()->status);

        $this->delete(route('payments.destroy', $payment));

        $this->assertSame('envoye', $invoice1->fresh()->status, 'PAY-07 : facture 1 recalculée après suppression');
        $this->assertSame('envoye', $invoice2->fresh()->status, 'PAY-07 : facture 2 recalculée après suppression');
        $this->assertEquals(0, $invoice1->fresh()->amount_paid);
        $this->assertEquals(0, $invoice2->fresh()->amount_paid);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function makeProject($company, array $attrs = []): Project
    {
        return Project::create(array_merge([
            'company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier test',
        ], $attrs));
    }

    private function makeInvoice($company, array $attrs = [], ?Project $project = null): Invoice
    {
        $project ??= $this->makeProject($company);
        return Invoice::create(array_merge([
            'company_id' => $company->id, 'project_id' => $project->id, 'client_id' => $project->client_id,
            'created_by' => auth()->id(), 'reference' => 'FAC-TEST-' . uniqid(),
            'title' => 'Facture test', 'type' => 'standard', 'invoice_date' => now()->toDateString(),
            'tva_rate' => 20, 'subtotal_ht' => 0, 'tva_amount' => 0, 'total_ttc' => 0,
            'rg_amount' => 0, 'net_to_pay' => 0, 'amount_paid' => 0, 'amount_remaining' => 0,
            'status' => 'brouillon',
        ], $attrs));
    }

    private function makeAcceptedQuote($company, Project $project): Quote
    {
        $quote = Quote::create([
            'company_id' => $company->id, 'client_id' => $project->client_id, 'project_id' => $project->id,
            'created_by' => auth()->id(), 'reference' => 'DEV-TEST-' . uniqid(), 'title' => 'Devis accepté test',
            'quote_date' => now()->toDateString(), 'tva_rate' => 20, 'status' => 'accepte',
            'subtotal_ht' => 0, 'discount_amount' => 0, 'taxable_ht' => 0, 'tva_amount' => 0, 'total_ttc' => 0,
        ]);
        $quote->items()->create([
            'description' => 'Terrassement', 'quantity' => 100, 'unit' => 'm3', 'unit_price' => 10000,
            'discount' => 0, 'total_ht' => 1000000, 'sort_order' => 1,
        ]);
        return $quote;
    }
}
