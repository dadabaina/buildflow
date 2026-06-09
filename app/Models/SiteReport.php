<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteReport extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'project_id', 'created_by', 'reference', 'title',
        'report_date', 'location', 'participants', 'weather', 'content',
        'next_meeting_date', 'status',
    ];

    protected $casts = [
        'report_date'       => 'date',
        'next_meeting_date' => 'date',
        'participants'      => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (SiteReport $report) {
            if (empty($report->reference)) {
                $year  = now()->year;
                $count = static::withoutGlobalScopes()
                    ->whereYear('created_at', $year)
                    ->where('company_id', $report->company_id)
                    ->count();
                $report->reference = 'CR-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
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

    public function items()
    {
        return $this->hasMany(SiteReportItem::class);
    }
}
