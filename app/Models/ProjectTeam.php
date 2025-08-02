<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Get the project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user has a specific permission in this project
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || isset($permissions[$permission]) && $permissions[$permission] === true;
    }

    /**
     * Grant a permission
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions[$permission] = true;
        $this->update(['permissions' => $permissions]);
    }

    /**
     * Revoke a permission
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        unset($permissions[$permission]);
        $this->update(['permissions' => $permissions]);
    }

    /**
     * Check if user is project manager
     */
    public function isProjectManager(): bool
    {
        return $this->role === 'project_manager';
    }

    /**
     * Check if user is team member
     */
    public function isTeamMember(): bool
    {
        return $this->role === 'team_member';
    }

    /**
     * Check if user is viewer
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'project_manager' => 'Project Manager',
            'team_member' => 'Team Lid',
            'viewer' => 'Viewer',
            default => ucfirst($this->role)
        };
    }

    /**
     * Get role color
     */
    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            'project_manager' => 'purple',
            'team_member' => 'blue',
            'viewer' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Scope by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope project managers
     */
    public function scopeProjectManagers($query)
    {
        return $query->where('role', 'project_manager');
    }

    /**
     * Scope team members
     */
    public function scopeTeamMembers($query)
    {
        return $query->where('role', 'team_member');
    }

    /**
     * Scope viewers
     */
    public function scopeViewers($query)
    {
        return $query->where('role', 'viewer');
    }
}