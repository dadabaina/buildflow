<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressBilling extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'quote_id', 'invoice_id', 'created_by',
        'reference', 'title', 'situation_number', 'billing_date', 'due_date',
        'status', 'subtotal_ht', 'rg_rate', 'rg_amount',
        'tva_rate', 'tva_amount', 'total_ttc', 'net_to_pay', 'notes',
    ];

    protected $casts = [
        'billing_date'   => 'date',
        'due_date'       => 'date',
        'subtotal_ht'    => 'decimal:2',
        'rg_rate'        => 'decimal:2',
        'rg_amount'      => 'decimal:2',
        'tva_rate'       => 'decimal:2',
        'tva_amount'     => 'decimal:2',
        'total_ttc'      => 'decimal:2',
        'net_to_pay'     => 'decimal:2',
    ];

    /* ── Relations ──────────────────────────────────────────── */

    public function company()   { return $this->belongsTo(Company::class); }
    public function project()   { return $this->belongsTo(Project::class); }
    public function quote()     { return $this->belongsTo(Quote::class); }
    public function invoice()   { return $this->belongsTo(Invoice::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function lines()     { return $this->hasMany(ProgressBillingLine::class)->orderBy('sort_order'); }

    /* ── Accessors ──────────────────────────────────────────── */

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'Brouillon',
            'envoye'    => 'Envoyé',
            'valide'    => 'Validé',
            'facture'   => 'Facturé',
            'annule'    => 'Annulé',
            default     => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'badge-soft-secondary',
            'envoye'    => 'badge-soft-info',
            'valide'    => 'badge-soft-success',
            'facture'   => 'badge-soft-primary',
            'annule'    => 'badge-soft-danger',
            default     => 'badge-soft-secondary',
        };
    }

    /* ── Methods ────────────────────────────────────────────── */

    public function recalcTotals(): void
    {
        $ht  = $this->lines()->sum('current_amount');
        $rg  = round($ht * $this->rg_rate / 100, 2);
        $tva = round($ht * $this->tva_rate / 100, 2);
        $ttc = $ht + $tva;
        $net = $ttc - $rg;

        $this->update([
            'subtotal_ht' => $ht,
            'rg_amount'   => $rg,
            'tva_amount'  => $tva,
            'total_ttc'   => $ttc,
            'net_to_pay'  => $net,
        ]);
    }
}
