<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, BelongsToCompany;

    /** Pause déjeuner déduite par défaut quand aucune valeur n'est saisie. */
    const DEFAULT_BREAK_HOURS = 1.0;

    protected $fillable = [
        'company_id', 'project_id', 'employee_id', 'created_by',
        'checked_in_by', 'checked_out_by',
        'work_date', 'photo_path', 'photo_path_in', 'photo_path_out',
        'check_in', 'check_out', 'break_hours',
        'hours_worked', 'days_worked', 'status',
        'latitude', 'longitude', 'notes',
    ];

    protected $casts = [
        'work_date'    => 'date',
        'break_hours'  => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'days_worked'  => 'decimal:2',
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
    ];

    public function company()     { return $this->belongsTo(Company::class); }
    public function project()     { return $this->belongsTo(Project::class); }
    public function employee()    { return $this->belongsTo(Employee::class); }
    public function createdBy()   { return $this->belongsTo(User::class, 'created_by'); }
    public function checkedInBy() { return $this->belongsTo(User::class, 'checked_in_by'); }
    public function checkedOutBy(){ return $this->belongsTo(User::class, 'checked_out_by'); }

    public function getStatusLibelleAttribute(): string
    {
        return match ($this->status) {
            'present'              => 'Présent',
            'absent_justifie'      => 'Absent justifié',
            'absent_non_justifie'  => 'Absent non justifié',
            default                => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'present'              => 'bg-success',
            'absent_justifie'      => 'bg-warning text-dark',
            'absent_non_justifie'  => 'bg-danger',
            default                => 'bg-secondary',
        };
    }

    /**
     * Calcule heures/jours travaillés à partir d'une entrée et d'une sortie.
     * Si aucune pause n'est renseignée, une pause déjeuner par défaut
     * (self::DEFAULT_BREAK_HOURS) est déduite.
     */
    public static function computeHours(?string $checkIn, ?string $checkOut, $breakHours = null): array
    {
        if (!$checkIn || !$checkOut) {
            return ['hours_worked' => null, 'days_worked' => null];
        }

        $breakHours = $breakHours ?? self::DEFAULT_BREAK_HOURS;

        [$h1, $m1] = explode(':', $checkIn);
        [$h2, $m2] = explode(':', $checkOut);
        $minutes = ($h2 * 60 + $m2) - ($h1 * 60 + $m1) - ((float) $breakHours * 60);

        if ($minutes <= 0) {
            return ['hours_worked' => null, 'days_worked' => null];
        }

        return [
            'hours_worked' => round($minutes / 60, 2),
            'days_worked'  => round($minutes / 60 / 8, 2),
        ];
    }
}
