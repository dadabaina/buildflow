<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'equipments';

    protected $fillable = [
        'company_id', 'name', 'reference', 'category', 'brand', 'model',
        'serial_number', 'acquisition_date', 'acquisition_cost',
        'is_internal', 'supplier_id',
        'daily_rental_cost', 'status', 'notes',
    ];

    protected $casts = [
        'acquisition_date'  => 'date',
        'acquisition_cost'  => 'decimal:2',
        'daily_rental_cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function maintenances()
    {
        return $this->hasMany(EquipmentMaintenance::class)->latest('maintenance_date');
    }

    public function projectAssignments()
    {
        return $this->hasMany(ProjectEquipment::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'disponible'  => 'Disponible',
            'affecte'     => 'Affecté',
            'maintenance' => 'Maintenance',
            'hors_service' => 'Hors service',
            default       => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'disponible'  => 'success',
            'affecte'     => 'primary',
            'maintenance' => 'warning',
            'hors_service' => 'secondary',
            default       => 'secondary',
        };
    }
}
