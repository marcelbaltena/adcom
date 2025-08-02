<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assignee extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignable_type',
        'assignable_id',
        'user_id',
        'role'
    ];

    protected $casts = [
        'role' => 'string'
    ];

    // Role options
    public static function getRoleOptions()
    {
        return [
            'owner' => 'Owner',
            'assignee' => 'Assignee', 
            'reviewer' => 'Reviewer'
        ];
    }

    // Relationships
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAssignee(): bool
    {
        return $this->role === 'assignee';
    }

    public function isReviewer(): bool
    {
        return $this->role === 'reviewer';
    }

    // Role badge color
    public function getRoleColorAttribute()
    {
        return match($this->role) {
            'owner' => 'bg-purple-100 text-purple-800',
            'assignee' => 'bg-blue-100 text-blue-800',
            'reviewer' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
