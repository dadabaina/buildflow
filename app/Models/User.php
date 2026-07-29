<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar_path',
        'job_title',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function managedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_managers');
    }

    public function managesProject(int $projectId): bool
    {
        return $this->managedProjects()->where('projects.id', $projectId)->exists();
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        $initials = collect(explode(' ', $this->name))
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->join('');
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&background=0d6efd&color=fff&size=40';
    }
}
