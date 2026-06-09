<?php

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuoteAccepted extends Notification
{
    use Queueable;

    public function __construct(public Quote $quote) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Devis accepté par le client',
            'message' => 'Le client ' . ($this->quote->client?->name ?? '—')
                . ' a accepté le devis ' . $this->quote->reference . '.',
            'url'     => route('quotes.show', $this->quote),
            'icon'    => 'bi-check-circle-fill',
            'color'   => 'success',
        ];
    }
}
