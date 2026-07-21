<?php

namespace Tests\Feature;

use App\Console\Commands\SendNotificationDigests;
use App\Mail\NotificationDigestMail;
use App\Models\NotificationDigestLog;
use App\Models\NotificationEmailSetting;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskOverdue;
use Illuminate\Support\Facades\Mail;

class NotificationDigestTest extends RecetteTestCase
{
    /** Configuration : ajouter puis retirer un email destinataire pour un type de notification */
    public function test_configuration_emails_destinataires(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $this->post(route('settings.notification_emails.store'), [
            'notification_type' => TaskOverdue::class,
            'email'              => 'chef@chantier.mg',
        ])->assertSessionHas('success');

        $setting = NotificationEmailSetting::where('company_id', $company->id)
            ->where('notification_type', TaskOverdue::class)->first();
        $this->assertSame(['chef@chantier.mg'], $setting->emails);

        // Ajouter le même email deux fois ne le duplique pas
        $this->post(route('settings.notification_emails.store'), [
            'notification_type' => TaskOverdue::class,
            'email'              => 'chef@chantier.mg',
        ]);
        $this->assertCount(1, $setting->fresh()->emails);

        $this->delete(route('settings.notification_emails.destroy', $setting), ['email' => 'chef@chantier.mg'])
            ->assertSessionHas('success');
        $this->assertSame([], $setting->fresh()->emails);
    }

    /** Digest groupé : plusieurs tâches en retard le même jour -> un seul email listant tout */
    public function test_digest_groupe_plusieurs_evenements_meme_jour(): void
    {
        Mail::fake();

        $company = $this->makeCompany();
        $admin = $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier digest']);

        NotificationEmailSetting::create([
            'company_id' => $company->id, 'notification_type' => TaskOverdue::class,
            'emails' => ['direction@chantier.mg'],
        ]);

        // 3 tâches en retard, notifiées en base (comme le ferait tasks:notify-overdue)
        foreach (['Tâche A', 'Tâche B', 'Tâche C'] as $title) {
            $task = Task::create([
                'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => $admin->id,
                'title' => $title, 'status' => 'en_cours', 'priority' => 'normale', 'weight' => 1,
                'due_date' => now()->subDays(2),
            ]);
            $admin->notify(new TaskOverdue($task));
        }

        $this->artisan('notifications:send-digests')->assertExitCode(0);

        Mail::assertSent(NotificationDigestMail::class, function ($mail) {
            return $mail->hasTo('direction@chantier.mg')
                && $mail->items->count() === 3;
        });

        $this->assertSame(
            1,
            NotificationDigestLog::where('company_id', $company->id)
                ->where('notification_type', TaskOverdue::class)
                ->where('digest_date', now()->toDateString())
                ->count()
        );
    }

    /** Garde anti-doublon : relancer la commande le même jour ne renvoie pas un second email */
    public function test_pas_plus_dun_email_par_jour(): void
    {
        Mail::fake();

        $company = $this->makeCompany();
        $admin = $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier digest2']);

        NotificationEmailSetting::create([
            'company_id' => $company->id, 'notification_type' => TaskOverdue::class,
            'emails' => ['direction@chantier.mg'],
        ]);

        $task = Task::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => $admin->id,
            'title' => 'Tâche unique', 'status' => 'en_cours', 'priority' => 'normale', 'weight' => 1,
            'due_date' => now()->subDay(),
        ]);
        $admin->notify(new TaskOverdue($task));

        $this->artisan('notifications:send-digests')->assertExitCode(0);
        $this->artisan('notifications:send-digests')->assertExitCode(0);
        $this->artisan('notifications:send-digests')->assertExitCode(0);

        Mail::assertSent(NotificationDigestMail::class, 1);
    }

    /** Aucun email destinataire configuré pour ce type -> aucun envoi */
    public function test_aucun_email_si_pas_configure(): void
    {
        Mail::fake();

        $company = $this->makeCompany();
        $admin = $this->actingAsCompanyUser($company);
        $project = Project::create(['company_id' => $company->id, 'client_id' => $this->makeClient($company)->id, 'name' => 'Chantier digest3']);

        $task = Task::create([
            'company_id' => $company->id, 'project_id' => $project->id, 'created_by' => $admin->id,
            'title' => 'Tâche sans config email', 'status' => 'en_cours', 'priority' => 'normale', 'weight' => 1,
            'due_date' => now()->subDay(),
        ]);
        $admin->notify(new TaskOverdue($task));

        $this->artisan('notifications:send-digests')->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
