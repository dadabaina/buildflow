<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryRate extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id', 'job_type_id', 'region_id',
        'daily_rate', 'hourly_rate', 'effective_date', 'notes',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Tarif journalier courant pour un métier (+ région optionnelle).
     */
    public static function currentRate(int $companyId, int $jobTypeId, ?int $regionId = null): ?float
    {
        $query = static::where('company_id', $companyId)
            ->where('job_type_id', $jobTypeId)
            ->where('effective_date', '<=', now()->toDateString())
            ->orderByDesc('effective_date');

        if ($regionId) {
            $regional = (clone $query)->where('region_id', $regionId)->first();
            if ($regional) {
                return (float) $regional->daily_rate;
            }
        }

        $general = (clone $query)->whereNull('region_id')->first()
            ?? $query->first();

        return $general ? (float) $general->daily_rate : null;
    }
}
