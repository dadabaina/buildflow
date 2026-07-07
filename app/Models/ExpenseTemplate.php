<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseTemplate extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'description',
        'output_unit', 'output_quantity', 'is_active',
    ];

    protected $casts = [
        'output_quantity' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Lignes du modèle (matériaux, M.O., matériel, sous-traitance).
     */
    public function items()
    {
        return $this->hasMany(ExpenseTemplateItem::class)->orderBy('sort_order');
    }
}
