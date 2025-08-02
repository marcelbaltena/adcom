<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'user_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the parent loggable model
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted description
     */
    public function getFormattedDescriptionAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        // Generate description based on action
        $userName = $this->user ? $this->user->name : 'System';
        
        return match($this->action) {
            'created' => "{$userName} created this item",
            'updated' => "{$userName} updated this item",
            'deleted' => "{$userName} deleted this item",
            'status_changed' => "{$userName} changed the status",
            'assigned' => "{$userName} assigned this item",
            'comment_added' => "{$userName} added a comment",
            'attachment_added' => "{$userName} added an attachment",
            'team_member_added' => "{$userName} added a team member",
            'team_member_removed' => "{$userName} removed a team member",
            default => "{$userName} performed {$this->action}"
        };
    }

    /**
     * Get changes array
     */
    public function getChangesAttribute(): array
    {
        $changes = [];
        
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }
        
        return $changes;
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'plus-circle',
            'updated' => 'edit',
            'deleted' => 'trash',
            'status_changed' => 'refresh-cw',
            'assigned' => 'user-plus',
            'comment_added' => 'message-circle',
            'attachment_added' => 'paperclip',
            'team_member_added' => 'user-plus',
            'team_member_removed' => 'user-minus',
            default => 'activity'
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'status_changed' => 'yellow',
            'assigned' => 'purple',
            'comment_added' => 'gray',
            'attachment_added' => 'indigo',
            'team_member_added' => 'green',
            'team_member_removed' => 'red',
            default => 'gray'
        };
    }

    /**
     * Log an activity
     */
    public static function log($loggable, string $action, string $description = null, array $oldValues = [], array $newValues = []): self
    {
        return self::create([
            'loggable_type' => get_class($loggable),
            'loggable_id' => $loggable->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope recent activities
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}