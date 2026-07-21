<?php

namespace App\Models;

use App\Notifications\DocumentUploaded;
use App\Notifications\InvoiceOverdue;
use App\Notifications\PaymentReceived;
use App\Notifications\QuoteAccepted;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskOverdue;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class NotificationEmailSetting extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'notification_type', 'emails'];

    protected $casts = [
        'emails' => 'array',
    ];

    /**
     * Catalogue des types de notification pouvant être envoyés par email (digest quotidien).
     * Clé = classe de notification, valeur = libellé affiché dans les paramètres.
     */
    public const TYPES = [
        TaskAssigned::class    => 'Tâche assignée',
        TaskOverdue::class     => 'Tâche en retard',
        InvoiceOverdue::class  => 'Facture en retard',
        PaymentReceived::class => 'Paiement reçu',
        QuoteAccepted::class   => 'Devis accepté par le client',
        DocumentUploaded::class => 'Document ajouté',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
