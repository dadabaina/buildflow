<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'address', 'city', 'country',
        'logo_path', 'currency', 'tva_rate', 'rg_rate', 'fg_rate',
        'marge_rate', 'aleas_rate', 'quote_prefix', 'invoice_prefix',
        'credit_note_prefix', 'purchase_order_prefix', 'project_prefix',
        'plan', 'plan_expires_at', 'is_active', 'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'plan_expires_at' => 'datetime',
        'settings' => 'array',
        'tva_rate' => 'decimal:2',
        'rg_rate' => 'decimal:2',
        'fg_rate' => 'decimal:2',
        'marge_rate' => 'decimal:2',
        'aleas_rate' => 'decimal:2',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function jobTypes()
    {
        return $this->hasMany(JobType::class);
    }

    public function jobCategories()
    {
        return $this->hasMany(JobCategory::class);
    }

    public function unitTypes()
    {
        return $this->hasMany(UnitType::class);
    }

    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    /**
     * Référence devis suivante (DEV-0001, ...). À appeler dans une transaction :
     * le lockForUpdate empêche deux créations simultanées d'obtenir le même numéro.
     */
    public function nextQuoteReference(): string
    {
        $prefix  = $this->quote_prefix ?? 'DEV';
        $lastNum = $this->quotes()->lockForUpdate()
            ->max(DB::raw("CAST(SUBSTRING(reference, LENGTH('{$prefix}')+2) AS UNSIGNED)")) ?? 0;

        return $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Référence facture suivante (FAC-0001, ...). Mêmes précautions que nextQuoteReference().
     */
    public function nextInvoiceReference(): string
    {
        $prefix  = $this->invoice_prefix ?? 'FAC';
        $lastNum = $this->invoices()->lockForUpdate()
            ->max(DB::raw("CAST(SUBSTRING(reference, LENGTH('{$prefix}')+2) AS UNSIGNED)")) ?? 0;

        return $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }

    public function progressBillings()
    {
        return $this->hasMany(ProgressBilling::class);
    }

    public function salaryRates()
    {
        return $this->hasMany(SalaryRate::class);
    }

    public function dosageModels()
    {
        return $this->hasMany(DosageModel::class);
    }

    public function materialCategories()
    {
        return $this->hasMany(MaterialCategory::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function siteReports()
    {
        return $this->hasMany(SiteReport::class);
    }

    public function receptionReports()
    {
        return $this->hasMany(ReceptionReport::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    public function loginLogs()
    {
        return $this->hasManyThrough(LoginLog::class, User::class);
    }

    /**
     * Calcule le coefficient K (Debboursé Sec → Prix de vente)
     * K = 1 / (1 - (FG% + Marge% + Aléas%))
     */
    public function getCoefficientKAttribute(): float
    {
        $sum = ($this->fg_rate + $this->marge_rate + $this->aleas_rate) / 100;
        if ($sum >= 1) {
            return 1;
        }
        return round(1 / (1 - $sum), 4);
    }
}
