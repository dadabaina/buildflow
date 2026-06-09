<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dosage_model_id', 'material_id', 'job_type_id', 'item_type',
        'description', 'unit', 'quantity_per_unit', 'waste_rate', 'unit_price', 'sort_order',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:4',
        'waste_rate'        => 'decimal:2',
        'unit_price'        => 'decimal:2',
    ];

    public function dosageModel()
    {
        return $this->belongsTo(DosageModel::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    /**
     * Quantité finale en tenant compte des pertes.
     */
    public function effectiveQuantityPerUnit(): float
    {
        return (float) $this->quantity_per_unit * (1 + (float) $this->waste_rate / 100);
    }

    /**
     * Libellé affiché (nom matériau ou description libre).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->material?->name ?? $this->description;
    }

    /**
     * Unité affichée (de la ligne ou du matériau).
     */
    public function getDisplayUnitAttribute(): string
    {
        return $this->unit ?: ($this->material?->unit ?? '');
    }
}
