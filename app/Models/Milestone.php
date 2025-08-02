<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'start_date',
        'end_date',
        'actual_start_date', 
        'actual_end_date',
        'completed_at',
        'budget',
        'fee_type',
        'pricing_type',
        'price',
        'estimated_hours',
        'hourly_rate',
        'actual_hours',
        'billable_hours',
        'spent',
        'allocated_budget',
        'remaining_budget',
        'budget_status',
        'budget_notes',
        'completion_percentage',
        'timeline_status',
        'position',
        'order',
        'manual_progress',
        'allow_comments',
        'notify_watchers',
        'created_by',
        'owned_by',
        'last_activity_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'billable_hours' => 'decimal:2',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'allocated_budget' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'manual_progress' => 'integer',
        'position' => 'integer',
        'order' => 'integer',
        'allow_comments' => 'boolean',
        'notify_watchers' => 'boolean',
        'due_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'completed_at' => 'date',
        'last_activity_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'concept',
        'priority' => 'normaal',
        'fee_type' => 'in_fee',
        'pricing_type' => 'fixed_price',
        'completion_percentage' => 0,
        'actual_hours' => 0,
        'billable_hours' => 0,
        'spent' => 0,
        'allocated_budget' => 0,
        'remaining_budget' => 0,
        'budget_status' => 'on_track',
        'timeline_status' => 'not_started',
        'manual_progress' => 0,
        'allow_comments' => true,
        'notify_watchers' => true,
        'position' => 0,
        'order' => 0
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ownedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owned_by');
    }
	
	/**
 * Get the color class based on budget status
 *
 * @return string
 */
public function getBudgetColor()
{
    if ($this->budget <= 0) {
        return 'text-gray-500'; // No budget
    }
    
    $spentPercentage = $this->budget > 0 ? ($this->spent / $this->budget) * 100 : 0;
    
    if ($spentPercentage >= 100) {
        return 'text-red-600'; // Over budget
    } elseif ($spentPercentage >= 90) {
        return 'text-orange-600'; // Warning zone
    } elseif ($spentPercentage >= 75) {
        return 'text-yellow-600'; // Getting close
    } else {
        return 'text-green-600'; // On track
    }
}

    // Calculate total estimated hours from tasks
    public function getTotalEstimatedHours(): float
    {
        return $this->tasks()->sum('estimated_hours');
    }

    // Calculate total actual hours from tasks
    public function getTotalActualHours(): float
    {
        return $this->tasks()->sum('actual_hours');
    }

    // Calculate milestone price
    public function calculatePrice(): float
    {
        if ($this->pricing_type === 'hourly_rate') {
            return ($this->hourly_rate ?? 0) * ($this->estimated_hours ?? 0);
        }
        
        return $this->price ?? 0;
    }

    // Calculate budget from tasks
    public function recalculateBudget(): void
    {
        $totalBudget = $this->tasks()->sum('budget');
        $totalSpent = $this->tasks()->sum('spent_amount');
        $totalRemaining = $this->tasks()->sum('remaining_budget');
        
        // Determine budget status
        $budgetStatus = 'on_track';
        if ($totalBudget > 0) {
            $percentage = ($totalSpent / $totalBudget) * 100;
            if ($percentage > 100) {
                $budgetStatus = 'over';
            } elseif ($percentage > 90) {
                $budgetStatus = 'warning';
            } elseif ($percentage < 50 && $this->completion_percentage > 75) {
                $budgetStatus = 'under';
            }
        }
        
        $this->update([
            'allocated_budget' => $totalBudget,
            'spent' => $totalSpent,
            'remaining_budget' => $totalRemaining,
            'budget_status' => $budgetStatus
        ]);
    }

    // Update completion percentage based on tasks
    public function updateCompletionPercentage(): void
    {
        if ($this->tasks->count() === 0) {
            return;
        }

        $totalPercentage = $this->tasks->sum('completion_percentage');
        $averagePercentage = $totalPercentage / $this->tasks->count();

        $this->update(['completion_percentage' => round($averagePercentage, 2)]);
    }

    // Check if milestone is overdue
    public function isOverdue(): bool
    {
        return $this->due_date && Carbon::parse($this->due_date)->isPast() && $this->status !== 'completed';
    }

    // Mark as completed
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completion_percentage' => 100,
            'completed_at' => now(),
            'actual_end_date' => now()
        ]);
    }

    /**
     * Update budget calculations for this milestone
     * This method is called when tasks are added/updated/deleted
     */
    public function updateBudgetCalculations()
    {
        // First recalculate using existing method
        $this->recalculateBudget();
        
        // Calculate total estimated hours from tasks
        $this->updateEstimatedHours();
        
        // Update project totals if project exists
        if ($this->project) {
            $this->project->updateBudgetCalculations();
        }
    }

    /**
     * Update total estimated and actual hours from tasks
     */
    public function updateEstimatedHours()
    {
        $totalEstimatedHours = $this->tasks()->sum('estimated_hours');
        $totalActualHours = $this->tasks()->sum('actual_hours');
        
        // Only update if not manually set
        $updates = [];
        if (!$this->estimated_hours || $this->estimated_hours == 0) {
            $updates['estimated_hours'] = $totalEstimatedHours;
        }
        
        // Always update actual hours from tasks
        $updates['actual_hours'] = $totalActualHours;
        $updates['billable_hours'] = $this->tasks()->sum('billable_hours');
        
        if (!empty($updates)) {
            $this->update($updates);
        }
    }

    /**
     * Get actual cost (alias for spent)
     */
    public function getActualCostAttribute()
    {
        return $this->spent;
    }

    /**
     * Set actual cost (alias for spent)
     */
    public function setActualCostAttribute($value)
    {
        $this->attributes['spent'] = $value;
    }

    // Status options (Nederlands)
    public static function getStatusOptions(): array
    {
        return [
            'concept' => 'Concept',
            'in_progress' => 'Bezig',
            'completed' => 'Afgerond'
        ];
    }

    // Priority options (Nederlands)
    public static function getPriorityOptions(): array
    {
        return [
            'laag' => 'Laag',
            'normaal' => 'Normaal',
            'hoog' => 'Hoog'
        ];
    }

    // Fee type options
    public static function getFeeTypeOptions(): array
    {
        return [
            'in_fee' => 'In Fee',
            'extended_fee' => 'Extended Fee'
        ];
    }

    // Pricing type options
    public static function getPricingTypeOptions(): array
    {
        return [
            'fixed_price' => 'Vaste prijs',
            'hourly_rate' => 'Uurtarief'
        ];
    }

    // Budget status options
    public static function getBudgetStatusOptions(): array
    {
        return [
            'under' => 'Onder budget',
            'on_track' => 'Op schema',
            'warning' => 'Waarschuwing',
            'over' => 'Over budget'
        ];
    }

    // Timeline status options
    public static function getTimelineStatusOptions(): array
    {
        return [
            'not_started' => 'Niet gestart',
            'on_time' => 'Op tijd',
            'behind' => 'Achterstand',
            'ahead' => 'Voorsprong'
        ];
    }
}