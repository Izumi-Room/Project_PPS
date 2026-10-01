<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'study_program_id',
        'identifier_number',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Study Program relationship.
     */
    public function studyProgram(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id');
    }

    /**
     * Identity number alias (NIM / NIDN).
     */
    public function getIdentityNumberAttribute(): ?string
    {
        return $this->identifier_number;
    }

    /**
     * Audit logs where this user is the subject.
     */
    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    /**
     * Audit logs where this user performed the action.
     */
    public function actionsPerformed(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $query, ?string $term): \Illuminate\Database\Eloquent\Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = "%{$term}%";
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', $term)
              ->orWhere('email', 'like', $term)
              ->orWhere('identifier_number', 'like', $term)
              ->orWhere('phone', 'like', $term);
        });
    }

    public function scopeFilterRole(\Illuminate\Database\Eloquent\Builder $query, ?string $role): \Illuminate\Database\Eloquent\Builder
    {
        if (blank($role)) {
            return $query;
        }

        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', strtoupper($role));
        });
    }

    public function scopeFilterStudyProgram(\Illuminate\Database\Eloquent\Builder $query, mixed $prodiId): \Illuminate\Database\Eloquent\Builder
    {
        if (blank($prodiId)) {
            return $query;
        }

        return $query->where('study_program_id', $prodiId);
    }

    /**
     * Roles relationship (many-to-many through user_roles).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     * Rule: KAPRODI automatically inherits SUPERADMIN privilege.
     */
    public function hasRole(string $role): bool
    {
        $role = strtoupper(trim($role));
        $assignedRoles = $this->roles->pluck('name')->map(fn ($r) => strtoupper($r));

        if ($role === 'SUPERADMIN' && $assignedRoles->contains('KAPRODI')) {
            return true;
        }

        return $assignedRoles->contains($role);
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param  array<string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given roles.
     *
     * @param  array<string>  $roles
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (! $this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has a specific permission.
     * Superadmin and Kaprodi automatically have all permissions.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('SUPERADMIN')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role|string $role): void
    {
        if (is_string($role)) {
            $roleModel = Role::where('name', strtoupper(trim($role)))->firstOrFail();
        } else {
            $roleModel = $role;
        }

        $this->roles()->syncWithoutDetaching([$roleModel->id]);
        $this->unsetRelation('roles');
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role|string $role): void
    {
        if (is_string($role)) {
            $roleModel = Role::where('name', strtoupper(trim($role)))->first();
            if ($roleModel) {
                $this->roles()->detach($roleModel->id);
            }
        } else {
            $this->roles()->detach($role->id);
        }

        $this->unsetRelation('roles');
    }

    /**
     * Synchronize roles.
     *
     * @param  array<string>  $roleNames
     */
    public function syncRoles(array $roleNames): void
    {
        $roleIds = Role::whereIn('name', array_map('strtoupper', $roleNames))->pluck('id');
        $this->roles()->sync($roleIds);
        $this->unsetRelation('roles');
    }

    /**
     * Internship applications submitted by this user (Mahasiswa).
     */
    public function internshipApplications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InternshipApplication::class, 'user_id');
    }

    /**
     * In-app notifications for this user.
     */
    public function appNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppNotification::class, 'user_id')->latest();
    }

    /**
     * Advisor assignment requests assigned to this user (Dosen Pembimbing).
     */
    public function advisorAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InternshipAdvisorAssignment::class, 'advisor_id')->latest();
    }

    /**
     * Active mentored internship applications (where assignment accepted).
     */
    public function mentoredApplications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InternshipApplication::class, 'advisor_id')
            ->where('advisor_status', InternshipApplication::STATUS_ADVISOR_ACCEPTED);
    }

    /**
     * Course conversions submitted by this user (Mahasiswa).
     */
    public function courseConversions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseConversion::class, 'user_id')->latest();
    }

    /**
     * Internship logbooks created by this user (Mahasiswa).
     */
    public function internshipLogbooks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InternshipLogbook::class, 'user_id')->orderBy('week_number', 'asc')->orderBy('activity_date', 'asc');
    }
}
