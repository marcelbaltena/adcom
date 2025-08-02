<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_template_id',
        'title',
        'description',
        'days_from_start',
        'duration_days',
        'fee_type',
        'pricing_type',
        'estimated_hours',
        'deliverables',
        'order'
    ];

    protected $casts = [
        'deliverables' => 'array',
        'days_from_start' => 'integer',
        'duration_days' => 'integer',
        'estimated_hours' => 'decimal:2',
        'order' => 'integer'
    ];

    public function projectTemplate(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->orderBy('order');
    }

    public function getTotalEstimatedHours(): float
    {
        $milestoneHours = $this->estimated_hours ?? 0;
        
        $taskHours = $this->tasks->sum(function ($task) {
            $taskHours = $task->estimated_hours ?? 0;
            $subtaskHours = $task->subtasks->sum('estimated_hours') ?? 0;
            return $taskHours + $subtaskHours;
        });
        
        return $milestoneHours + $taskHours;
    }
}