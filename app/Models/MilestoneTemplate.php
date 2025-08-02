<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MilestoneTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_template_id',
        'project_template_id',  // Voor project templates
        'title',
        'description',
        'default_start_date',
        'default_end_date',
        'days_from_start',      // Voor project template planning
        'duration_days',        // Voor project template planning
        'fee_type',
        'pricing_type',
        'price',
        'hourly_rate',
        'estimated_hours',
        'deliverables',
        'order'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deliverables' => 'array',
        'default_start_date' => 'date',
        'default_end_date' => 'date',
        'price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'days_from_start' => 'integer',
        'duration_days' => 'integer',
        'order' => 'integer'
    ];

    /**
     * Get the service template that owns the milestone template.
     */
    public function serviceTemplate(): BelongsTo
    {
        return $this->belongsTo(ServiceTemplate::class);
    }

    /**
     * Get the project template that owns the milestone template.
     */
    public function projectTemplate(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class);
    }

    /**
     * Get the task templates for the milestone template.
     */
    public function taskTemplates(): HasMany
    {
        return $this->hasMany(TaskTemplate::class)->orderBy('order');
    }

    /**
     * Calculate the price based on pricing type
     */
    public function calculatePrice(): float
    {
        if ($this->pricing_type === 'hourly_rate') {
            return ($this->hourly_rate ?? 0) * ($this->estimated_hours ?? 0);
        }
        
        return $this->price ?? 0;
    }

    /**
     * Get total estimated hours including all tasks and subtasks
     */
    public function getTotalEstimatedHours(): float
    {
        $milestoneHours = $this->estimated_hours ?? 0;
        
        $taskHours = $this->taskTemplates->sum(function ($task) {
            $taskHours = $task->estimated_hours ?? 0;
            $subtaskHours = $task->subtaskTemplates->sum('estimated_hours') ?? 0;
            return $taskHours + $subtaskHours;
        });
        
        return $milestoneHours + $taskHours;
    }

    /**
     * Check if this milestone belongs to a project template
     */
    public function isProjectTemplate(): bool
    {
        return !is_null($this->project_template_id);
    }

    /**
     * Check if this milestone belongs to a service template
     */
    public function isServiceTemplate(): bool
    {
        return !is_null($this->service_template_id);
    }

    /**
     * Get the parent template (either service or project)
     */
    public function getParentTemplate()
    {
        if ($this->isProjectTemplate()) {
            return $this->projectTemplate;
        }
        
        return $this->serviceTemplate;
    }

    /**
     * Scope a query to only include milestones for project templates
     */
    public function scopeForProjectTemplates($query)
    {
        return $query->whereNotNull('project_template_id');
    }

    /**
     * Scope a query to only include milestones for service templates
     */
    public function scopeForServiceTemplates($query)
    {
        return $query->whereNotNull('service_template_id');
    }
}