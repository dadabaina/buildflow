<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressBillingLine extends Model
{
    protected $fillable = [
        'progress_billing_id', 'quote_item_id', 'description',
        'quote_quantity', 'unit_price', 'unit',
        'previous_pct', 'current_pct', 'sort_order',
    ];

    protected $casts = [
        'quote_quantity'   => 'decimal:3',
        'unit_price'       => 'decimal:2',
        'previous_pct'     => 'decimal:2',
        'current_pct'      => 'decimal:2',
        'cumulative_pct'   => 'decimal:2',
        'current_amount'   => 'decimal:2',
    ];

    public function progressBilling()
    {
        return $this->belongsTo(ProgressBilling::class);
    }

    public function quoteItem()
    {
        return $this->belongsTo(QuoteItem::class);
    }
}
