<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'trackable_type', 'trackable_id', 'user_id', 'date',
        'start_time', 'end_time', 'hours', 'billable',
        'description', 'internal_notes', 'hourly_rate', 'total_cost',
        'status', 'approved_by', 'approved_at', 'invoiced',
        'invoice_id', 'tags'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'billable' => 'boolean',
        'invoiced' => 'boolean',
        'approved_at' => 'datetime',
        'tags' => 'array'
    ];

    protected $attributes = [
        'billable' => true,
        'status' => 'draft',
        'invoiced' => false
    ];

    // Relationships
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Calculations
    public function calculateTotalCost(): float
    {
        return $this->hours * $this->hourly_rate;
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

    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'blue',
            'approved' => 'green',
            'billed' => 'purple',
            'rejected' => 'red',
            default => 'gray'
        };
    }
}
