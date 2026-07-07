<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'quote_item_id', 'created_by',
        'title', 'description', 'status', 'priority', 'weight',
        'due_date', 'checklist', 'sort_order',
    ];

    protected $casts = [
        'due_date'  => 'date',
        'checklist' => 'array',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function project()   { return $this->belongsTo(Project::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function quoteItem() { return $this->belongsTo(QuoteItem::class); }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'task_employees');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->where('status', 'validee')->sum('total_amount');
    }

    /**
     * Budget prévu de la tâche : déboursé sec (DBE) de la ligne de devis d'origine,
     * ou null si la tâche ne vient pas d'un devis ou si la ligne n'a pas de sous-détail.
     */
    public function getPlannedBudgetAttribute(): ?float
    {
        $dbe = $this->quoteItem?->dbe_total;

        return $dbe > 0 ? (float) $dbe : null;
    }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'a_faire'  => 'À faire',
            'en_cours' => 'En cours',
            'en_pause' => 'En pause',
            'termine'  => 'Terminée',
            'annule'   => 'Annulée',
            default    => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'a_faire'  => 'bg-secondary',
            'en_cours' => 'bg-primary',
            'en_pause' => 'bg-warning text-dark',
            'termine'  => 'bg-success',
            'annule'   => 'bg-danger',
            default    => 'bg-secondary',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'basse'   => 'bg-light text-dark border',
            'normale' => 'bg-info text-dark',
            'haute'   => 'bg-warning text-dark',
            'urgente' => 'bg-danger',
            default   => 'bg-secondary',
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['termine', 'annule']);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->status === 'termine') return 100;
        if ($this->status === 'annule') return 0;

        if (is_array($this->checklist) && count($this->checklist) > 0) {
            $total = count($this->checklist);
            $done = collect($this->checklist)->where('done', true)->count();
            return (int) (($done / $total) * 100);
        }

        return in_array($this->status, ['en_cours', 'en_pause']) ? 50 : 0;
    }
}
