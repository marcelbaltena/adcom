<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'is_active',
        'usage_count',
        'total_days',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'total_days' => 'integer'
    ];

    /**
     * Get the milestones for this project template
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('order');
    }

    /**
     * Get the user who created this template
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get total estimated hours for the template
     */
    public function getTotalEstimatedHours(): float
    {
        $total = 0;
        
        foreach ($this->milestones as $milestone) {
            $total += $milestone->getTotalEstimatedHours();
        }
        
        return $total;
    }

    /**
 * Clone this template to a project with a given start date
 */
public function cloneToProject(Project $project, Carbon $startDate)
{
    \DB::beginTransaction();
    
    try {
        // Increment usage count
        $this->increment('usage_count');
        
        // Calculate project end date based on template total days
        $projectEndDate = $startDate->copy()->addDays($this->total_days);
        
        // Update project end date if not set or if template end date is later
        if (!$project->end_date || $projectEndDate->gt($project->end_date)) {
            $project->update(['end_date' => $projectEndDate]);
            \Log::info('Updated project end date to: ' . $projectEndDate->format('Y-m-d'));
        }
        
        foreach ($this->milestones as $milestoneTemplate) {
            // Calculate milestone dates based on days_from_start
            $milestoneStartDate = $startDate->copy()->addDays($milestoneTemplate->days_from_start);
            $milestoneEndDate = $milestoneStartDate->copy()->addDays($milestoneTemplate->duration_days - 1);
            
            // Create milestone without prices
            $milestone = Milestone::create([
                'project_id' => $project->id,
                'title' => $milestoneTemplate->title,
                'description' => $milestoneTemplate->description,
                'start_date' => $milestoneStartDate,
                'end_date' => $milestoneEndDate,
                'due_date' => $milestoneEndDate,
                'fee_type' => $milestoneTemplate->fee_type,
                'pricing_type' => $milestoneTemplate->pricing_type,
                'budget' => 0, // Geen prijzen kopiëren
                'price' => 0,
                'hourly_rate' => 0,
                'estimated_hours' => $milestoneTemplate->estimated_hours ?? 0,
                'status' => 'concept',
                'priority' => 'normaal',
                'order' => $milestoneTemplate->order,
                'deliverables' => $milestoneTemplate->deliverables
            ]);
            
            // Clone tasks
            foreach ($milestoneTemplate->tasks as $taskTemplate) {
                $task = Task::create([
                    'milestone_id' => $milestone->id,
                    'project_id' => $project->id,
                    'title' => $taskTemplate->title,
                    'description' => $taskTemplate->description,
                    'start_date' => $milestoneStartDate,
                    'end_date' => $milestoneEndDate,
                    'fee_type' => $taskTemplate->fee_type,
                    'pricing_type' => $taskTemplate->pricing_type,
                    'budget' => 0,
                    'price' => 0,
                    'hourly_rate' => 0,
                    'estimated_hours' => $taskTemplate->estimated_hours ?? 0,
                    'status' => 'concept',
                    'priority' => 'normaal',
                    'order' => $taskTemplate->order,
                    'deliverables' => $taskTemplate->deliverables,
                    'checklist_items' => $taskTemplate->checklist_items
                ]);
                
                // Clone subtasks
                foreach ($taskTemplate->subtasks as $subtaskTemplate) {
                    Subtask::create([
                        'task_id' => $task->id,
                        'title' => $subtaskTemplate->title,
                        'description' => $subtaskTemplate->description,
                        'start_date' => $milestoneStartDate,
                        'end_date' => $milestoneEndDate,
                        'fee_type' => $subtaskTemplate->fee_type,
                        'pricing_type' => $subtaskTemplate->pricing_type,
                        'budget' => 0,
                        'price' => 0,
                        'hourly_rate' => 0,
                        'estimated_hours' => $subtaskTemplate->estimated_hours ?? 0,
                        'status' => 'concept',
                        'priority' => 'normaal',
                        'order' => $subtaskTemplate->order
                    ]);
                }
            }
        }
        
        \DB::commit();
        
        return true;
        
    } catch (\Exception $e) {
        \DB::rollback();
        throw $e;
    }
}

}