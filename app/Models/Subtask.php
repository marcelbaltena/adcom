<?php

namespace App\Models;

use App\Traits\HasTeamFeatures;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subtask extends Model
{
    use HasFactory, HasTeamFeatures;

    protected $fillable = [
        'task_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'start_date',
        'end_date',
        'completed_at',
        'actual_start_date', 
        'actual_end_date',
        'fee_type',
        'pricing_type',
        'price',
        'budget',
        'estimated_hours',
        'actual_hours',
        'spent_amount',
        'remaining_budget',
        'budget_status',
        'budget_notes',
        'manual_progress',
        'completion_percentage',
        'timeline_status',
        'billable_hours',
        'hourly_rate',
        'assigned_to',
        'assigned_at',
        'allow_comments',
        'notify_watchers',
        'position',
        'order',
        'created_by',
        'owned_by',
        'last_activity_at',
        'has_team_features',
        'is_completed',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'budget' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'billable_hours' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'manual_progress' => 'integer',
        'position' => 'integer',
        'order' => 'integer',
        'allow_comments' => 'boolean',
        'notify_watchers' => 'boolean',
        'has_team_features' => 'boolean',
        'is_completed' => 'boolean',
        'due_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'assigned_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'concept',
        'priority' => 'normaal',
        'fee_type' => 'in_fee',
        'pricing_type' => 'hourly_rate',
        'completion_percentage' => 0,
        'actual_hours' => 0.00,
        'billable_hours' => 0.00,
        'spent_amount' => 0.00,
        'remaining_budget' => 0.00,
        'budget_status' => 'on_track',
        'timeline_status' => 'not_started',
        'manual_progress' => 0,
        'allow_comments' => true,
        'notify_watchers' => true,
        'has_team_features' => true,
        'is_completed' => false,
        'position' => 0,
        'order' => 0,
    ];

    // ===========================
    // RELATIONSHIPS
    // ===========================

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ownedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owned_by');
    }

    // Legacy single assignee
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Multiple assignees
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
     * Assign user to subtask
     */
    public function assignUser(User $user, string $role = 'assignee'): void
    {
        if (!$this->assignees()->where('user_id', $user->id)->exists()) {
            $this->assignees()->attach($user->id, ['role' => $role]);
            
            // Update assigned_at
            $this->update(['assigned_at' => now()]);
            
            // Log activity
            $this->logActivity('assigned', "{$user->name} assigned as {$role}");
        }
    }

    /**
     * Check if user is assigned
     */
    public function isAssignedTo(User $user): bool
    {
        return $this->assignees()->where('user_id', $user->id)->exists() ||
               $this->assigned_to === $user->id;
    }

    // ===========================
    // STATUS METHODS
    // ===========================

    /**
     * Toggle completion status
     */
    public function toggleCompletion(): void
    {
        if ($this->is_completed) {
            $this->markAsIncomplete();
        } else {
            $this->markAsCompleted();
        }
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'status' => 'completed',
            'completion_percentage' => 100,
            'completed_at' => now(),
            'actual_end_date' => now(),
        ]);
        
        // Log activity
        $this->logActivity('completed', 'Subtask marked as completed');
        
        // Update parent task
        if ($this->task) {
            $this->task->updateBudgetCalculations();
        }
    }

    /**
     * Mark as incomplete
     */
    public function markAsIncomplete(): void
    {
        $this->update([
            'is_completed' => false,
            'status' => 'concept',
            'completion_percentage' => 0,
            'completed_at' => null,
        ]);
        
        // Log activity
        $this->logActivity('reopened', 'Subtask reopened');
        
        // Update parent task
        if ($this->task) {
            $this->task->updateBudgetCalculations();
        }
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
    // HELPER METHODS
    // ===========================

    /**
     * Calculate price
     */
    public function calculatePrice(): float
    {
        if ($this->pricing_type === 'hourly_rate') {
            return ($this->hourly_rate ?? 0) * ($this->estimated_hours ?? 0);
        }
        
        return $this->price ?? $this->budget ?? 0;
    }

    /**
     * Check if overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !$this->is_completed;
    }

    /**
     * Check if user can view
     */
    public function canBeViewedBy(User $user): bool
    {
        // Check task/project access
        if ($this->task && !$this->task->canBeViewedBy($user)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can edit
     */
    public function canBeEditedBy(User $user): bool
    {
        // Check task/project edit access
        if ($this->task && $this->task->canBeEditedBy($user)) {
            return true;
        }
        
        // Check if assigned
        if ($this->isAssignedTo($user)) {
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
        return $query->where('is_completed', true);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->where('is_completed', false);
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
    // ATTRIBUTES
    // ===========================

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'concept' => 'gray',
            'in_progress' => 'yellow',
            'completed' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'laag' => 'green',
            'normaal' => 'blue',
            'hoog' => 'red',
            default => 'blue'
        };
    }

    /**
     * Get timeline status
     */
    public function getTimelineStatusAttribute(): string
    {
        if (!$this->due_date) return 'not_set';
        if ($this->is_completed) return 'completed';
        
        $today = now()->startOfDay();
        $dueDate = $this->due_date->startOfDay();
        
        if ($today->greaterThan($dueDate)) return 'overdue';
        if ($today->diffInDays($dueDate) <= 2) return 'due_soon';
        
        return 'on_track';
    }

    // ===========================
    // STATIC OPTIONS
    // ===========================

    public static function getStatusOptions(): array
    {
        return [
            'concept' => 'Concept',
            'in_progress' => 'Bezig',
            'completed' => 'Voltooid',
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