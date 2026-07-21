<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class NotificationDigestLog extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'notification_type', 'digest_date', 'items_count', 'sent_at'];

    protected $casts = [
        'digest_date' => 'date',
        'sent_at'     => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
