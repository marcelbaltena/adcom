<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_task_id',
        'title',
        'description',
        'fee_type',
        'pricing_type',
        'estimated_hours',
        'order'
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'order' => 'integer'
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }
}