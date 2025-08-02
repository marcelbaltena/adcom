<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Task;
use App\Models\Milestone;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_template_id',
        'title',
        'description',
        'fee_type',
        'pricing_type',
        'price',
        'hourly_rate',
        'estimated_hours',
        'checklist_items',
        'deliverables',
        'default_start_date',
        'default_end_date',
        'order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'checklist_items' => 'array',
        'deliverables' => 'array',
        'default_start_date' => 'date',
        'default_end_date' => 'date',
        'order' => 'integer'
    ];

    protected $attributes = [
        'fee_type' => 'in_fee',
        'pricing_type' => 'fixed_price',
        'order' => 0
    ];

    // Relationships
    public function milestoneTemplate(): BelongsTo
    {
        return $this->belongsTo(MilestoneTemplate::class);
    }

    public function subtaskTemplates(): HasMany
    {
        return $this->hasMany(SubtaskTemplate::class)->orderBy('order');
    }

    // Calculate price
    public function calculatePrice(): float
    {
        if ($this->pricing_type === 'hourly_rate') {
            return ($this->hourly_rate ?? 0) * ($this->estimated_hours ?? 0);
        }
        
        return $this->price ?? 0;
    }

    // Get total estimated hours including subtasks
    public function getTotalEstimatedHours(): float
    {
        $taskHours = $this->estimated_hours ?? 0;
        $subtaskHours = $this->subtaskTemplates->sum('estimated_hours');
        
        return $taskHours + $subtaskHours;
    }

    // Clone to milestone
    public function cloneToMilestone(Milestone $milestone): Task
    {
        DB::beginTransaction();
        try {
            // Map template fee_type to task fee_type
            $feeTypeMapping = [
                'in_fee' => 'in_fee',
                'extended' => 'extended_fee'
            ];
            
            $mappedFeeType = $feeTypeMapping[$this->fee_type] ?? 'in_fee';
            
            // Get current max order for this milestone
            $maxOrder = $milestone->tasks()->max('order') ?? -1;
            $maxPosition = $milestone->tasks()->max('position') ?? -1;
            
            // Calculate dates based on milestone dates
            $startDate = $this->default_start_date ?? $milestone->start_date ?? now();
            $endDate = $this->default_end_date ?? $milestone->end_date ?? now()->addDays(7);
            
            // Create task from template
            $taskData = [
                'milestone_id' => $milestone->id,
                'project_id' => $milestone->project_id,
                'title' => $this->title,
                'description' => $this->description,
                'fee_type' => $mappedFeeType,
                'pricing_type' => $this->pricing_type,
                'price' => $this->price,
                'hourly_rate' => $this->hourly_rate,
                'estimated_hours' => $this->estimated_hours,
                'budget' => $this->calculatePrice(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'concept',
                'priority' => 'normaal',
                'order' => $maxOrder + 1,
                'position' => $maxPosition + 1,
                'completion_percentage' => 0,
                'is_billable' => true,
                'actual_hours' => 0,
                'billable_hours' => 0,
                'included_in_milestone_fee' => true,
                'allocated_budget' => 0,
                'spent_amount' => 0,
                'remaining_budget' => $this->calculatePrice(),
                'budget_status' => 'on_track',
                'timeline_status' => 'not_started'
            ];
            
            // Add checklist items if present
            if ($this->checklist_items && is_array($this->checklist_items)) {
                $taskData['checklist'] = $this->checklist_items;
            }
            
            // Add deliverables to description if present
            if ($this->deliverables && is_array($this->deliverables)) {
                $deliverablesText = "\n\nDeliverables:\n" . implode("\n", array_map(function($item) { 
                    return "• " . $item; 
                }, $this->deliverables));
                
                $taskData['description'] = ($taskData['description'] ?? '') . $deliverablesText;
            }
            
            $task = Task::create($taskData);
            
            // Clone all subtasks
            foreach ($this->subtaskTemplates as $subtaskTemplate) {
                $subtaskTemplate->cloneToTask($task);
            }
            
            DB::commit();
            return $task;
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in TaskTemplate::cloneToMilestone: ' . $e->getMessage());
            throw $e;
        }
    }
}