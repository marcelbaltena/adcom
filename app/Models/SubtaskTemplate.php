<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Task;
use App\Models\Subtask;
use Carbon\Carbon;

class SubtaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_template_id',
        'title',
        'description',
        'default_start_date',
        'default_end_date',
        'fee_type',
        'pricing_type',
        'price',
        'hourly_rate',
        'estimated_hours',
        'order'
    ];

    protected $casts = [
        'default_start_date' => 'date',
        'default_end_date' => 'date',
        'price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'order' => 'integer'
    ];

    protected $attributes = [
        'fee_type' => 'in_fee',
        'pricing_type' => 'fixed_price',
        'order' => 0
    ];

    // Relationships
    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    // Calculate price
    public function calculatePrice(): float
    {
        if ($this->pricing_type === 'hourly_rate') {
            return ($this->hourly_rate ?? 0) * ($this->estimated_hours ?? 0);
        }
        
        return $this->price ?? 0;
    }

    // Clone to task
    public function cloneToTask(Task $task): Subtask
    {
        // Bereken datums op basis van task datums
        $startDate = $this->default_start_date ?? $task->start_date ?? now();
        $endDate = $this->default_end_date ?? $task->end_date ?? now()->addDays(7);
        
        // Get current max order for this task
        $maxOrder = $task->subtasks()->max('order') ?? -1;
        $maxPosition = $task->subtasks()->max('position') ?? -1;
        
        // Map fee type if needed
        $feeTypeMapping = [
            'in_fee' => 'in_fee',
            'extended' => 'extended_fee'
        ];
        
        $mappedFeeType = $feeTypeMapping[$this->fee_type] ?? 'in_fee';
        
        return Subtask::create([
            'task_id' => $task->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'fee_type' => $mappedFeeType,
            'pricing_type' => $this->pricing_type,
            'price' => $this->price,
            'hourly_rate' => $this->hourly_rate,
            'estimated_hours' => $this->estimated_hours,
            'budget' => $this->calculatePrice(),
            'spent_amount' => 0,
            'remaining_budget' => $this->calculatePrice(),
            'budget_status' => 'on_track',
            'status' => 'concept',
            'priority' => 'normaal',
            'order' => $maxOrder + 1,
            'position' => $maxPosition + 1,
            'is_completed' => false,
            'completion_percentage' => 0,
            'timeline_status' => 'not_started',
            'actual_hours' => 0,
            'billable_hours' => 0
        ]);
    }
}