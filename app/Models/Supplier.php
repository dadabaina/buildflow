<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'contact_name', 'email', 'phone',
        'address', 'city', 'nif', 'type', 'notes', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }
    public function employees() { return $this->hasMany(Employee::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}
