<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'code', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function projects() { return $this->hasMany(Project::class); }
    public function clients() { return $this->hasMany(Client::class); }
    public function employees() { return $this->hasMany(Employee::class); }
}
