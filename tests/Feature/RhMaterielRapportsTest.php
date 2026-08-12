<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Equipment;
use App\Models\Project;
use App\Models\ReceptionReport;
use App\Models\SiteReport;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * RH-01 à RH-04, MAT-01 à MAT-06, RPT-01 à RPT-05 du cahier de recette.
 */
class RhMaterielRapportsTest extends RecetteTestCase
{
    /** RH-01, RH-02 : création d'un employé + pointage */
    public function test_rh_01_02_employe_et_pointage(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        $resp = $this->post(route('employees.store'), [
            'first_name' => 'Jean', 'last_name' => 'Rakoto', 'contract_type' => 'cdi',
            'hire_date' => now()->toDateString(),
        ]);
        $resp->assertRedirect();
        $employee = Employee::first();
        $this->assertNotNull($employee);

        // Le pointage n'est proposé/accepté que pour un salarié affecté au chantier.
        $project->employees()->attach($employee->id, ['is_active' => true]);

        $this->post(route('attendances.store'), [
            'project_id' => $project->id, 'employee_id' => $employee->id,
            'work_date' => now()->toDateString(), 'status' => 'present',
        ])->assertRedirect(route('attendances.index'));

        $this->assertSame(1, \App\Models\Attendance::where('employee_id', $employee->id)->count());
    }

    /** RH-01bis : photo employé — upload, redimensionnement en miniature carrée, et remplacement propre */
    public function test_rh_01bis_photo_employe_upload_et_miniature(): void
    {
        Storage::fake('public');
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        // Upload initial : image large (1200x800), doit être réduite à une miniature 300x300.
        $original = UploadedFile::fake()->image('portrait.jpg', 1200, 800);
        $resp = $this->post(route('employees.store'), [
            'first_name' => 'Marie', 'last_name' => 'Rasoa', 'photo' => $original,
        ]);
        $resp->assertRedirect();

        $employee = Employee::first();
        $this->assertNotNull($employee->photo_path);
        $this->assertStringEndsWith('.webp', $employee->photo_path);
        Storage::disk('public')->assertExists($employee->photo_path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($employee->photo_path));
        $this->assertSame(300, $width, 'La photo doit être redimensionnée en miniature 300x300');
        $this->assertSame(300, $height);

        $firstPhotoPath = $employee->photo_path;

        // Remplacement : l'ancienne miniature doit être supprimée, une nouvelle générée.
        $this->patch(route('employees.update', $employee), [
            'first_name' => 'Marie', 'last_name' => 'Rasoa',
            'photo' => UploadedFile::fake()->image('nouvelle.jpg', 600, 600),
        ])->assertRedirect(route('employees.show', $employee));

        $employee->refresh();
        $this->assertNotEquals($firstPhotoPath, $employee->photo_path);
        Storage::disk('public')->assertMissing($firstPhotoPath);
        Storage::disk('public')->assertExists($employee->photo_path);

        // Mise à jour SANS nouvelle photo : la photo actuelle doit être conservée (pas de perte silencieuse).
        $currentPhoto = $employee->photo_path;
        $this->patch(route('employees.update', $employee), [
            'first_name' => 'Marie', 'last_name' => 'Rasoa-Modifiée',
        ])->assertRedirect(route('employees.show', $employee));

        $this->assertSame($currentPhoto, $employee->fresh()->photo_path);
    }

