<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_milestone_id',
        'title',
        'description',
        'fee_type',
        'pricing_type',
        'estimated_hours',
        'deliverables',
        'checklist_items',
        'order'
    ];

    protected $casts = [
        'deliverables' => 'array',
        'checklist_items' => 'array',
        'estimated_hours' => 'decimal:2',
        'order' => 'integer'
    ];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'project_milestone_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectSubtask::class)->orderBy('order');
    }
}