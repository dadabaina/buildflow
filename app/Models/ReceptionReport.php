<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceptionReport extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'project_id', 'created_by', 'reference', 'reception_date',
        'client_name', 'reserves', 'rg_amount', 'rg_release_date', 'notes', 'status',
    ];

    protected $casts = [
        'reception_date'  => 'date',
        'rg_release_date' => 'date',
        'rg_amount'       => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReceptionReport $report) {
            if (empty($report->reference)) {
                $year  = now()->year;
                $count = static::withoutGlobalScopes()
                    ->whereYear('created_at', $year)
                    ->where('company_id', $report->company_id)
                    ->count();
                $report->reference = 'PV-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
