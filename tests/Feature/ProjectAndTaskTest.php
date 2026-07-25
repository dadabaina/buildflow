<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;

/**
 * CHA-01 à CHA-12 et TSK-01 à TSK-12 du cahier de recette : Chantiers et Tâches.
 */
class ProjectAndTaskTest extends RecetteTestCase
{
    /** CHA-01 : création manuelle d'un chantier */
    public function test_cha_01_creation_manuelle(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $client = $this->makeClient($company);

        $resp = $this->post(route('projects.store'), [
            'name' => 'Villa Andohalo', 'client_id' => $client->id,
        ]);

        $project = Project::first();
        $resp->assertRedirect(route('projects.show', $project));
        $this->assertSame('prospection', $project->status);
    }

    /** CHA-03 : transition de statut autorisée / refusée */
    public function test_cha_03_transition_statut(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        // prospection -> devis_en_cours : autorisé
        $this->patch(route('projects.status', $project), ['status' => 'devis_en_cours'])
            ->assertSessionHas('success');
        $this->assertSame('devis_en_cours', $project->fresh()->status);

        // devis_en_cours -> cloture : non autorisé (pas dans les transitions)
        $this->patch(route('projects.status', $project), ['status' => 'cloture'])
            ->assertSessionHas('error');
        $this->assertSame('devis_en_cours', $project->fresh()->status);
    }

    /** CHA-06, CHA-07 : plus de 20 tâches toutes visibles, badge et avancement corrects */
    public function test_cha_06_07_pas_de_plafond_sur_les_taches(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        for ($i = 1; $i <= 25; $i++) {
            Task::create([
                'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => auth()->id(),
                'title' => "Tâche $i", 'status' => $i <= 15 ? 'termine' : 'a_faire',
                'priority' => 'normale', 'weight' => 1,
            ]);
        }

        $page = $this->get(route('projects.show', $project));
        $page->assertOk();
        $page->assertSee('25</span>', false); // badge du nombre total de tâches
        $page->assertSee('15/25', false); // avancement : 15 terminées sur 25
        $page->assertSee('Tâche 25');
    }

    /** TSK-11 : le paramètre ?tab=tasks ouvre réellement l'onglet Tâches */
    public function test_tsk_11_onglet_tasks_via_query_param(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        $page = $this->get(route('projects.show', ['project' => $project->id, 'tab' => 'tasks']));
        $page->assertSee("activeTab: 'tasks'", false);

        $default = $this->get(route('projects.show', $project));
        $default->assertSee("activeTab: 'infos'", false);
    }

    /** TSK-01 : création manuelle d'une tâche */
    public function test_tsk_01_creation_manuelle(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        $resp = $this->post(route('tasks.store'), [
            'project_id' => $project->id, 'title' => 'Couler la dalle',
            'status' => 'a_faire', 'priority' => 'normale', 'weight' => 1,
        ]);

        $task = Task::first();
        $resp->assertRedirect(route('tasks.show', $task));
        $this->assertTrue($project->projectLogs()->where('action', 'task_created')->exists());
    }

    /** TSK-03 : changement de statut */
    public function test_tsk_03_changement_statut(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $task = $this->makeTask($project, $company);

        $this->patch(route('tasks.status', $task), ['status' => 'en_cours'])->assertSessionHas('success');
        $this->assertSame('en_cours', $task->fresh()->status);
    }

    /** TSK-04 : réordonnancement au sein d'une colonne, persistant */
    public function test_tsk_04_reorder_persistant(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $t1 = $this->makeTask($project, $company, ['title' => 'A']);
        $t2 = $this->makeTask($project, $company, ['title' => 'B']);

        $this->patch(route('tasks.reorder'), [
            'status' => 'a_faire', 'task_ids' => [$t2->id, $t1->id],
        ])->assertOk();

        $this->assertEquals(0, $t2->fresh()->sort_order);
        $this->assertEquals(1, $t1->fresh()->sort_order);
    }

    /** TSK-05 : checklist et progression */
    public function test_tsk_05_checklist_et_progression(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $task = $this->makeTask($project, $company);

        $this->patch(route('tasks.checklist', $task), [
            'checklist' => [
                ['label' => 'Terrasser', 'done' => true],
                ['label' => 'Couler', 'done' => false],
            ],
        ])->assertOk();

        $task->refresh();
        $this->assertSame(50, $task->progress_percent);
    }

    /** TSK-06 : commentaires */
    public function test_tsk_06_commentaires(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $task = $this->makeTask($project, $company);

        $this->post(route('tasks.comments.store', $task), ['body' => 'Attention aux fers à béton'])
            ->assertSessionHas('success');
        $this->assertSame(1, $task->comments()->count());
    }

    /** TSK-07 : assignation d'employés */
    public function test_tsk_07_assignation_employes(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $task = $this->makeTask($project, $company);
        $employee = Employee::create(['company_id' => $company->id, 'first_name' => 'Jean', 'last_name' => 'Rakoto']);

        $this->put(route('tasks.update', $task), [
            'project_id' => $project->id, 'title' => $task->title, 'status' => 'a_faire',
            'priority' => 'normale', 'weight' => 1, 'employee_ids' => [$employee->id],
        ])->assertRedirect(route('tasks.show', $task));

        $this->assertTrue($task->employees()->where('employees.id', $employee->id)->exists());
    }

    /** TSK-12 : suppression d'une tâche */
    public function test_tsk_12_suppression(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $task = $this->makeTask($project, $company);
        $expense = \App\Models\Expense::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'task_id' => $task->id,
            'created_by' => auth()->id(), 'description' => 'Dépense liée', 'expense_date' => now()->toDateString(),
            'quantity' => 1, 'unit_price' => 5000, 'status' => 'saisie',
        ]);

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);

        // La dépense reste rattachée au chantier mais est détachée de la tâche supprimée.
        $expense->refresh();
        $this->assertNull($expense->task_id);
        $this->assertSame($project->id, $expense->project_id);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'deleted_at' => null]);
    }

    /** CHA-09bis : la photo de l'employé (si définie) apparaît dans l'onglet Équipe du chantier */
    public function test_cha_09bis_photo_employe_dans_equipe_chantier(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        $sansPhoto = Employee::create(['company_id' => $company->id, 'first_name' => 'Jean', 'last_name' => 'Sans Photo']);
        $avecPhoto = Employee::create(['company_id' => $company->id, 'first_name' => 'Marie', 'last_name' => 'Avec Photo', 'photo_path' => 'employees/test.webp']);
        $project->employees()->sync([$sansPhoto->id, $avecPhoto->id]);

        $page = $this->get(route('projects.show', ['project' => $project->id, 'tab' => 'team']));
        $page->assertOk();

        // Avec photo : une balise <img> pointant vers le fichier stocké.
        $page->assertSee('src="' . asset('storage/employees/test.webp') . '"', false);
        // Sans photo : repli sur les initiales, comme avant.
        $page->assertSee('JS'); // initiales de "Jean" "Sans Photo"
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function makeProject($company, array $attrs = []): Project
    {
        $client = $this->makeClient($company);
        return Project::create(array_merge([
            'company_id' => $company->id, 'client_id' => $client->id,
            'name' => 'Chantier test',
        ], $attrs));
    }

    private function makeTask(Project $project, $company, array $attrs = []): Task
    {
        return Task::create(array_merge([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => auth()->id(),
            'title' => 'Tâche test', 'status' => 'a_faire', 'priority' => 'normale', 'weight' => 1,
        ], $attrs));
    }
}
