<?php

namespace App\Console\Commands;

use App\Mail\NotificationDigestMail;
use App\Models\Company;
use App\Models\NotificationDigestLog;
use App\Models\NotificationEmailSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Mail;

class SendNotificationDigests extends Command
{
    protected $signature = 'notifications:send-digests';
    protected $description = "Envoie, au plus une fois par jour et par type, un email récapitulatif des notifications générées aujourd'hui vers les adresses configurées par société.";

    public function handle()
    {
        $today = now()->toDateString();

        $settings = NotificationEmailSetting::whereNotNull('emails')->get()
            ->filter(fn ($s) => !empty($s->emails));

        $this->info("Analyse de {$settings->count()} configuration(s) de notification par email...");

        foreach ($settings as $setting) {
            // Garde anti-doublon : un seul digest envoyé par (société, type, jour),
            // même si la commande est relancée plusieurs fois le même jour.
            $alreadySent = NotificationDigestLog::where('company_id', $setting->company_id)
                ->where('notification_type', $setting->notification_type)
                ->where('digest_date', $today)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $company = Company::find($setting->company_id);
            if (!$company) {
                continue;
            }

            $userIds = User::where('company_id', $company->id)->pluck('id');

            $notifications = DatabaseNotification::where('type', $setting->notification_type)
                ->where('notifiable_type', User::class)
                ->whereIn('notifiable_id', $userIds)
                ->whereDate('created_at', $today)
                ->orderBy('created_at')
                ->get();

            if ($notifications->isEmpty()) {
                continue;
            }

            // Un même événement (ex: une facture en retard) est enregistré une fois par
            // destinataire in-app (un admin, deux admins...) : on ne garde qu'une occurrence
            // par événement distinct pour le digest, sur la base du message généré.
            $items = $notifications->unique(fn ($n) => $n->data['message'] ?? $n->id)
                ->map(fn ($n) => [
                    'title'   => $n->data['title'] ?? '',
                    'message' => $n->data['message'] ?? '',
                    'url'     => $n->data['url'] ?? url('/'),
                ])
                ->values();

            $label = NotificationEmailSetting::TYPES[$setting->notification_type] ?? $setting->notification_type;
            $mailable = new NotificationDigestMail($company, $label, $items, now());

            // SMTP personnalisé de la société si activé, sinon mailer par défaut (.env).
            $mailerName = $company->mailSettings?->resolveMailerName();
            $mailerName ? Mail::mailer($mailerName)->to($setting->emails)->send($mailable)
                        : Mail::to($setting->emails)->send($mailable);

            NotificationDigestLog::create([
                'company_id'        => $company->id,
                'notification_type' => $setting->notification_type,
                'digest_date'       => $today,
                'items_count'       => $items->count(),
                'sent_at'           => now(),
            ]);

            $this->line("Digest envoyé : {$company->name} / {$label} ({$items->count()} événement(s)) → " . implode(', ', $setting->emails));
        }

        $this->info('Traitement terminé.');
    }
}
