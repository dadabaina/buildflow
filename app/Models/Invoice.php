<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'client_id', 'quote_id', 'created_by',
        'reference', 'title', 'type', 'invoice_date', 'due_date',
        'tva_rate', 'rg_rate', 'subtotal_ht', 'tva_amount', 'total_ttc',
        'rg_amount', 'net_to_pay', 'amount_paid', 'amount_remaining',
        'status', 'credit_note_for', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'tva_rate' => 'decimal:2',
        'rg_rate' => 'decimal:2',
        'subtotal_ht' => 'decimal:2',
        'tva_amount' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'rg_amount' => 'decimal:2',
        'net_to_pay' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_remaining' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_allocations')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatePaymentStatus(): void
    {
        $paid = $this->payments()->sum('payment_allocations.amount');
        $remaining = max(0, $this->net_to_pay - $paid);

        $status = match (true) {
            $paid <= 0 => $this->status === 'brouillon' ? 'brouillon' : 'envoye',
            $remaining <= 0 => 'soldee',
            default => 'partiellement_payee',
        };

        $this->update([
            'amount_paid' => $paid,
            'amount_remaining' => $remaining,
            'status' => $status,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['soldee', 'annulee', 'brouillon']);
    }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'Brouillon',
            'envoye' => 'Envoyée',
            'partiellement_payee' => 'Partiellement payée',
            'soldee' => 'Soldée',
            'en_retard' => 'En retard',
            'annulee' => 'Annulée',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'brouillon' => 'bg-secondary',
            'envoye' => 'bg-info text-dark',
            'partiellement_payee' => 'bg-warning text-dark',
            'soldee' => 'bg-success',
            'en_retard' => 'bg-danger',
            'annulee' => 'bg-dark',
            default => 'bg-secondary',
        };
    }
}
