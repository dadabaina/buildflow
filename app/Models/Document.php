<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'project_id', 'uploaded_by',
        'category', 'original_name', 'stored_name', 'path',
        'mime_type', 'file_size', 'version', 'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /* ── Categories ─────────────────────────────────────────── */

    public static array $categories = [
        'plan'           => 'Plan / Dessin',
        'contrat'        => 'Contrat',
        'devis'          => 'Devis',
        'facture'        => 'Facture',
        'photo'          => 'Photo',
        'rapport'        => 'Rapport',
        'administratif'  => 'Administratif',
        'autre'          => 'Autre',
    ];

    /* ── Relations ──────────────────────────────────────────── */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* ── Accessors ──────────────────────────────────────────── */

    public function getCategoryLibelleAttribute(): string
    {
        return static::$categories[$this->category] ?? $this->category;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' Mo';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' Ko';
        return $bytes . ' o';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
