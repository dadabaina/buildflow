<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment, public ?Invoice $invoice = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $invoiceId = $this->invoice?->id ?? $this->payment->invoices()->first()?->id;

        return [
            'title'   => 'Paiement reçu',
            'message' => 'Paiement de ' . number_format($this->payment->amount, 0, ',', ' ') . ' Ar enregistré.',
            'url'     => $invoiceId ? route('invoices.show', $invoiceId) : route('payments.index'),
            'icon'    => 'bi-cash-stack',
            'color'   => 'success',
        ];
    }
}
