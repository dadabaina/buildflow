<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProjectMaterialThreshold extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'project_id', 'material_id', 'min_threshold'];

    protected $casts = [
        'min_threshold' => 'decimal:3',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
