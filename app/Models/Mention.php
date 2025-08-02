<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends Model
{
    use HasFactory;

    protected $fillable = [
        'mentionable_type',
        'mentionable_id',
        'user_id',
        'mentioned_user_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($mention) {
            // Send notification to mentioned user
            // You can implement notification logic here
        });
    }

    /**
     * Get the parent mentionable model
     */
    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the mention
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mentioned user
     */
    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Get context text
     */
    public function getContextAttribute(): string
    {
        if (!$this->mentionable) {
            return 'Unknown context';
        }

        if ($this->mentionable_type === 'App\\Models\\Comment') {
            $comment = $this->mentionable;
            $parent = $comment->commentable;
            
            if ($parent) {
                return match(get_class($parent)) {
                    'App\\Models\\Project' => "in project '{$parent->name}'",
                    'App\\Models\\Milestone' => "in milestone '{$parent->title}'",
                    'App\\Models\\Task' => "in task '{$parent->title}'",
                    'App\\Models\\Subtask' => "in subtask '{$parent->title}'",
                    default => 'in ' . class_basename(get_class($parent))
                };
            }
        }

        return 'in ' . class_basename($this->mentionable_type);
    }

    /**
     * Get notification message
     */
    public function getNotificationMessageAttribute(): string
    {
        $userName = $this->user ? $this->user->name : 'Someone';
        return "{$userName} mentioned you {$this->context}";
    }

    /**
     * Scope unread mentions
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope read mentions
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for mentioned user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('mentioned_user_id', $userId);
    }

    /**
     * Scope recent mentions
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}