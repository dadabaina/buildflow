<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Rôle "chef_chantier" : scope aux chantiers assignés (project_managers) et
 * kiosque de pointage terrain (Entrée/Sortie avec photo + horodatage auto).
 */
class ChefChantierKiosqueTest extends RecetteTestCase
{
    private function makeProject($company, array $attrs = []): Project
    {
        $client = $this->makeClient($company);
        return Project::create(array_merge([
            'company_id' => $company->id, 'client_id' => $client->id,
            'name' => 'Chantier test', 'status' => 'en_cours',
        ], $attrs));
    }

    /** Le chef de chantier ne voit et ne peut gérer que les chantiers qui lui sont assignés. */
    public function test_chef_chantier_scope_projets_assignes(): void
    {
        $company = $this->makeCompany();
        $admin = $this->actingAsCompanyUser($company, 'admin');
        $monChantier = $this->makeProject($company, ['name' => 'Mon chantier']);
        $autreChantier = $this->makeProject($company, ['name' => 'Autre chantier']);

        $chef = $this->makeUser($company, 'chef_chantier');
        $chef->managedProjects()->sync([$monChantier->id]);

        $this->actingAs($chef);
        session(['company_id' => $company->id]);
        setPermissionsTeamId($company->id);

        // Accès autorisé à son propre chantier
        $this->get(route('projects.show', $monChantier))->assertOk();

        // Accès refusé au chantier d'un autre chef de chantier
        $this->get(route('projects.show', $autreChantier))->assertForbidden();
        $this->put(route('projects.update', $autreChantier), ['name' => 'Hack', 'client_id' => $autreChantier->client_id])
            ->assertForbidden();
    }

    /** Le chef de chantier peut modifier les champs opérationnels mais pas les champs financiers. */
    public function test_chef_chantier_lecture_seule_sur_financier(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'admin');
        $project = $this->makeProject($company, ['contract_amount' => 1000000, 'name' => 'Chantier X']);

        $chef = $this->makeUser($company, 'chef_chantier');
        $chef->managedProjects()->sync([$project->id]);
        $this->actingAs($chef);
        session(['company_id' => $company->id]);
        setPermissionsTeamId($company->id);

        $this->put(route('projects.update', $project), [
            'name' => 'Chantier X modifié',
            'client_id' => $project->client_id,
            'contract_amount' => 999999999,
        ])->assertRedirect(route('projects.show', $project));

        $project->refresh();
        $this->assertSame('Chantier X modifié', $project->name, 'Le champ opérationnel doit être modifiable');
        $this->assertEquals(1000000, (float) $project->contract_amount, 'Le montant du marché ne doit pas être modifiable par le chef de chantier');
    }

    /** Kiosque : Entrée puis Sortie avec photo, horodatage automatique, une seule ligne par jour. */
    public function test_kiosque_entree_puis_sortie(): void
    {
        Storage::fake('public');
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'admin');
        $project = $this->makeProject($company);
        $employee = Employee::create(['company_id' => $company->id, 'first_name' => 'Jean', 'last_name' => 'Rakoto']);
        $project->employees()->attach($employee->id, ['is_active' => true]);

        $chef = $this->makeUser($company, 'chef_chantier');
        $chef->managedProjects()->sync([$project->id]);
        $this->actingAs($chef);
        session(['company_id' => $company->id]);
        setPermissionsTeamId($company->id);

        $this->get(route('pointage.kiosque', ['project_id' => $project->id]))
            ->assertOk()
            ->assertSee('Jean Rakoto');

        $this->post(route('pointage.entree', [$project, $employee]), [
            'photo' => UploadedFile::fake()->image('entree.jpg', 400, 400),
        ])->assertRedirect(route('pointage.kiosque', ['project_id' => $project->id]));

        $attendance = Attendance::where('employee_id', $employee->id)->first();
        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in);
        $this->assertNotNull($attendance->photo_path_in);
        $this->assertSame($chef->id, $attendance->checked_in_by);
        $this->assertNull($attendance->check_out);

        // Recule l'heure d'entrée (sans voyager dans le temps global, ce qui risquerait
        // de traverser minuit selon l'heure d'exécution des tests) pour simuler une
        // vraie journée de travail et vérifier le calcul des heures.
        $attendance->update(['check_in' => '08:00']);
        $this->post(route('pointage.sortie', [$project, $employee]), [
            'photo'       => UploadedFile::fake()->image('sortie.jpg', 400, 400),
            // Horodatage fixé en après-midi : le calcul des heures ne doit pas
            // dépendre de l'heure d'exécution réelle du test (sinon un run
            // avant 08h/09h locale produirait un intervalle négatif).
            'captured_at' => now()->setTime(17, 0)->toDateTimeString(),
        ])->assertRedirect(route('pointage.kiosque', ['project_id' => $project->id]));

        $attendance->refresh();
        $this->assertNotNull($attendance->check_out);
        $this->assertNotNull($attendance->photo_path_out);
        $this->assertSame($chef->id, $attendance->checked_out_by);
        $this->assertNotNull($attendance->hours_worked);

        // Une seule ligne pour la journée (upsert, pas de doublon)
        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->count());
    }

    /** Impossible de pointer la Sortie sans avoir pointé l'Entrée le même jour. */
    public function test_kiosque_sortie_sans_entree_refusee(): void
    {
        Storage::fake('public');
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'admin');
        $project = $this->makeProject($company);
        $employee = Employee::create(['company_id' => $company->id, 'first_name' => 'Marie', 'last_name' => 'Rasoa']);
        $project->employees()->attach($employee->id, ['is_active' => true]);

        $chef = $this->makeUser($company, 'chef_chantier');
        $chef->managedProjects()->sync([$project->id]);
        $this->actingAs($chef);
        session(['company_id' => $company->id]);
        setPermissionsTeamId($company->id);

        $resp = $this->post(route('pointage.sortie', [$project, $employee]), [
            'photo' => UploadedFile::fake()->image('sortie.jpg', 400, 400),
        ]);
        $resp->assertSessionHas('error');
        $this->assertSame(0, Attendance::where('employee_id', $employee->id)->count());
    }

    /** Un chef de chantier ne peut pas pointer un employé d'un chantier qui ne lui est pas assigné. */
    public function test_kiosque_refuse_chantier_non_assigne(): void
    {
        Storage::fake('public');
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company, 'admin');
        $monChantier = $this->makeProject($company);
        $autreChantier = $this->makeProject($company);
        $employee = Employee::create(['company_id' => $company->id, 'first_name' => 'Paul', 'last_name' => 'Andry']);
        $employee->projects()->attach($autreChantier->id, ['is_active' => true]);

        $chef = $this->makeUser($company, 'chef_chantier');
        $chef->managedProjects()->sync([$monChantier->id]);
        $this->actingAs($chef);
        session(['company_id' => $company->id]);
        setPermissionsTeamId($company->id);

        $this->post(route('pointage.entree', [$autreChantier, $employee]), [
            'photo' => UploadedFile::fake()->image('entree.jpg', 400, 400),
        ])->assertForbidden();
    }
}
