<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceOverdue extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Facture en retard',
            'message' => 'La facture ' . $this->invoice->reference . ' est en retard de paiement.',
            'url'     => route('invoices.show', $this->invoice),
            'icon'    => 'bi-exclamation-circle',
            'color'   => 'danger',
        ];
    }
}
