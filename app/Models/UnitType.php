<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'symbol', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
