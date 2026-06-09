<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'warehouse_id', 'material_id', 'project_id', 'created_by',
        'item_name', 'unit', 'type', 'quantity', 'unit_cost',
        'reference', 'notes', 'movement_date',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'quantity'      => 'decimal:3',
        'unit_cost'     => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalAttribute(): float
    {
        return (float) ($this->quantity * $this->unit_cost);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'entree'     => 'Entrée',
            'sortie'     => 'Sortie',
            'transfert'  => 'Transfert',
            'ajustement' => 'Ajustement',
            default      => $this->type,
        };
    }

    public function typeColor(): string
    {
        return match ($this->type) {
            'entree'     => 'success',
            'sortie'     => 'danger',
            'transfert'  => 'info',
            'ajustement' => 'warning',
            default      => 'secondary',
        };
    }
}
