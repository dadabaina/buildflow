<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Collection $items Chaque item : ['title' => string, 'message' => string, 'url' => string]
     */
    public function __construct(
        public Company $company,
        public string $typeLabel,
        public Collection $items,
        public Carbon $digestDate,
    ) {}

    public function envelope(): Envelope
    {
        $mailSettings = $this->company->mailSettings;
        $from = ($mailSettings?->is_enabled && $mailSettings->from_address)
            ? new Address($mailSettings->from_address, $mailSettings->from_name ?: $this->company->name)
            : null;

        return new Envelope(
            from: $from,
            subject: sprintf(
                '[%s] %s — %d %s',
                $this->company->name,
                $this->typeLabel,
                $this->items->count(),
                $this->items->count() > 1 ? 'notifications' : 'notification'
            ),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.notification-digest');
    }
}
