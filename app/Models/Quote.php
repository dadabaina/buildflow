<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'client_id', 'created_by',
        'reference', 'title', 'quote_date', 'valid_until',
        'tva_rate', 'discount_global', 'discount_type',
        'subtotal_ht', 'discount_amount', 'taxable_ht', 'tva_amount', 'total_ttc',
        'status', 'version', 'parent_quote_id',
        'client_token', 'client_responded_at', 'client_response_note',
        'notes', 'terms',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'client_responded_at' => 'datetime',
        'tva_rate' => 'decimal:2',
        'discount_global' => 'decimal:2',
        'subtotal_ht' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_ht' => 'decimal:2',
        'tva_amount' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections()
    {
        return $this->hasMany(QuoteSection::class)->orderBy('sort_order');
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function generateClientToken(): void
    {
        $this->update(['client_token' => Str::random(64)]);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total_ht');
        $taxable = $subtotal;

        if ($this->discount_type === 'percent') {
            $discountAmt = round($subtotal * $this->discount_global / 100, 2);
        } else {
            $discountAmt = $this->discount_global;
        }

        $taxable = $subtotal - $discountAmt;
        $tva = round($taxable * $this->tva_rate / 100, 2);
        $ttc = $taxable + $tva;

        $this->update([
            'subtotal_ht' => $subtotal,
            'discount_amount' => $discountAmt,
            'taxable_ht' => $taxable,
            'tva_amount' => $tva,
            'total_ttc' => $ttc,
        ]);
    }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'Brouillon',
            'envoye' => 'Envoyé',
            'accepte' => 'Accepté',
            'refuse' => 'Refusé',
            'expire' => 'Expiré',
            'annule' => 'Annulé',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'bg-secondary',
            'envoye' => 'bg-info text-dark',
            'accepte' => 'bg-success',
            'refuse' => 'bg-danger',
            'expire' => 'bg-warning text-dark',
            'annule' => 'bg-dark',
            default => 'bg-secondary',
        };
    }
}
