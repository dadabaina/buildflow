<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectEquipment extends Model
{
    protected $table = 'project_equipments';

    protected $fillable = [
        'project_id', 'equipment_id', 'company_id',
        'start_date', 'end_date', 'daily_cost', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'daily_cost' => 'decimal:2',
    ];

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
