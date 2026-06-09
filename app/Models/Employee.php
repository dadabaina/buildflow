<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'job_type_id', 'region_id', 'supplier_id',
        'matricule', 'first_name', 'last_name', 'email', 'phone',
        'address', 'birth_date', 'hire_date', 'end_date',
        'contract_type', 'daily_rate', 'monthly_salary', 'rib', 'notes', 'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'daily_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'employee_job_type');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_employees')
            ->withPivot(['role', 'start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getContractTypeLibelleAttribute(): string
    {
        return match ($this->contract_type) {
            'cdi' => 'CDI',
            'cdd' => 'CDD',
            'interim' => 'Intérim',
            'sous_traitant' => 'Sous-traitant',
            'journalier' => 'Journalier',
            default => $this->contract_type,
        };
    }
}
