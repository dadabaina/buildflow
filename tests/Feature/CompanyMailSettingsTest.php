<?php

namespace Tests\Feature;

use App\Mail\NotificationDigestMail;
use App\Models\CompanyMailSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CompanyMailSettingsTest extends RecetteTestCase
{
    /** Enregistrement de la configuration SMTP de la société */
    public function test_enregistrement_configuration_smtp(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $this->post(route('settings.mail.update'), [
            'is_enabled'   => '1',
            'host'         => 'smtp.gmail.com',
            'port'         => '587',
            'username'     => 'contact@masociete.mg',
            'password'     => 'secret-123',
            'encryption'   => 'tls',
            'from_address' => 'noreply@masociete.mg',
            'from_name'    => 'Ma Société',
        ])->assertRedirect(route('settings.notification_emails.index'));

        $settings = CompanyMailSettings::where('company_id', $company->id)->first();
        $this->assertNotNull($settings);
        $this->assertTrue($settings->is_enabled);
        $this->assertSame('smtp.gmail.com', $settings->host);
        $this->assertSame(587, $settings->port);
        $this->assertSame('tls', $settings->encryption);
        $this->assertSame('noreply@masociete.mg', $settings->from_address);
        // Le mot de passe est déchiffré correctement via le cast Eloquent...
        $this->assertSame('secret-123', $settings->password);
        // ...mais n'est jamais stocké en clair dans la colonne brute.
        $raw = DB::table('company_mail_settings')->where('company_id', $company->id)->value('password');
        $this->assertNotSame('secret-123', $raw);
    }

    /** Laisser le mot de passe vide lors d'une mise à jour conserve l'ancien */
    public function test_mot_de_passe_non_ecrase_si_laisse_vide(): void
    {
        $company = $this->makeCompany();
        $this->actingAsCompanyUser($company);

        $this->post(route('settings.mail.update'), [
            'is_enabled' => '1', 'host' => 'smtp.mailtrap.io', 'port' => '2525', 'password' => 'motdepasse-initial',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        // Nouvelle mise à jour : on change juste le port, mot de passe laissé vide.
        $this->post(route('settings.mail.update'), [
            'is_enabled' => '1', 'host' => 'smtp.mailtrap.io', 'port' => '2526',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $settings = CompanyMailSettings::where('company_id', $company->id)->first();
        $this->assertSame(2526, $settings->port);
        $this->assertSame('motdepasse-initial', $settings->password);
    }

    /** resolveMailerName() : null si désactivé, nom de mailer configuré si activé */
    public function test_resolve_mailer_name(): void
    {
        $company = $this->makeCompany();

        $disabled = CompanyMailSettings::create([
            'company_id' => $company->id, 'is_enabled' => false, 'host' => 'smtp.x.mg',
        ]);
        $this->assertNull($disabled->resolveMailerName());

        $enabled = new CompanyMailSettings([
            'company_id' => $company->id, 'is_enabled' => true, 'host' => 'smtp.x.mg', 'port' => 465, 'encryption' => 'ssl',
        ]);
        $name = $enabled->resolveMailerName();
        $this->assertSame('company_' . $company->id, $name);
        $this->assertSame('smtp.x.mg', config("mail.mailers.{$name}.host"));
        $this->assertSame(465, config("mail.mailers.{$name}.port"));
    }

    /** Bouton "Envoyer un email de test" */
    public function test_envoi_email_de_test(): void
    {
        Mail::fake();
        $company = $this->makeCompany();
        $user = $this->actingAsCompanyUser($company);

        // Sans configuration enregistrée : refus explicite
        $this->post(route('settings.mail.test'))->assertSessionHas('error');

        CompanyMailSettings::create([
            'company_id' => $company->id, 'is_enabled' => false, 'host' => 'smtp.mailtrap.io', 'port' => 2525,
        ]);

        $this->post(route('settings.mail.test'))->assertSessionHas('success');

        Mail::assertSent(NotificationDigestMail::class, fn ($mail) => $mail->hasTo($user->email));
    }
}
