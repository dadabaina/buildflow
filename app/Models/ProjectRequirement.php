<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRequirement extends Model
{
    protected $fillable = [
        'project_id', 'job_type_id', 'needed_quantity', 
        'estimated_hours', 'start_date', 'end_date', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'needed_quantity' => 'integer',
        'estimated_hours' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }
}
