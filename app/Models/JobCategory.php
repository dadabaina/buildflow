<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name'];

    public function jobTypes()
    {
        return $this->hasMany(JobType::class);
    }
}
