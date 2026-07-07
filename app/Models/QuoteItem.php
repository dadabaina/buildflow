<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id', 'quote_section_id', 'dosage_model_id',
        'description', 'detail', 'unit', 'quantity', 'unit_price', 'discount', 'total_ht', 'sort_order',
        'dbe_materials', 'dbe_labor', 'dbe_equipment', 'dbe_subcontract', 'dbe_total',
        'fg_rate', 'margin_rate', 'alea_rate', 'price_override',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'dbe_materials' => 'decimal:2',
        'dbe_labor' => 'decimal:2',
        'dbe_equipment' => 'decimal:2',
        'dbe_subcontract' => 'decimal:2',
        'dbe_total' => 'decimal:2',
        'fg_rate' => 'decimal:2',
        'margin_rate' => 'decimal:2',
        'alea_rate' => 'decimal:2',
        'price_override' => 'boolean',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function section()
    {
        return $this->belongsTo(QuoteSection::class, 'quote_section_id');
    }

    public function dosageModel()
    {
        return $this->belongsTo(DosageModel::class);
    }

    public function task()
    {
        return $this->hasOne(Task::class);
    }

    /**
     * Coefficient multiplicateur global (FG + marge + aléas).
     * K = (1 + fg%) × (1 + margin%) × (1 + alea%)
     */
    public function getKCoefficientAttribute(): float
    {
        return (1 + (float) $this->fg_rate / 100)
             * (1 + (float) $this->margin_rate / 100)
             * (1 + (float) $this->alea_rate / 100);
    }

    public function calculateTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discount = $subtotal * ($this->discount / 100);
        $this->total_ht = round($subtotal - $discount, 2);
        $this->save();
    }
}
