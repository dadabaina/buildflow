<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'material_category_id', 'name', 'description',
        'unit', 'reference', 'stock_quantity', 'min_stock_level', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'stock_quantity' => 'decimal:3',
        'min_stock_level'=> 'decimal:3',
    ];

    public function isLowStock(): bool
    {
        return $this->min_stock_level > 0 && $this->stock_quantity <= $this->min_stock_level;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function prices()
    {
        return $this->hasMany(MaterialPrice::class)->orderByDesc('effective_date');
    }

    public function dosageItems()
    {
        return $this->hasMany(DosageItem::class);
    }

    /**
     * Prix unitaire courant (le plus récent, optionnellement par région).
     */
    public function currentPrice(?int $regionId = null): ?float
    {
        $query = $this->prices()->where('effective_date', '<=', now()->toDateString());

        if ($regionId) {
            // Prix spécifique à la région en priorité, puis prix général
            $regional = (clone $query)->where('region_id', $regionId)->first();
            if ($regional) {
                return (float) $regional->unit_price;
            }
        }

        $general = $query->whereNull('region_id')->first()
            ?? $query->first();

        return $general ? (float) $general->unit_price : null;
    }
}