    /** RH-01ter : la liste des pointages affiche côte à côte la photo de référence de l'employé et la photo capturée au pointage */
    public function test_rh_01ter_liste_pointages_compare_photo_employe_et_photo_pointage(): void
    {
        Storage::fake('public');
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        $avecPhoto = Employee::create(['company_id' => $company->id, 'first_name' => 'Marie', 'last_name' => 'Rasoa', 'photo_path' => 'employees/marie.webp']);
        Storage::disk('public')->put('employees/marie.webp', 'contenu-photo-employe');

        $sansPhoto = Employee::create(['company_id' => $company->id, 'first_name' => 'Jean', 'last_name' => 'Rakoto']);

        $attAvecCapture = \App\Models\Attendance::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'employee_id' => $avecPhoto->id,
            'created_by' => auth()->id(), 'work_date' => now()->toDateString(), 'status' => 'present',
            'photo_path' => 'pointages/2026/07/25/capture.webp',
        ]);
        Storage::disk('public')->put('pointages/2026/07/25/capture.webp', 'contenu-photo-pointage');

        \App\Models\Attendance::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'employee_id' => $sansPhoto->id,
            'created_by' => auth()->id(), 'work_date' => now()->toDateString(), 'status' => 'present',
        ]);

        $page = $this->get(route('attendances.index'));
        $page->assertOk();
        $page->assertSee('Photo employé');
        $page->assertSee('Photo pointage');

        // Pointage avec les deux photos : les deux <img> doivent apparaître.
        $page->assertSee('src="' . asset('storage/employees/marie.webp') . '"', false);
        $page->assertSee('src="' . asset('storage/pointages/2026/07/25/capture.webp') . '"', false);

        // Pointage sans aucune des deux photos : repli sur les icônes, pas d'erreur.
        $page->assertSee('bx-user small'); // repli photo employé
        $page->assertSee('bx-camera small'); // repli photo pointage
    }

    /** MAT-01 : équipement loué avec décompte des jours avant restitution */
    public function test_mat_01_equipement_loue(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $equipment = Equipment::create([
            'company_id' => $company->id, 'name' => 'Bétonnière 350L',
            'is_internal' => false, 'status' => 'disponible', 'daily_rental_cost' => 15000,
        ]);

        $this->post(route('projects.equipments.assign', $project), [
            'equipment_id' => $equipment->id, 'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ])->assertSessionHas('success');

        $this->assertSame('affecte', $equipment->fresh()->status);
        $this->assertSame(1, $project->projectAssignments()->count());
    }

    /** MAT-02 : maintenance équipement */
    public function test_mat_02_maintenance(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $equipment = Equipment::create([
            'company_id' => $company->id, 'name' => 'Camion benne',
            'is_internal' => true, 'status' => 'disponible',
        ]);

        $this->post(route('equipments.maintenances.store', $equipment), [
            'type' => 'preventive', 'maintenance_date' => now()->toDateString(), 'cost' => 50000,
        ])->assertSessionHas('success');

        $this->assertSame(1, $equipment->maintenances()->count());
    }

    /** MAT-03, MAT-04 : mouvement de stock entrée puis sortie vers un chantier */
    public function test_mat_03_04_mouvements_stock(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $warehouse = Warehouse::create(['company_id' => $company->id, 'name' => 'Dépôt central']);

        $this->post(route('stock-movements.store'), [
            'warehouse_id' => $warehouse->id, 'item_name' => 'Ciment CPA', 'unit' => 'sac',
            'type' => 'entree', 'quantity' => 100, 'movement_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->post(route('stock-movements.store'), [
            'warehouse_id' => $warehouse->id, 'item_name' => 'Ciment CPA', 'unit' => 'sac',
            'type' => 'sortie', 'quantity' => 30, 'project_id' => $project->id, 'movement_date' => now()->toDateString(),
        ])->assertRedirect();

        $balance = StockMovement::where('warehouse_id', $warehouse->id)
            ->selectRaw('SUM(CASE WHEN type="entree" THEN quantity ELSE -quantity END) as bal')
            ->value('bal');
        $this->assertEquals(70, (float) $balance);
    }

    /** RPT-01, RPT-02 : compte-rendu de chantier créé puis finalisé (verrouillé) */
    public function test_rpt_01_02_compte_rendu_et_finalisation(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company, ['status' => 'en_cours']);

        $resp = $this->post(route('site-reports.store'), [
            'project_id' => $project->id, 'title' => 'Réunion de chantier', 'report_date' => now()->toDateString(),
        ]);
        $resp->assertRedirect();
        $report = SiteReport::first();
        $this->assertNotSame('finalise', $report->status);

        $this->post(route('site-reports.finalize', $report))->assertSessionHas('success');
        $report->refresh();
        $this->assertSame('finalise', $report->status);

        $this->get(route('site-reports.edit', $report))->assertForbidden();
    }

    /** RPT-04, RPT-05 : PV de réception signé -> chantier clôturé, libération RG */
    public function test_rpt_04_05_pv_reception_et_liberation_rg(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company, ['status' => 'termine']);

        $resp = $this->post(route('reception-reports.store'), [
            'project_id' => $project->id, 'reception_date' => now()->toDateString(), 'rg_amount' => 100000,
        ]);
        $resp->assertRedirect();
        $report = ReceptionReport::first();

        $this->post(route('reception-reports.accept', $report))->assertSessionHas('success');
        $report->refresh();
        $this->assertSame('signe', $report->status);
        $this->assertSame('cloture', $project->fresh()->status, 'RPT-04 : le chantier passe clôturé');

        $this->post(route('reception-reports.release-rg', $report), ['rg_release_date' => now()->toDateString()])
            ->assertSessionHas('success');
        $this->assertSame('rg_libere', $report->fresh()->status);
    }

    /** REP-04 : /reports/attendance ne plante plus, affiche les vraies heures et le vrai poste */
    public function test_rep_04_rapport_pointage_sans_erreur(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);
        $jobType = \App\Models\JobType::create(['company_id' => $company->id, 'name' => 'Maçon']);
        $employee = Employee::create([
            'company_id' => $company->id, 'job_type_id' => $jobType->id,
            'first_name' => 'Jean', 'last_name' => 'Rakoto',
        ]);
        \App\Models\Attendance::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'employee_id' => $employee->id,
            'created_by' => auth()->id(), 'work_date' => now()->toDateString(), 'status' => 'present', 'hours_worked' => 8,
        ]);

        $page = $this->get(route('reports.attendance'));
        $page->assertOk();
        $page->assertSee('8.00h', false);
        $page->assertSee('Maçon');
        $page->assertDontSee('Ouvrier');

        $pdf = $this->get(route('reports.attendance', ['export' => 'pdf']));
        $pdf->assertOk();
    }

    /** tasks:notify-overdue : une tâche en retard notifie l'employé assigné (via son compte User) */
    public function test_notify_overdue_tasks_notifie_employe_assigne(): void
    {
        $company = $this->makeCompany();
        $admin = $this->actingAsCompanyUser($company);
        $project = $this->makeProject($company);

        $employee = Employee::create([
            'company_id' => $company->id, 'first_name' => 'Jean', 'last_name' => 'Rakoto', 'email' => 'jean@chantier.mg',
        ]);
        $employeeUser = $this->makeUser($company, 'operateur', ['email' => 'jean@chantier.mg']);

        $overdueTask = \App\Models\Task::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => $admin->id, 'title' => 'Tâche en retard',
            'status' => 'en_cours', 'priority' => 'normale', 'weight' => 1, 'due_date' => now()->subDays(3),
        ]);
        $overdueTask->employees()->attach($employee->id);

        // Tâche non en retard : ne doit déclencher aucune notification
        \App\Models\Task::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => $admin->id, 'title' => 'Tâche à temps',
            'status' => 'en_cours', 'priority' => 'normale', 'weight' => 1, 'due_date' => now()->addDays(3),
        ]);

        // Tâche terminée malgré une échéance passée : ne doit pas notifier non plus
        \App\Models\Task::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => $admin->id, 'title' => 'Tâche terminée en retard',
            'status' => 'termine', 'priority' => 'normale', 'weight' => 1, 'due_date' => now()->subDays(1),
        ]);

        // Tâche en retard sans employé assigné : doit notifier les admins de la société
        $unassignedOverdue = \App\Models\Task::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => $admin->id, 'title' => 'Tâche en retard non assignée',
            'status' => 'a_faire', 'priority' => 'normale', 'weight' => 1, 'due_date' => now()->subDay(),
        ]);

        $this->artisan('tasks:notify-overdue')->assertExitCode(0);

        $this->assertSame(
            1,
            $employeeUser->notifications()->where('type', \App\Notifications\TaskOverdue::class)->count(),
            'L\'employé assigné à la tâche en retard doit recevoir exactement une notification'
        );
        $notifData = $employeeUser->notifications()->first()->data;
        $this->assertSame($overdueTask->id, $notifData['url'] ? (int) basename(parse_url($notifData['url'], PHP_URL_PATH)) : null);
        $this->assertStringContainsString('Tâche en retard', $notifData['message']);

        $this->assertGreaterThanOrEqual(
            1,
            $admin->notifications()->where('type', \App\Notifications\TaskOverdue::class)->count(),
            'Un admin doit être notifié pour la tâche en retard non assignée'
        );
    }

    private function makeProject($company, array $attrs = []): Project
    {
        return Project::create(array_merge([
            'company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier test',
        ], $attrs));
    }
}
