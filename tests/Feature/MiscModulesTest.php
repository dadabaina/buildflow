<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * DOC-01/02, NOT-01, REP-01, AID-02 du cahier de recette : Documents, Notifications,
 * Reporting, Centre d'aide (vérifications de surface : accessibilité et données de base).
 */
class MiscModulesTest extends RecetteTestCase
{
    /** DOC-01, DOC-02 : upload puis suppression d'un document */
    public function test_doc_01_02_upload_et_suppression(): void
    {
        Storage::fake('private');
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier DOC']);

        $resp = $this->post(route('documents.store'), [
            'project_id' => $project->id, 'category' => 'plan',
            'file' => UploadedFile::fake()->create('plan.pdf', 100, 'application/pdf'),
        ]);
        $resp->assertRedirect();
        $document = Document::first();
        $this->assertNotNull($document);

        $this->delete(route('documents.destroy', $document))->assertRedirect();
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    /** NOT-01 : le centre de notifications est accessible */
    public function test_not_01_centre_notifications_accessible(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $this->get(route('notifications.index'))->assertOk();
    }

    /** REP-01 : le rapport financier est accessible et cohérent */
    public function test_rep_01_rapport_financier_accessible(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $this->get(route('reports.financial'))->assertOk();
    }

    /** AID-02 : la page hub du centre d'aide liste les guides par catégorie, avec parcours de démarrage, schéma du cycle et FAQ */
    public function test_aid_02_page_hub_aide(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $page = $this->get(route('help.index'));
        $page->assertOk();
        $page->assertSee('Chantiers');
        $page->assertSee('Devis');
        $page->assertSee('Premiers pas');
        $page->assertSee('0/7', false); // aucune étape faite pour une société toute neuve
        $page->assertSee('Le cycle de BuildFlow');
        $page->assertSee('Configuration initiale');
        $page->assertSee('Utilisation quotidienne');
        $page->assertSee('Questions fréquentes');
        $page->assertSee('Budget initial'); // extrait d\'une réponse de la FAQ
    }

    /** AID-05 : le parcours de démarrage détecte réellement l\'avancement (pas une checklist statique) */
    public function test_aid_05_parcours_demarrage_progression_reelle(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $this->get(route('help.index'))->assertSee('0/7', false);

        $client = $this->makeClient($company);
        $this->get(route('help.index'))->assertSee('1/7', false);

        $quote = Quote::create([
            'company_id' => $company->id, 'client_id' => $client->id, 'created_by' => auth()->id(),
            'reference' => 'DEV-TEST', 'title' => 'Devis test', 'quote_date' => now(), 'tva_rate' => 20,
            'discount_global' => 0, 'discount_type' => 'percent', 'status' => 'brouillon',
            'subtotal_ht' => 0, 'discount_amount' => 0, 'taxable_ht' => 0, 'tva_amount' => 0, 'total_ttc' => 100000,
        ]);
        $this->get(route('help.index'))->assertSee('2/7', false);

        $quote->update(['status' => 'accepte']);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $client->id, 'name' => 'Chantier test']);
        $this->get(route('help.index'))->assertSee('4/7', false); // devis accepté + chantier visible = 2 étapes de plus

        Invoice::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'client_id' => $client->id, 'created_by' => auth()->id(),
            'reference' => 'FAC-TEST', 'title' => 'Facture test', 'type' => 'standard', 'invoice_date' => now(),
            'tva_rate' => 20, 'subtotal_ht' => 0, 'tva_amount' => 0, 'total_ttc' => 0, 'rg_amount' => 0,
            'net_to_pay' => 0, 'amount_paid' => 0, 'amount_remaining' => 0, 'status' => 'brouillon',
        ]);
        $this->get(route('help.index'))->assertSee('5/7', false);
    }

    /** AID-06 : le centre d'aide n'affiche que les modules accessibles au rôle connecté */
    public function test_aid_06_filtrage_par_role(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'operateur');

        $page = $this->get(route('help.index'));
        $page->assertOk();
        // L'opérateur a accès aux dépenses...
        $page->assertSee('Dépenses');
        // ...mais pas à l'administration, réservée aux rôles avec la permission correspondante.
        $page->assertDontSee('Utilisateurs');
        $page->assertDontSee('Configurer les listes utilisées');
    }

    /** AID-03 : les 5 modules du cycle central ont un guide détaillé (modale étapes + champs), les autres gardent le tour interactif */
    public function test_aid_03_guides_detailles_cycle_central(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $page = $this->get(route('help.index'));
        $page->assertOk();

        // Modules avec guide détaillé : bouton = déclencheur de modale, pas un lien direct.
        foreach (['quotes-index', 'projects-index', 'tasks-index', 'expenses-index', 'invoices-index'] as $slug) {
            $page->assertSee('id="guide-' . $slug . '"', false);
        }
        $page->assertSee('Explication des champs');
        $page->assertSee('Client'); // champ du guide Devis
        $page->assertSee('Obligatoire');

        // Module sans guide détaillé (ex: avenants) : conserve le lien direct ?tour=1.
        $page->assertSee(route('amendments.index') . '?tour=1', false);
    }

    /**
     * AID-04 : l'icône « Aide » du bandeau pointe toujours vers /aide, sur n'importe quelle
     * page — y compris une page sans visite guidée interactive enregistrée (ex. dashboard).
     * Non-régression du bug où l'icône se désactivait (pointer-events: none côté JS) et
     * devenait totalement incliquable dès qu'aucun guide n'existait pour la page courante.
     */
    public function test_aid_04_icone_aide_toujours_accessible(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        foreach ([route('dashboard'), route('help.index')] as $url) {
            $page = $this->get($url);
            $page->assertOk();
            $page->assertSee('id="help-launch-btn"', false);
            $page->assertSee('href="' . route('help.index') . '"', false);
        }
    }
}
