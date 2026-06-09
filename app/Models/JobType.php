<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'job_category_id', 'name', 'metiers', 'code', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function category() { return $this->belongsTo(JobCategory::class, 'job_category_id'); }
    public function employees() { return $this->belongsToMany(Employee::class, 'employee_job_type'); }
    public function primaryEmployees() { return $this->hasMany(Employee::class); }
}
