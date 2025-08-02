<?php

namespace App\Models;

use App\Traits\HasTeamFeatures;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory, HasTeamFeatures;

    protected $fillable = [
        'milestone_id',
        'project_id',
        'title',
        'description',
        'checklist',
        'status',
        'priority',
        'due_date',
        'start_date',
        'end_date',
        'actual_start_date',
        'actual_end_date',
        'completed_at',
        'estimated_hours',
        'budget',
        'fee_type',
        'included_in_milestone_fee',
        'pricing_type',
        'price',
        'manual_progress',
        'allow_comments',
        'notify_watchers',
        'actual_hours',
        'allocated_budget',
        'spent_amount',
        'remaining_budget',
        'budget_status',
        'budget_notes',
        'completion_percentage',
        'is_billable',
        'timeline_status',
        'billable_hours',
        'hourly_rate',
        'assigned_to',
        'position',
        'order',
        'created_by',
        'owned_by',
        'last_activity_at',
        'has_team_features',
    ];

    protected $casts = [
        'checklist' => 'array',
        'price' => 'decimal:2',
        'budget' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'billable_hours' => 'decimal:2',
        'allocated_budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'manual_progress' => 'integer',
        'position' => 'integer',
        'order' => 'integer',
        'is_billable' => 'boolean',
        'included_in_milestone_fee' => 'boolean',
        'allow_comments' => 'boolean',
        'notify_watchers' => 'boolean',
        'has_team_features' => 'boolean',
        'due_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'completed_at' => 'date',
        'last_activity_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'concept',
        'priority' => 'normaal',
        'fee_type' => 'in_fee',
        'pricing_type' => 'hourly_rate',
        'included_in_milestone_fee' => true,
        'is_billable' => true,
        'completion_percentage' => 0,
        'actual_hours' => 0.00,
        'billable_hours' => 0.00,
        'allocated_budget' => 0.00,
        'spent_amount' => 0.00,
        'remaining_budget' => 0.00,
        'budget_status' => 'on_track',
        'timeline_status' => 'not_started',
        'manual_progress' => 0,
        'allow_comments' => true,
        'notify_watchers' => true,
        'has_team_features' => true,
        'position' => 0,
        'order' => 0,
    ];

    // ===========================
    // RELATIONSHIPS
    // ===========================

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ownedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owned_by');
    }

    // Legacy single assignee (for backward compatibility)
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Multiple assignees through polymorphic relation
    public function assignees(): BelongsToMany
    {
        return $this->morphToMany(User::class, 'assignable', 'assignees')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // Watchers
    public function watchers(): BelongsToMany
    {
        return $this->morphToMany(User::class, 'watchable', 'watchers')
                    ->withTimestamps();
    }

    // Comments
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
                    ->orderBy('created_at', 'desc');
    }

    // Activity logs
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
                    ->orderBy('created_at', 'desc');
    }

    // Attachments
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')
                    ->orderBy('created_at', 'desc');
    }

    // ===========================
    // TEAM FEATURES METHODS
    // ===========================

    /**
     * Add an assignee to the task
     */
    public function assignUser(User $user, string $role = 'assignee'): void
    {
        if (!$this->assignees()->where('user_id', $user->id)->exists()) {
            $this->assignees()->attach($user->id, ['role' => $role]);
            
            // Log activity
            $this->logActivity('assigned', "{$user->name} assigned as {$role}");
            
            // Update last activity
            $this->touch('last_activity_at');
        }
    }

    /**
     * Remove an assignee from the task
     */
    public function unassignUser(User $user): void
    {
        $this->assignees()->detach($user->id);
        
        // Log activity
        $this->logActivity('unassigned', "{$user->name} unassigned");
        
        // Update last activity
        $this->touch('last_activity_at');
    }

    /**
     * Check if user is assigned to this task
     */
    public function isAssignedTo(User $user): bool
    {
        return $this->assignees()->where('user_id', $user->id)->exists() ||
               $this->assigned_to === $user->id;
    }

    /**
     * Get all assigned users (including legacy single assignee)
     */
    public function getAllAssignedUsers()
    {
        $users = $this->assignees;
        
        // Add legacy assigned user if not already in collection
        if ($this->assigned_to && !$users->contains('id', $this->assigned_to)) {
            $legacyUser = User::find($this->assigned_to);
            if ($legacyUser) {
                $users->push($legacyUser);
            }
        }
        
        return $users;
    }

    // ===========================
    // BUDGET CALCULATIONS
    // ===========================

    /**
     * Calculate task price based on pricing type
     */
    public function calculatePrice(): float
    {
        if ($this->pricing_type === 'hourly_rate') {
            return ($this->hourly_rate ?? 0) * ($this->estimated_hours ?? 0);
        }
        
        return $this->price ?? $this->budget ?? 0;
    }

    /**
     * Update budget calculations
     */
    public function updateBudgetCalculations(): void
    {
        // Calculate from subtasks
        $this->allocated_budget = $this->subtasks()->sum('price') ?? 0;
        $this->spent_amount = $this->subtasks()->sum('spent_amount') ?? 0;
        $this->remaining_budget = $this->calculatePrice() - $this->spent_amount;
        
        // Update budget status
        $this->updateBudgetStatus();
        
        // Update completion percentage
        $this->updateCompletionPercentage();
        
        $this->saveQuietly();
        
        // Update parent milestone
        if ($this->milestone) {
            $this->milestone->updateBudgetCalculations();
        }
    }

    /**
     * Update budget status
     */
    private function updateBudgetStatus(): void
    {
        $budget = $this->calculatePrice();
        
        if ($budget == 0) {
            $this->budget_status = 'on_track';
            return;
        }
        
        $percentage = ($this->spent_amount / $budget) * 100;
        
        if ($percentage > 100) {
            $this->budget_status = 'over';
        } elseif ($percentage > 90) {
            $this->budget_status = 'warning';
        } elseif ($percentage < 50 && $this->completion_percentage > 75) {
            $this->budget_status = 'under';
        } else {
            $this->budget_status = 'on_track';
        }
    }

    /**
     * Update completion percentage based on subtasks
     */
    private function updateCompletionPercentage(): void
    {
        if ($this->manual_progress > 0) {
            $this->completion_percentage = $this->manual_progress;
            return;
        }
        
        $subtaskCount = $this->subtasks()->count();
        
        if ($subtaskCount === 0) {
            $this->completion_percentage = $this->status === 'voltooid' ? 100 : 0;
            return;
        }
        
        $completedCount = $this->subtasks()->where('is_completed', true)->count();
        $this->completion_percentage = round(($completedCount / $subtaskCount) * 100, 2);
    }

    // ===========================
    // STATUS & PROGRESS METHODS
    // ===========================

    /**
     * Mark task as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'voltooid',
            'completion_percentage' => 100,
            'completed_at' => now(),
            'actual_end_date' => now(),
        ]);
        
        // Log activity
        $this->logActivity('completed', 'Task marked as completed');
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status !== 'voltooid';
    }

    /**
     * Log activity
     */
    public function logActivity(string $action, string $description = null, array $oldValues = [], array $newValues = []): void
    {
        ActivityLog::log($this, $action, $description, $oldValues, $newValues);
        $this->touch('last_activity_at');
    }

    // ===========================
    // CHECKLIST METHODS
    // ===========================

    /**
     * Add checklist item
     */
    public function addChecklistItem(string $item): void
    {
        $checklist = $this->checklist ?? [];
        $checklist[] = [
            'id' => uniqid(),
            'text' => $item,
            'completed' => false,
        ];
        
        $this->update(['checklist' => $checklist]);
    }

    /**
     * Toggle checklist item
     */
    public function toggleChecklistItem(string $itemId): void
    {
        $checklist = $this->checklist ?? [];
        
        foreach ($checklist as &$item) {
            if ($item['id'] === $itemId) {
                $item['completed'] = !$item['completed'];
                break;
            }
        }
        
        $this->update(['checklist' => $checklist]);
    }

    /**
     * Get checklist progress
     */
    public function getChecklistProgressAttribute(): float
    {
        $checklist = $this->checklist ?? [];
        
        if (empty($checklist)) {
            return 0;
        }
        
        $completed = collect($checklist)->where('completed', true)->count();
        return round(($completed / count($checklist)) * 100, 2);
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    /**
     * Check if user can view this task
     */
    public function canBeViewedBy(User $user): bool
    {
        // Check project access first
        if (!$user->canViewProject($this->project)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can edit this task
     */
    public function canBeEditedBy(User $user): bool
    {
        // Check project edit access
        if ($user->canEditProject($this->project)) {
            return true;
        }
        
        // Check if user is assigned
        if ($this->isAssignedTo($user)) {
            return true;
        }
        
        // Check if user created the task
        if ($this->created_by === $user->id) {
            return true;
        }
        
        return false;
    }

    // ===========================
    // SCOPES
    // ===========================

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('position');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'voltooid');
    }

    public function scopeIncomplete($query)
    {
        return $query->where('status', '!=', 'voltooid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->where('status', '!=', 'voltooid');
    }

    public function scopeAssignedTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
              ->orWhereHas('assignees', function ($q2) use ($user) {
                  $q2->where('user_id', $user->id);
              });
        });
    }

    // ===========================
    // STATIC OPTIONS
    // ===========================

    public static function getStatusOptions(): array
    {
        return [
            'concept' => 'Concept',
            'bezig' => 'Bezig',
            'review' => 'Review',
            'voltooid' => 'Voltooid',
        ];
    }

    public static function getPriorityOptions(): array
    {
        return [
            'laag' => 'Laag',
            'normaal' => 'Normaal',
            'hoog' => 'Hoog',
        ];
    }

    public static function getFeeTypeOptions(): array
    {
        return [
            'in_fee' => 'In Fee',
            'extended_fee' => 'Extended Fee',
        ];
    }

    public static function getPricingTypeOptions(): array
    {
        return [
            'fixed_price' => 'Vaste prijs',
            'hourly_rate' => 'Uurtarief',
        ];
    }
}
