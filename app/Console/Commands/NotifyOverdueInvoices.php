<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceOverdue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyOverdueInvoices extends Command
{
    protected $signature = 'invoices:notify-overdue';
    protected $description = 'Identifie les factures en retard et notifie les administrateurs.';

    public function handle()
    {
        $overdueInvoices = Invoice::where('status', '!=', 'soldee')
            ->where('status', '!=', 'annulee')
            ->where('status', '!=', 'brouillon')
            ->where('due_date', '<', now())
            ->get();

        $this->info("Analyse de " . $overdueInvoices->count() . " facture(s) potentiellement en retard...");

        foreach ($overdueInvoices as $invoice) {
            // Update status if not already 'en_retard'
            if ($invoice->status !== 'en_retard') {
                $invoice->update(['status' => 'en_retard']);
            }

            // Notify company admins
            $admins = User::role('admin')
                ->where('company_id', $invoice->company_id)
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new InvoiceOverdue($invoice));
            }
            
            $this->line("Notification envoyée pour la facture : {$invoice->reference}");
        }

        $this->info("Traitement terminé.");
    }
}
