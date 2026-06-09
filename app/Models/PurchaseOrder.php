<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'supplier_id', 'created_by',
        'reference', 'order_date', 'delivery_date', 'status',
        'total_ht', 'tva_rate', 'total_ttc',
        'delivery_conditions', 'notes',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'delivery_date' => 'date',
        'total_ht'      => 'decimal:2',
        'tva_rate'      => 'decimal:2',
        'total_ttc'     => 'decimal:2',
    ];

    public static array $statusTransitions = [
        'brouillon'           => ['valide', 'annule'],
        'valide'              => ['partiellement_livre', 'livre', 'annule'],
        'partiellement_livre' => ['livre', 'annule'],
        'livre'               => [],
        'annule'              => [],
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            if (empty($po->reference)) {
                $year = now()->format('Y');
                $seq  = static::withoutGlobalScopes()
                    ->whereYear('created_at', $year)
                    ->count() + 1;
                $po->reference = sprintf('BC-%s-%03d', $year, $seq);
            }
        });
    }

    /* ── Relations ─────────────────────────────────────────── */

    public function company()    { return $this->belongsTo(Company::class); }
    public function project()    { return $this->belongsTo(Project::class); }
    public function supplier()   { return $this->belongsTo(Supplier::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
    public function items()      { return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort_order'); }

    /* ── Helpers ────────────────────────────────────────────── */

    public function recalcTotals(): void
    {
        $ht = $this->items()->sum('total');
        $this->update([
            'total_ht'  => $ht,
            'total_ttc' => round($ht * (1 + $this->tva_rate / 100), 2),
        ]);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::$statusTransitions[$this->status] ?? []);
    }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'brouillon'           => 'Brouillon',
            'valide'              => 'Validé',
            'partiellement_livre' => 'Partiellement livré',
            'livre'               => 'Livré',
            'annule'              => 'Annulé',
            default               => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'brouillon'           => 'bg-secondary',
            'valide'              => 'bg-success',
            'partiellement_livre' => 'bg-warning text-dark',
            'livre'               => 'bg-info',
            'annule'              => 'bg-danger',
            default               => 'bg-secondary',
        };
    }
}
