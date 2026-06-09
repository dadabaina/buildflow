<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteReportItem extends Model
{
    protected $fillable = [
        'site_report_id', 'description', 'responsible', 'due_date', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function siteReport()
    {
        return $this->belongsTo(SiteReport::class);
    }
}
