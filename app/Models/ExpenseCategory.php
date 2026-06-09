<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'color', 'icon', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function expenses() { return $this->hasMany(Expense::class); }
}
