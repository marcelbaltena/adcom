<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'project_value',
        'currency',
        'spent',
        'user_id',
        'billing_company_id',
        'created_by_company_id',
        'customer_id',
        'source',
        'customer_can_view',
        'customer_permissions',
        'budget_tolerance_percentage',
        'budget_warning_percentage',
        'allocated_budget',
        'remaining_budget',
        'budget_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'project_value' => 'decimal:2',
        'spent' => 'decimal:2',
        'allocated_budget' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'customer_can_view' => 'boolean',
        'customer_permissions' => 'array',
        'budget_tolerance_percentage' => 'decimal:2',
        'budget_warning_percentage' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'planning',
        'currency' => 'EUR',
        'spent' => 0,
        'allocated_budget' => 0,
        'budget_status' => 'on_track',
        'customer_can_view' => true,
        'budget_tolerance_percentage' => 10.00,
        'budget_warning_percentage' => 5.00,
    ];

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($project) {
            // Update budget calculations before saving
            if (!$project->isDirty('spent')) {
                $project->spent = $project->calculateSpentAmount();
            }
            if (!$project->isDirty('allocated_budget')) {
                $project->allocated_budget = $project->calculateAllocatedBudget();
            }
            if (!$project->isDirty('remaining_budget')) {
                $project->remaining_budget = $project->budget - $project->spent;
            }
            
            // Update budget status
            $project->updateBudgetStatus();
        });
    }

    // ===========================
    // RELATIONSHIPS
    // ===========================

    /**
     * Get the customer for this project
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the billing company for this project
     */
    public function billingCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'billing_company_id');
    }

    /**
     * Get the company that created this project
     */
    public function createdByCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'created_by_company_id');
    }

    /**
     * Get the owner of this project
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for owner relationship
     */
    public function user(): BelongsTo
    {
        return $this->owner();
    }

    /**
     * Get all milestones for this project
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('order');
    }

    /**
     * Get all tasks through milestones
     */
    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Milestone::class);
    }

    /**
     * Get all team members for this project
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_teams')
                    ->withPivot('role', 'permissions')
                    ->withTimestamps();
    }

    /**
     * Get project team entries
     */
    public function projectTeams(): HasMany
    {
        return $this->hasMany(ProjectTeam::class);
    }

    /**
     * Get all watchers for this project
     */
    public function watchers(): BelongsToMany
    {
        return $this->morphToMany(User::class, 'watchable', 'watchers')
                    ->withTimestamps();
    }

    /**
     * Get all comments for this project
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get all activity logs for this project
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get all attachments for this project
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')
                    ->orderBy('created_at', 'desc');
    }

    // ===========================
    // TEAM MANAGEMENT METHODS
    // ===========================

    /**
     * Add a team member to the project
     */
    public function addTeamMember(User $user, string $role = 'team_member', array $permissions = []): void
    {
        // Check if user is already in team
        if ($this->teamMembers()->where('user_id', $user->id)->exists()) {
            throw new \Exception('User is already a team member of this project');
        }

        $this->teamMembers()->attach($user->id, [
            'role' => $role,
            'permissions' => json_encode($permissions)
        ]);

        // Log activity
        $this->logActivity('team_member_added', "Added {$user->name} as {$role}");
    }

    /**
     * Remove a team member from the project
     */
    public function removeTeamMember(User $user): void
    {
        $this->teamMembers()->detach($user->id);

        // Log activity
        $this->logActivity('team_member_removed', "Removed {$user->name} from team");
    }

    /**
     * Update team member's role and permissions
     */
    public function updateTeamMemberRole(User $user, string $role, array $permissions = []): void
    {
        $this->teamMembers()->updateExistingPivot($user->id, [
            'role' => $role,
            'permissions' => json_encode($permissions)
        ]);

        // Log activity
        $this->logActivity('team_member_updated', "Updated {$user->name}'s role to {$role}");
    }

    /**
     * Check if user is a team member
     */
    public function hasTeamMember(User $user): bool
    {
        return $this->teamMembers()->where('user_id', $user->id)->exists();
    }

    /**
     * Get team member's role
     */
    public function getTeamMemberRole(User $user): ?string
    {
        $member = $this->teamMembers()->where('user_id', $user->id)->first();
        return $member?->pivot->role;
    }

    /**
     * Get project managers
     */
    public function getProjectManagers()
    {
        return $this->teamMembers()->wherePivot('role', 'project_manager')->get();
    }

    /**
     * Check if user is project manager
     */
    public function isProjectManager(User $user): bool
    {
        return $this->getTeamMemberRole($user) === 'project_manager';
    }

    // ===========================
    // WATCHER METHODS
    // ===========================

    /**
     * Add a watcher to the project
     */
    public function addWatcher(User $user): void
    {
        if (!$this->watchers()->where('user_id', $user->id)->exists()) {
            $this->watchers()->attach($user->id);
        }
    }

    /**
     * Remove a watcher from the project
     */
    public function removeWatcher(User $user): void
    {
        $this->watchers()->detach($user->id);
    }

    /**
     * Check if user is watching
     */
    public function isWatchedBy(User $user): bool
    {
        return $this->watchers()->where('user_id', $user->id)->exists();
    }

    // ===========================
    // ACTIVITY LOGGING
    // ===========================

    /**
     * Log activity for this project
     */
    public function logActivity(string $action, string $description = null, array $oldValues = [], array $newValues = []): void
    {
        $this->activityLogs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // ===========================
    // BUDGET CALCULATIONS
    // ===========================

    /**
     * Calculate allocated budget from milestones
     */
    public function calculateAllocatedBudget(): float
    {
        return $this->milestones()
            ->where('fee_type', 'in_fee')
            ->sum('price') ?? 0;
    }

    /**
     * Calculate spent amount from milestones
     */
    public function calculateSpentAmount(): float
    {
        return $this->milestones()->sum('spent') ?? 0;
    }

    /**
     * Calculate extended fee budget
     */
    public function calculateExtendedFeeBudget(): float
    {
        return $this->milestones()
            ->where('fee_type', 'extended')
            ->sum('price') ?? 0;
    }

    /**
     * Get remaining budget attribute
     */
    public function getRemainingBudgetAttribute(): float
    {
        return $this->budget - $this->spent;
    }

    /**
     * Update all budget calculations
     */
    public function updateBudgetCalculations(): void
    {
        $this->spent = $this->calculateSpentAmount();
        $this->allocated_budget = $this->calculateAllocatedBudget();
        $this->remaining_budget = $this->budget - $this->spent;
        
        $this->updateBudgetStatus();
        $this->saveQuietly(); // Save without triggering events again
    }

    /**
     * Alias for updateBudgetCalculations
     */
    public function recalculateBudget(): void
    {
        $this->updateBudgetCalculations();
    }

    /**
     * Update budget status based on spending
     */
    public function updateBudgetStatus(): void
    {
        if (!$this->budget || $this->budget == 0) {
            $this->budget_status = 'on_track';
            return;
        }

        $spentPercentage = ($this->spent / $this->budget) * 100;
        $warningThreshold = $this->budget_warning_percentage ?? 5;
        $toleranceThreshold = $this->budget_tolerance_percentage ?? 10;

        if ($spentPercentage >= (100 + $toleranceThreshold)) {
            $this->budget_status = 'over';
        } elseif ($spentPercentage >= (100 - $warningThreshold)) {
            $this->budget_status = 'warning';
        } elseif ($spentPercentage < 50) {
            $this->budget_status = 'under';
        } else {
            $this->budget_status = 'on_track';
        }
    }

    /**
     * Check if project can allocate budget
     */
    public function canAllocateBudget(float $amount, $excludeMilestone = null): bool
    {
        $query = $this->milestones()->where('fee_type', 'in_fee');
        
        if ($excludeMilestone) {
            $query->where('id', '!=', $excludeMilestone->id);
        }
        
        $currentAllocation = $query->sum('price') ?? 0;
        $availableBudget = $this->budget - $currentAllocation;
        
        return $amount <= $availableBudget;
    }

    // ===========================
    // FINANCIAL CALCULATIONS
    // ===========================

    /**
     * Get total project value including extended fees
     */
    public function getTotalProjectValue(): float
    {
        $baseValue = $this->project_value ?? $this->budget;
        $extendedFees = $this->calculateExtendedFeeBudget();
        
        return $baseValue + $extendedFees;
    }

    /**
     * Get profit margin
     */
    public function getProfitMargin(): float
    {
        $revenue = $this->getTotalProjectValue();
        $costs = $this->spent;
        
        if ($revenue == 0) return 0;
        
        return $revenue - $costs;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginPercentage(): float
    {
        $revenue = $this->getTotalProjectValue();
        
        if ($revenue == 0) return 0;
        
        return round(($this->getProfitMargin() / $revenue) * 100, 2);
    }

    /**
     * Get budget variance
     */
    public function getBudgetVariance(): float
    {
        return $this->budget - $this->spent;
    }

    /**
     * Get budget variance percentage
     */
    public function getBudgetVariancePercentage(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        
        return round((($this->budget - $this->spent) / $this->budget) * 100, 2);
    }

    /**
     * Get budget utilization percentage
     */
    public function getBudgetUtilization(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        
        return round(($this->allocated_budget / $this->budget) * 100, 2);
    }

    /**
     * Get budget efficiency percentage
     */
    public function getBudgetEfficiency(): float
    {
        if (!$this->allocated_budget || $this->allocated_budget == 0) {
            return 0;
        }
        
        return round(($this->spent / $this->allocated_budget) * 100, 2);
    }

    // ===========================
    // STATUS & HELPER METHODS
    // ===========================

    /**
     * Check if project is over budget
     */
    public function isOverBudget(): bool
    {
        return $this->budget_status === 'over';
    }

    /**
     * Check if project is in warning zone
     */
    public function isInWarningZone(): bool
    {
        return $this->budget_status === 'warning';
    }

    /**
     * Get budget color based on status
     */
    public function getBudgetColor(): string
    {
        if (!$this->budget || $this->budget == 0) {
            return 'text-gray-600';
        }

        $spentPercentage = ($this->spent / $this->budget) * 100;
        $warningThreshold = $this->budget_warning_percentage ?? 5;
        $toleranceThreshold = $this->budget_tolerance_percentage ?? 10;

        if ($spentPercentage <= $warningThreshold) {
            return 'text-green-600';
        } elseif ($spentPercentage <= (100 - $toleranceThreshold)) {
            return 'text-green-600';
        } elseif ($spentPercentage <= (100 + $toleranceThreshold)) {
            return 'text-yellow-600';
        } else {
            return 'text-red-600';
        }
    }

    /**
     * Get status color attribute
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'planning' => 'blue',
            'active' => 'green',
            'on_hold' => 'yellow',
            'completed' => 'gray',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status label attribute
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planning' => 'Planning',
            'active' => 'Actief',
            'on_hold' => 'On Hold',
            'completed' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
            default => 'Onbekend'
        };
    }

    /**
     * Get budget status color attribute
     */
    public function getBudgetStatusColorAttribute(): string
    {
        return match($this->budget_status) {
            'under' => 'blue',
            'on_track' => 'green',
            'warning' => 'yellow',
            'over' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get budget status label attribute
     */
    public function getBudgetStatusLabelAttribute(): string
    {
        return match($this->budget_status) {
            'under' => 'Onder Budget',
            'on_track' => 'Op Schema',
            'warning' => 'Waarschuwing',
            'over' => 'Over Budget',
            default => 'Onbekend'
        };
    }

    // ===========================
    // FORMATTED ATTRIBUTES
    // ===========================

    /**
     * Get formatted budget
     */
    public function getFormattedBudgetAttribute(): string
    {
        $symbol = match($this->currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            default => $this->currency . ' '
        };
        
        return $symbol . number_format($this->budget, 2);
    }

    /**
     * Get formatted spent amount
     */
    public function getFormattedSpentAttribute(): string
    {
        $symbol = match($this->currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            default => $this->currency . ' '
        };
        
        return $symbol . number_format($this->spent, 2);
    }

    /**
     * Get formatted remaining budget
     */
    public function getFormattedRemainingBudgetAttribute(): string
    {
        $symbol = match($this->currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            default => $this->currency . ' '
        };
        
        return $symbol . number_format($this->remaining_budget, 2);
    }

    // ===========================
    // SCOPES
    // ===========================

    /**
     * Scope active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope completed projects
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope projects over budget
     */
    public function scopeOverBudget($query)
    {
        return $query->where('budget_status', 'over');
    }

    /**
     * Scope projects in warning
     */
    public function scopeInWarning($query)
    {
        return $query->where('budget_status', 'warning');
    }

    /**
     * Scope projects for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope projects for a specific customer
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope projects for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where(function($q) use ($companyId) {
            $q->where('billing_company_id', $companyId)
              ->orWhere('created_by_company_id', $companyId);
        });
    }

    /**
     * Scope accessible projects for a user
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->role === 'admin' || $user->can_see_all_projects) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Own projects
            $q->where('user_id', $user->id)
              // Company projects
              ->orWhere(function ($q2) use ($user) {
                  if ($user->company_id) {
                      $q2->where('billing_company_id', $user->company_id)
                         ->orWhere('created_by_company_id', $user->company_id);
                  }
              })
              // Team member projects
              ->orWhereHas('teamMembers', function ($q3) use ($user) {
                  $q3->where('user_id', $user->id);
              });
        });
    }

    // ===========================
    // STATUS OPTIONS
    // ===========================

    /**
     * Get available status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'planning' => 'Planning',
            'active' => 'Actief',
            'on_hold' => 'On Hold',
            'completed' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
        ];
    }

    /**
     * Get available currency options
     */
    public static function getCurrencyOptions(): array
    {
        return [
            'EUR' => 'Euro (€)',
            'USD' => 'US Dollar ($)',
            'GBP' => 'British Pound (£)',
        ];
    }

    /**
     * Get available source options
     */
    public static function getSourceOptions(): array
    {
        return [
            'direct' => 'Direct',
            'referral' => 'Referral',
            'marketing' => 'Marketing',
            'existing_customer' => 'Bestaande Klant',
        ];
    }
}