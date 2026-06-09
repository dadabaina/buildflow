<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Amendment extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'quote_id', 'created_by',
        'reference', 'title', 'description', 'status',
        'subtotal_ht', 'tva_rate', 'tva_amount', 'total_ttc',
        'client_token', 'valid_until', 'notes',
    ];

    protected $casts = [
        'valid_until'  => 'date',
        'subtotal_ht'  => 'decimal:2',
        'tva_rate'     => 'decimal:2',
        'tva_amount'   => 'decimal:2',
        'total_ttc'    => 'decimal:2',
    ];

    /* ── Relations ──────────────────────────────────────────── */

    public function company()   { return $this->belongsTo(Company::class); }
    public function project()   { return $this->belongsTo(Project::class); }
    public function quote()     { return $this->belongsTo(Quote::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function items()     { return $this->hasMany(AmendmentItem::class)->orderBy('sort_order'); }

    /* ── Accessors ──────────────────────────────────────────── */

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'Brouillon',
            'envoye'    => 'Envoyé',
            'accepte'   => 'Accepté',
            'refuse'    => 'Refusé',
            'annule'    => 'Annulé',
            default     => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'badge-soft-secondary',
            'envoye'    => 'badge-soft-info',
            'accepte'   => 'badge-soft-success',
            'refuse'    => 'badge-soft-danger',
            'annule'    => 'badge-soft-danger',
            default     => 'badge-soft-secondary',
        };
    }

    /* ── Methods ────────────────────────────────────────────── */

    public function recalcTotals(): void
    {
        $ht = $this->items()->get()->reduce(function ($carry, $item) {
            return $carry + ($item->is_deduction ? -$item->total_ht : $item->total_ht);
        }, 0);

        $tva = round($ht * $this->tva_rate / 100, 2);
        $this->update([
            'subtotal_ht' => $ht,
            'tva_amount'  => $tva,
            'total_ttc'   => $ht + $tva,
        ]);
    }
}
