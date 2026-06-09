<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'expense_category_id', 'supplier_id',
        'created_by', 'validated_by', 'description', 'expense_date',
        'quantity', 'unit', 'unit_price', 'payment_mode', 'payment_reference',
        'receipt_path', 'status', 'rejection_reason', 'validated_at', 'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'validated_at' => 'datetime',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'saisie' => 'Saisie',
            'validee' => 'Validée',
            'rejetee' => 'Rejetée',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'validee' => 'bg-success',
            'rejetee' => 'bg-danger',
            default => 'bg-warning text-dark',
        };
    }
}
