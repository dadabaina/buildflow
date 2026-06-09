<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProjectLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'project_id',
        'user_id',
        'action',
        'description',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper pour logger une action rapidement.
     */
    public static function log($projectId, $action, $description, $metadata = null)
    {
        return self::create([
            'company_id'  => currentCompany()->id ?? Auth::user()->company_id,
            'project_id'  => $projectId,
            'user_id'     => \Illuminate\Support\Facades\Auth::id(),
            'action'      => $action,
            'description' => $description,
            'metadata'    => $metadata,
        ]);
    }
}
