<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_template_id', 'material_id', 'job_type_id', 'item_type',
        'description', 'unit', 'quantity_per_unit', 'waste_rate', 'unit_price',
        'expense_category_id', 'sort_order',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:4',
        'waste_rate'        => 'decimal:2',
        'unit_price'        => 'decimal:2',
    ];

    public function expenseTemplate()
    {
        return $this->belongsTo(ExpenseTemplate::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * Quantité finale en tenant compte des pertes.
     */
    public function effectiveQuantityPerUnit(): float
    {
        return (float) $this->quantity_per_unit * (1 + (float) $this->waste_rate / 100);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->material?->name ?? $this->description;
    }

    public function getDisplayUnitAttribute(): string
    {
        return $this->unit ?: ($this->material?->unit ?? '');
    }
}
