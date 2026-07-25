<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'employee_id', 'created_by',
        'work_date', 'photo_path', 'check_in', 'check_out', 'break_hours',
        'hours_worked', 'days_worked', 'status',
        'latitude', 'longitude', 'notes',
    ];

    protected $casts = [
        'work_date'    => 'date',
        'break_hours'  => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'days_worked'  => 'decimal:2',
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function project()   { return $this->belongsTo(Project::class); }
    public function employee()  { return $this->belongsTo(Employee::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'present'              => 'Présent',
            'absent_justifie'      => 'Absent justifié',
            'absent_non_justifie'  => 'Absent non justifié',
            default                => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'present'              => 'bg-success',
            'absent_justifie'      => 'bg-warning text-dark',
            'absent_non_justifie'  => 'bg-danger',
            default                => 'bg-secondary',
        };
    }
}
