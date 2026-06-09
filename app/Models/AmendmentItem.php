<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmendmentItem extends Model
{
    protected $fillable = [
        'amendment_id', 'description', 'quantity',
        'unit', 'unit_price', 'is_deduction', 'sort_order',
    ];

    protected $casts = [
        'quantity'     => 'decimal:3',
        'unit_price'   => 'decimal:2',
        'total_ht'     => 'decimal:2',
        'is_deduction' => 'boolean',
    ];

    public function amendment()
    {
        return $this->belongsTo(Amendment::class);
    }
}
