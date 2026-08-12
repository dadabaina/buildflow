<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPayment extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'employee_id', 'created_by',
        'reference', 'payment_date', 'period_start', 'period_end',
        'amount', 'payment_mode', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'salary_payment_allocations')
            ->withPivot('amount')
            ->withTimestamps();
    }
}
