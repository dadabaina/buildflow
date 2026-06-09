<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosageModel extends Model
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
        return $this->hasMany(DosageItem::class)->orderBy('sort_order');
    }

    /**
     * Lignes matériaux uniquement.
     */
    public function materialItems()
    {
        return $this->hasMany(DosageItem::class)->where('item_type', 'material')->orderBy('sort_order');
    }

    /**
     * Lignes main d'œuvre uniquement.
     */
    public function laborItems()
    {
        return $this->hasMany(DosageItem::class)->where('item_type', 'labor')->orderBy('sort_order');
    }

    /**
     * Lignes de devis qui utilisent ce modèle.
     */
    public function quoteItems()
    {
        return $this->hasMany(QuoteItem::class);
    }
}
