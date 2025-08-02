<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BudgetAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'allocatable_type', 'allocatable_id', 'allocated_amount',
        'spent_amount', 'committed_amount', 'allocation_date',
        'allocation_type', 'allocation_reason', 'status',
        'created_by', 'approved_by', 'approved_at',
        'metadata', 'previous_amount'
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'committed_amount' => 'decimal:2',
        'previous_amount' => 'decimal:2',
        'allocation_date' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected $attributes = [
        'status' => 'auto_approved',
        'allocation_type' => 'initial',
        'spent_amount' => 0,
        'committed_amount' => 0
    ];

    // Relationships
    public function allocatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper Methods
    public function approve(User $approver): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now()
        ]);
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->allocated_amount - $this->spent_amount - $this->committed_amount;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'auto_approved' => 'blue',
            'rejected' => 'red',
            default => 'gray'
        };
    }
}