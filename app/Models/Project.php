<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Project extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id', 'client_id', 'region_id', 'reference', 'name',
        'description', 'address', 'latitude', 'longitude',
        'start_date', 'planned_end_date', 'actual_end_date',
        'status', 'budget_total', 'contract_amount', 'tva_rate', 'rg_rate', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_end_date' => 'date',
        'budget_total' => 'decimal:2',
        'contract_amount' => 'decimal:2',
        'tva_rate' => 'decimal:2',
        'rg_rate' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public static array $statusTransitions = [
        'prospection' => ['devis_en_cours', 'annule'],
        'devis_en_cours' => ['devis_envoye', 'prospection', 'annule'],
        'devis_envoye' => ['en_cours', 'devis_en_cours', 'annule'],
        'en_cours' => ['en_pause', 'termine', 'annule'],
        'en_pause' => ['en_cours', 'annule'],
        'termine' => ['cloture'],
        'cloture' => [],
        'annule' => [],
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->reference)) {
                $year = now()->format('Y');
                $seq = static::withoutGlobalScopes()
                    ->whereYear('created_at', $year)
                    ->count() + 1;
                $project->reference = sprintf('BF-%s-%03d', $year, $seq);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'project_employees')
            ->withPivot(['role', 'start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function amendments()
    {
        return $this->hasMany(Amendment::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function materialThresholds()
    {
        return $this->hasMany(ProjectMaterialThreshold::class);
    }

    public function projectAssignments()
    {
        return $this->hasMany(ProjectEquipment::class);
    }

    public function projectLogs()
    {
        return $this->hasMany(ProjectLog::class)->latest();
    }

    /**
     * Retourne les matériaux sous le seuil d'alerte pour ce chantier.
     */
    public function getLowStockMaterials()
    {
        $warehouseIds = $this->warehouses->pluck('id')->toArray();
        
        $stockLevels = \App\Models\StockMovement::where('company_id', $this->company_id)
            ->where(function($q) use ($warehouseIds) {
                $q->where('project_id', $this->id)
                  ->orWhereIn('warehouse_id', $warehouseIds);
            })
            ->selectRaw('material_id, SUM(CASE WHEN type="entree" THEN quantity WHEN type="sortie" THEN -quantity ELSE 0 END) as balance')
            ->groupBy('material_id')
            ->get()
            ->keyBy('material_id');

        $alerts = [];
        $thresholds = $this->materialThresholds()->with('material')->get();

        foreach ($thresholds as $threshold) {
            $currentStock = $stockLevels[$threshold->material_id]->balance ?? 0;
            if ($currentStock <= $threshold->min_threshold) {
                $alerts[] = [
                    'material' => $threshold->material,
                    'current'  => $currentStock,
                    'min'      => $threshold->min_threshold,
                ];
            }
        }

        return collect($alerts);
    }

    public function requirements()
    {
        return $this->hasMany(ProjectRequirement::class);
    }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'prospection' => 'Prospection',
            'devis_en_cours' => 'Devis en cours',
            'devis_envoye' => 'Devis envoyé',
            'en_cours' => 'En cours',
            'en_pause' => 'En pause',
            'termine' => 'Terminé',
            'cloture' => 'Clôturé',
            'annule' => 'Annulé',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'en_cours' => 'bg-success',
            'termine' => 'bg-primary',
            'cloture' => 'bg-secondary',
            'annule' => 'bg-danger',
            'en_pause' => 'bg-warning text-dark',
            default => 'bg-info text-dark',
        };
    }

    public function getProgressPercentAttribute(): int
    {
        $tasks = $this->tasks()->where('status', '!=', 'annule')->get();
        
        if ($tasks->isEmpty()) {
            return 0;
        }

        $totalWeight = $tasks->sum('weight');
        
        if ($totalWeight === 0) {
            return 0;
        }

        $weightedProgress = $tasks->sum(function ($task) {
            return $task->progress_percent * $task->weight;
        });

        return (int) round($weightedProgress / $totalWeight);
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()
            ->where('status', 'validee')
            ->sum('total_amount');
    }

    public function getTotalInvoicedAttribute(): float
    {
        return (float) $this->invoices()
            ->whereNotIn('status', ['brouillon', 'annulee'])
            ->sum('total_ttc');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getTotalAmendmentsAttribute(): float
    {
        return (float) $this->amendments()
            ->where('status', 'accepte')
            ->sum('total_ttc');
    }

    public function getTotalMarketAmountAttribute(): float
    {
        return (float) $this->contract_amount + $this->total_amendments;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, static::$statusTransitions[$this->status] ?? []);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cloture', 'annule']);
    }

    public function getBudgetAttribute()
    {
        return $this->budget_total;
    }

    public function setBudgetAttribute($value)
    {
        $this->attributes['budget_total'] = $value;
    }
}
