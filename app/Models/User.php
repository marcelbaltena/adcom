<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
        'hourly_rate',
        'is_active',
        'permissions',
        'can_see_all_projects',
        'can_see_financial_data',
        'department',
        'timezone',
        'notification_preferences',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'permissions' => 'array',
        'can_see_all_projects' => 'boolean',
        'can_see_financial_data' => 'boolean',
        'notification_preferences' => 'array',
    ];

    /**
     * Default attributes
     */
    protected $attributes = [
        'role' => 'user',
        'is_active' => true,
        'can_see_all_projects' => false,
        'can_see_financial_data' => false,
        'timezone' => 'Europe/Amsterdam',
    ];

    // ===========================
    // RELATIONSHIPS
    // ===========================

    /**
     * Get the company that the user belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the work schedule for the user.
     */
    public function workSchedule(): HasOne
    {
        return $this->hasOne(UserWorkSchedule::class);
    }

    /**
     * Get the monthly hours for the user.
     */
    public function monthlyHours(): HasMany
    {
        return $this->hasMany(UserMonthlyHours::class);
    }

    /**
     * Get the projects this user is a team member of
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_teams')
                    ->withPivot('role', 'permissions')
                    ->withTimestamps();
    }

    /**
     * Get project teams entries
     */
    public function projectTeams(): HasMany
    {
        return $this->hasMany(ProjectTeam::class);
    }

    /**
     * Get all tasks assigned to this user (through assignees)
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->morphedByMany(Task::class, 'assignable', 'assignees')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get all subtasks assigned to this user (through assignees)
     */
    public function assignedSubtasks(): BelongsToMany
    {
        return $this->morphedByMany(Subtask::class, 'assignable', 'assignees')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get all milestones assigned to this user (through assignees)
     */
    public function assignedMilestones(): BelongsToMany
    {
        return $this->morphedByMany(Milestone::class, 'assignable', 'assignees')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get all items this user is watching
     */
    public function watching(): HasMany
    {
        return $this->hasMany(Watcher::class);
    }

    /**
     * Get all comments by this user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all mentions of this user
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class, 'mentioned_user_id');
    }

    /**
     * Get all attachments uploaded by this user
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get all activity logs by this user
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Projects owned by this user
     */
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'user_id');
    }

    /**
     * Tasks created by this user
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Subtasks created by this user
     */
    public function createdSubtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'created_by');
    }

    // ===========================
    // PERMISSION METHODS
    // ===========================

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission, string $resource = null, string $action = 'view'): bool
    {
        // Super admin kan alles
        if ($this->role === 'admin') {
            return true;
        }

        // Check role-based permissions
        if ($this->hasRolePermission($permission, $resource, $action)) {
            return true;
        }

        // Check user-specific permissions
        $permissions = $this->permissions ?? [];
        
        if ($resource) {
            $key = "{$resource}.{$action}.{$permission}";
        } else {
            $key = $permission;
        }

        return data_get($permissions, $key, false);
    }

    /**
     * Check if user's role has a permission
     */
    public function hasRolePermission(string $permission, ?string $resource, string $action): bool
    {
        return RolePermission::where('role', $this->role)
            ->where('permission', $permission)
            ->where('resource', $resource ?? '*')
            ->where('action', $action)
            ->where('allowed', true)
            ->exists();
    }

    /**
     * Check if user can view a specific project
     */
    public function canViewProject($project): bool
    {
        if ($project instanceof Project === false) {
            $project = Project::find($project);
        }

        // Admin en beheerder kunnen alles zien
        if (in_array($this->role, ['admin', 'beheerder'])) {
            return true;
        }

        // Check if user can see all projects
        if ($this->can_see_all_projects) {
            return true;
        }

        // Check if user is project owner
        if ($project->user_id === $this->id) {
            return true;
        }

        // Check if user is in the same company
        if ($this->company_id && in_array($this->company_id, [$project->billing_company_id, $project->created_by_company_id])) {
            return true;
        }

        // Check if user is in project team
        return $this->projects()->where('projects.id', $project->id)->exists();
    }

    /**
     * Check if user can edit a specific project
     */
    public function canEditProject($project): bool
    {
        if ($project instanceof Project === false) {
            $project = Project::find($project);
        }

        // Admin kan alles bewerken
        if ($this->role === 'admin') {
            return true;
        }

        // Beheerder kan projecten van eigen company bewerken
        if ($this->role === 'beheerder' && $this->company_id) {
            if (in_array($this->company_id, [$project->billing_company_id, $project->created_by_company_id])) {
                return true;
            }
        }

        // Project owner
        if ($project->user_id === $this->id) {
            return true;
        }

        // Check project team role
        $teamMember = $this->projectTeams()
            ->where('project_id', $project->id)
            ->first();

        return $teamMember && $teamMember->role === 'project_manager';
    }

    /**
     * Check if user can view financial data
     */
    public function canViewFinancials(): bool
    {
        return in_array($this->role, ['admin', 'beheerder', 'account_manager']) 
               || $this->can_see_financial_data;
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return in_array($this->role, ['admin', 'beheerder']);
    }

    /**
     * Check if user can manage companies
     */
    public function canManageCompanies(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Grant a specific permission to the user
     */
    public function grantPermission(string $permission, string $resource = null, string $action = 'view'): void
    {
        $permissions = $this->permissions ?? [];
        
        if ($resource) {
            $key = "{$resource}.{$action}.{$permission}";
        } else {
            $key = $permission;
        }

        data_set($permissions, $key, true);
        
        $this->update(['permissions' => $permissions]);
    }

    /**
     * Revoke a specific permission from the user
     */
    public function revokePermission(string $permission, string $resource = null, string $action = 'view'): void
    {
        $permissions = $this->permissions ?? [];
        
        if ($resource) {
            $key = "{$resource}.{$action}.{$permission}";
        } else {
            $key = $permission;
        }

        data_forget($permissions, $key);
        
        $this->update(['permissions' => $permissions]);
    }

    // ===========================
    // ROLE METHODS
    // ===========================

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is beheerder
     */
    public function isBeheerder(): bool
    {
        return $this->role === 'beheerder';
    }

    /**
     * Check if user is account manager
     */
    public function isAccountManager(): bool
    {
        return $this->role === 'account_manager';
    }

    /**
     * Check if user is regular user
     */
    public function isRegularUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has management role
     */
    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'beheerder', 'account_manager']);
    }

    /**
     * Get display name for role
     */
    public function getDisplayRoleAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'beheerder' => 'Beheerder',
            'account_manager' => 'Account Manager',
            'user' => 'Gebruiker',
            default => ucfirst($this->role)
        };
    }

    // ===========================
    // SCOPES
    // ===========================

    /**
     * Scope a query to only include active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include users of a specific company
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include users with a specific role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to only include managers
     */
    public function scopeManagers($query)
    {
        return $query->whereIn('role', ['admin', 'beheerder', 'account_manager']);
    }

    /**
     * Scope a query to only include users who can see financials
     */
    public function scopeWithFinancialAccess($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('role', ['admin', 'beheerder', 'account_manager'])
              ->orWhere('can_see_financial_data', true);
        });
    }

    // ===========================
    // ATTRIBUTE ACCESSORS
    // ===========================

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Default gravatar
        return 'https://www.gravatar.com/avatar/' . md5(strtolower($this->email)) . '?d=identicon&s=200';
    }

    /**
     * Get user's initials
     */
    public function getInitialsAttribute(): string
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';

        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }

        return substr($initials, 0, 2);
    }

    /**
     * Get effective hourly rate (user rate or company default)
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        return $this->hourly_rate ?: ($this->company?->default_hourly_rate ?? 0);
    }

    /**
     * Get company name
     */
    public function getCompanyNameAttribute(): string
    {
        return $this->company?->name ?? 'Geen bedrijf';
    }

    /**
     * Get unread mentions count
     */
    public function getUnreadMentionsCountAttribute(): int
    {
        return $this->mentions()->where('is_read', false)->count();
    }

    /**
     * Get assigned items count
     */
    public function getAssignedItemsCountAttribute(): int
    {
        return $this->assignedTasks()->count() + 
               $this->assignedSubtasks()->count() + 
               $this->assignedMilestones()->count();
    }

    /**
     * Get watching items count
     */
    public function getWatchingItemsCountAttribute(): int
    {
        return $this->watching()->count();
    }

    /**
     * Get current month hours
     */
    public function getCurrentMonthHoursAttribute()
    {
        return $this->getMonthlyHours(date('Y'), date('n'));
    }

    /**
     * Get current month billability percentage
     */
    public function getCurrentBillabilityAttribute()
    {
        $hours = $this->current_month_hours;
        if (!$hours || $hours->total_worked_hours == 0) {
            return 0;
        }
        
        return $hours->billability_percentage;
    }

    /**
     * Get working days string
     */
    public function getWorkingDaysStringAttribute()
    {
        $schedule = $this->workSchedule;
        if (!$schedule) {
            return 'Niet ingesteld';
        }
        
        return implode(', ', $schedule->working_days);
    }

    // ===========================
    // BUSINESS METHODS
    // ===========================

    /**
     * Activate the user
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the user
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Change user's company
     */
    public function changeCompany(Company $company): void
    {
        $this->update(['company_id' => $company->id]);
    }

    /**
     * Change user's role
     */
    public function changeRole(string $role): void
    {
        if (!in_array($role, ['admin', 'beheerder', 'account_manager', 'user'])) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }

        $this->update(['role' => $role]);
    }

    /**
     * Check if user is in the same company as another user
     */
    public function isSameCompany(User $user): bool
    {
        return $this->company_id && $this->company_id === $user->company_id;
    }

    /**
     * Check if user belongs to a specific company
     */
    public function belongsToCompany($companyId): bool
    {
        return $this->company_id == $companyId;
    }

    /**
     * Get all accessible projects for this user
     */
    public function getAccessibleProjects()
    {
        $query = Project::query();

        if ($this->role === 'admin' || $this->can_see_all_projects) {
            return $query;
        }

        return $query->where(function ($q) {
            // Own projects
            $q->where('user_id', $this->id)
              // Company projects
              ->orWhere(function ($q2) {
                  if ($this->company_id) {
                      $q2->where('billing_company_id', $this->company_id)
                         ->orWhere('created_by_company_id', $this->company_id);
                  }
              })
              // Team member projects
              ->orWhereHas('teamMembers', function ($q3) {
                  $q3->where('user_id', $this->id);
              });
        });
    }

    /**
     * Add user to project team
     */
    public function addToProjectTeam(Project $project, string $role = 'team_member', array $permissions = []): void
    {
        $this->projects()->attach($project->id, [
            'role' => $role,
            'permissions' => json_encode($permissions)
        ]);
    }

    /**
     * Remove user from project team
     */
    public function removeFromProjectTeam(Project $project): void
    {
        $this->projects()->detach($project->id);
    }

    /**
     * Update notification preference
     */
    public function updateNotificationPreference(string $type, bool $enabled): void
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$type] = $enabled;

        $this->update(['notification_preferences' => $preferences]);
    }

    /**
     * Check if user prefers a specific notification type
     */
    public function prefersNotification(string $type): bool
    {
        $preferences = $this->notification_preferences ?? [];
        return $preferences[$type] ?? true;
    }

    /**
     * Get monthly hours for a specific month
     */
    public function getMonthlyHours($year, $month)
    {
        return $this->monthlyHours()
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }

    /**
     * Get or create work schedule with default values
     */
    public function getOrCreateWorkSchedule()
    {
        return $this->workSchedule ?? $this->workSchedule()->create([
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => false,
            'sunday' => false,
            'hours_per_day' => 8,
            'hours_per_week' => 40,
            'hours_per_month' => 173.33,
        ]);
    }

    /**
     * Calculate expected hours for a specific month
     */
    public function calculateExpectedHours($year, $month)
    {
        $schedule = $this->getOrCreateWorkSchedule();
        return $schedule->calculateMonthlyHours($year, $month);
    }

    /**
     * Get total billable hours for a period
     */
    public function getBillableHours($startDate = null, $endDate = null)
    {
        $query = $this->monthlyHours();
        
        if ($startDate && $endDate) {
            $startYear = date('Y', strtotime($startDate));
            $startMonth = date('n', strtotime($startDate));
            $endYear = date('Y', strtotime($endDate));
            $endMonth = date('n', strtotime($endDate));
            
            $query->where(function($q) use ($startYear, $startMonth, $endYear, $endMonth) {
                $q->where(function($q2) use ($startYear, $startMonth, $endYear, $endMonth) {
                    $q2->where('year', '>', $startYear)
                       ->orWhere(function($q3) use ($startYear, $startMonth) {
                           $q3->where('year', $startYear)
                              ->where('month', '>=', $startMonth);
                       });
                })->where(function($q2) use ($endYear, $endMonth) {
                    $q2->where('year', '<', $endYear)
                       ->orWhere(function($q3) use ($endYear, $endMonth) {
                           $q3->where('year', $endYear)
                              ->where('month', '<=', $endMonth);
                       });
                });
            });
        }
        
        return $query->sum('billable_hours');
    }

    /**
     * Get productivity stats for a period
     */
    public function getProductivityStats($year = null, $month = null)
    {
        $query = $this->monthlyHours();
        
        if ($year) {
            $query->where('year', $year);
            if ($month) {
                $query->where('month', $month);
            }
        }
        
        $hours = $query->get();
        
        $totalContracted = $hours->sum('contracted_hours');
        $totalBillable = $hours->sum('billable_hours');
        $totalNonBillable = $hours->sum('non_billable_hours');
        $totalWorked = $totalBillable + $totalNonBillable;
        $totalVacation = $hours->sum('vacation_hours');
        $totalSick = $hours->sum('sick_hours');
        
        return [
            'contracted_hours' => $totalContracted,
            'worked_hours' => $totalWorked,
            'billable_hours' => $totalBillable,
            'non_billable_hours' => $totalNonBillable,
            'vacation_hours' => $totalVacation,
            'sick_hours' => $totalSick,
            'productivity_percentage' => $totalContracted > 0 ? round(($totalWorked / $totalContracted) * 100, 2) : 0,
            'billability_percentage' => $totalWorked > 0 ? round(($totalBillable / $totalWorked) * 100, 2) : 0,
        ];
    }

    /**
     * Check if user works on a specific day
     */
    public function worksOn($dayName)
    {
        $schedule = $this->workSchedule;
        if (!$schedule) {
            return false;
        }
        
        $dayName = strtolower($dayName);
        return $schedule->$dayName ?? false;
    }

    /**
     * Get role color for UI
     */
    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            'admin' => 'red',
            'beheerder' => 'orange',
            'account_manager' => 'blue',
            'user' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get role badge HTML
     */
    public function getRoleBadgeAttribute(): string
    {
        $color = $this->role_color;
        $label = $this->display_role;

        return "<span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{$color}-100 text-{$color}-800\">{$label}</span>";
    }
}