<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;

/**
 * AUTH-01 à AUTH-06, ADM-01 à ADM-04, MULTI-01 à MULTI-03 du cahier de recette.
 */
class AdminAuthMultiTest extends RecetteTestCase
{
    /** AUTH-01, AUTH-02 : connexion valide / invalide */
    public function test_auth_01_02_connexion(): void
    {
        $company = $this->makeCompany();
        $user = $this->makeUser($company, 'admin', ['email' => 'test@buildflow.local', 'password' => bcrypt('secret123')]);

        $this->post(route('login'), ['email' => 'test@buildflow.local', 'password' => 'wrong'])
            ->assertSessionHasErrors();
        $this->assertGuest();

        $this->post(route('login'), ['email' => 'test@buildflow.local', 'password' => 'secret123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** AUTH-04 : déconnexion */
    public function test_auth_04_deconnexion(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /** AUTH-05 : accès sans droit refusé */
    public function test_auth_05_acces_sans_droit(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'operateur');

        $this->get(route('users.index'))->assertForbidden();
    }

    /** AUTH-06 : modification du profil */
    public function test_auth_06_modification_profil(): void
    {
        $company = $this->makeCompany();
        $user = $this->actingAsCompanyUser($company);

        $this->patch(route('profile.update'), ['name' => 'Nouveau Nom', 'email' => $user->email])
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('Nouveau Nom', $user->fresh()->name);
    }

    /** ADM-01 : création d'un utilisateur avec un rôle */
    public function test_adm_01_creation_utilisateur(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'admin');
        setPermissionsTeamId($company->id);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web', 'company_id' => $company->id]);

        $resp = $this->post(route('users.store'), [
            'name' => 'Nouvel utilisateur', 'email' => 'nouveau@test.local',
            'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'manager',
        ]);
        $resp->assertRedirect(route('users.index'));

        $newUser = User::where('email', 'nouveau@test.local')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('manager'));
    }

    /**
     * ADM-02 : gestion des rôles. RoleController appelle $this->authorize('roles.view'|'roles.create'|...),
     * mais ces permissions ne sont jamais créées par RolesAndPermissionsSeeder (seules 'users.*' existent).
     * Ce test vérifie ce qui se passe réellement pour un admin (censé avoir tous les droits).
     */
    public function test_adm_02_gestion_des_roles(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'admin');

        $this->get(route('roles.index'))->assertOk();
    }

    /** ADM-04 : référentiels (région) disponible immédiatement */
    public function test_adm_04_creation_region(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'admin');

        $this->post(route('settings.regions.store'), ['name' => 'Analamanga'])
            ->assertSessionHas('success');
        $this->assertTrue($company->regions()->where('name', 'Analamanga')->exists());
    }

    /** MULTI-01 : étanchéité des listes entre deux sociétés */
    public function test_multi_01_etancheite_des_listes(): void
    {
        $companyA = $this->makeCompany();
        $companyB = $this->makeCompany();
        Client::create(['company_id' => $companyA->id, 'name' => 'Client Société A', 'type' => 'entreprise']);
        Client::create(['company_id' => $companyB->id, 'name' => 'Client Société B', 'type' => 'entreprise']);

        $this->actingAsCompanyUser($companyA, 'admin');
        $page = $this->get(route('clients.index'));
        $page->assertSee('Client Société A');
        $page->assertDontSee('Client Société B');
    }

    /** MULTI-02 : accès direct par URL à une ressource d'une autre société refusé */
    public function test_multi_02_acces_direct_url_refuse(): void
    {
        $companyA = $this->makeCompany();
        $companyB = $this->makeCompany();
        $clientB = Client::create(['company_id' => $companyB->id, 'name' => 'Client B', 'type' => 'entreprise']);

        $this->actingAsCompanyUser($companyA, 'admin');
        // Le global scope multi-société (CompanyScope) filtre la ressource avant même
        // le binding de route : la réponse est 404 (pas 403), sans fuite d'existence.
        $this->get(route('clients.show', $clientB))->assertNotFound();
    }

    /** MULTI-03 : références indépendantes entre sociétés */
    public function test_multi_03_references_independantes(): void
    {
        $companyA = $this->makeCompany();
        $companyB = $this->makeCompany();

        $this->actingAsCompanyUser($companyA, 'admin');
        $clientA = $this->makeClient($companyA);
        $this->post(route('quotes.store'), [
            'client_id' => $clientA->id, 'title' => 'Devis A', 'quote_date' => now()->toDateString(), 'tva_rate' => 20,
        ]);
        $quoteA = \App\Models\Quote::first();

        $this->post(route('logout'));
        $this->actingAsCompanyUser($companyB, 'admin');
        $clientB = $this->makeClient($companyB);
        $this->post(route('quotes.store'), [
            'client_id' => $clientB->id, 'title' => 'Devis B', 'quote_date' => now()->toDateString(), 'tva_rate' => 20,
        ]);
        $quoteB = \App\Models\Quote::where('company_id', $companyB->id)->first();

        $this->assertSame('DEV-0001', $quoteA->reference);
        $this->assertSame('DEV-0001', $quoteB->reference, 'MULTI-03 : chaque société a sa propre numérotation');
    }
}
