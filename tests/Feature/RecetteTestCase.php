<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Base commune pour les tests de recette (cahier_de_recette.md).
 * Fournit les usines de données minimales pour chaque module métier.
 *
 * Utilise DatabaseTransactions (pas RefreshDatabase) : le schéma de
 * `buildflow_testing` est migré une fois pour toutes (php artisan migrate),
 * chaque test s'exécute dans une transaction annulée à la fin. Si de
 * nouvelles migrations sont ajoutées, relancer `php artisan migrate`
 * manuellement contre cette base avant de relancer les tests.
 */
abstract class RecetteTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function makeCompany(array $attrs = []): Company
    {
        return Company::create(array_merge([
            'name' => 'Société Test ' . Str::random(6),
            'slug' => Str::slug('societe-test-' . Str::random(8)),
            'is_active' => true,
        ], $attrs));
    }

    /** Crée les rôles/permissions pour une société (scope Spatie "teams") et renvoie un utilisateur avec le rôle demandé. */
    protected function makeUser(Company $company, string $role = 'admin', array $attrs = []): User
    {
        setPermissionsTeamId($company->id);
        if (! \Spatie\Permission\Models\Role::where('company_id', $company->id)->where('name', $role)->exists()) {
            $this->seed(RolesAndPermissionsSeeder::class);
        }

        $user = User::create(array_merge([
            'company_id' => $company->id,
            'name' => 'Utilisateur Test',
            'email' => 'user' . Str::random(8) . '@test.local',
            'password' => bcrypt('password'),
            'is_active' => true,
        ], $attrs));

        $user->assignRole($role);

        return $user;
    }

    protected function actingAsCompanyUser(Company $company, string $role = 'admin'): User
    {
        $user = $this->makeUser($company, $role);
        $this->actingAs($user);
        setPermissionsTeamId($company->id);
        session(['company_id' => $company->id]);

        return $user;
    }

    protected function makeClient(Company $company, array $attrs = []): Client
    {
        return Client::create(array_merge([
            'company_id' => $company->id,
            'name' => 'Client Test ' . Str::random(6),
            'type' => 'entreprise',
        ], $attrs));
    }

    protected function makeExpenseCategory(Company $company, array $attrs = []): ExpenseCategory
    {
        return ExpenseCategory::create(array_merge([
            'company_id' => $company->id,
            'name' => 'Catégorie Test ' . Str::random(4),
        ], $attrs));
    }

    protected function makeRegion(Company $company, array $attrs = []): Region
    {
        return Region::create(array_merge([
            'company_id' => $company->id,
            'name' => 'Région Test ' . Str::random(4),
        ], $attrs));
    }
}
