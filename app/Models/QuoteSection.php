<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteSection extends Model
{
    use HasFactory;

    protected $fillable = ['quote_id', 'title', 'sort_order'];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function getSectionTotalAttribute(): float
    {
        return (float) $this->items()->sum('total_ht');
    }
}
