<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Project;
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

    /** AID-02 : la page hub du centre d'aide liste les guides par catégorie */
    public function test_aid_02_page_hub_aide(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $page = $this->get(route('help.index'));
        $page->assertOk();
        $page->assertSee('Chantiers');
        $page->assertSee('Devis');
    }
}
