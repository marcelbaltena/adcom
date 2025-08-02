<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'content',
        'is_internal',
        'mentions',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'mentions' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($comment) {
            // Process mentions
            $comment->processMentions();
            
            // Log activity
            if ($comment->commentable) {
                $comment->commentable->logActivity(
                    'comment_added',
                    "Comment added by {$comment->user->name}"
                );
            }
        });
    }

    /**
     * Get the parent commentable model
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get mentions for this comment
     */
    public function mentionRecords(): HasMany
    {
        return $this->morphMany(Mention::class, 'mentionable');
    }

    /**
     * Process mentions in the comment content
     */
    public function processMentions(): void
    {
        // Find all @mentions in the content
        preg_match_all('/@(\w+)/', $this->content, $matches);
        
        if (empty($matches[1])) {
            return;
        }

        $mentionedUsernames = array_unique($matches[1]);
        $mentionedUserIds = [];

        foreach ($mentionedUsernames as $username) {
            // Try to find user by username (name without spaces)
            $user = User::whereRaw('LOWER(REPLACE(name, " ", "")) = ?', [strtolower($username)])->first();
            
            if ($user && $user->id !== $this->user_id) {
                $mentionedUserIds[] = $user->id;
                
                // Create mention record
                Mention::create([
                    'mentionable_type' => get_class($this),
                    'mentionable_id' => $this->id,
                    'user_id' => $this->user_id,
                    'mentioned_user_id' => $user->id,
                ]);
            }
        }

        // Update mentions array
        if (!empty($mentionedUserIds)) {
            $this->update(['mentions' => $mentionedUserIds]);
        }
    }

    /**
     * Get formatted content with mentions highlighted
     */
    public function getFormattedContentAttribute(): string
    {
        $content = e($this->content);
        
        // Replace @mentions with links
        $content = preg_replace_callback('/@(\w+)/', function ($matches) {
            $username = $matches[1];
            $user = User::whereRaw('LOWER(REPLACE(name, " ", "")) = ?', [strtolower($username)])->first();
            
            if ($user) {
                return '<a href="' . route('users.show', $user) . '" class="text-blue-600 font-medium">@' . e($user->name) . '</a>';
            }
            
            return $matches[0];
        }, $content);
        
        // Convert line breaks to <br>
        $content = nl2br($content);
        
        return $content;
    }

    /**
     * Get parent item name
     */
    public function getParentNameAttribute(): string
    {
        if (!$this->commentable) {
            return 'Unknown';
        }

        return match($this->commentable_type) {
            'App\\Models\\Project' => 'Project: ' . $this->commentable->name,
            'App\\Models\\Milestone' => 'Milestone: ' . $this->commentable->title,
            'App\\Models\\Task' => 'Task: ' . $this->commentable->title,
            'App\\Models\\Subtask' => 'Subtask: ' . $this->commentable->title,
            default => class_basename($this->commentable_type) . ': ' . ($this->commentable->name ?? $this->commentable->title ?? 'Item')
        };
    }

    /**
     * Check if comment can be edited by user
     */
    public function canBeEditedBy(User $user): bool
    {
        // Author can edit within 15 minutes
        if ($this->user_id === $user->id && $this->created_at->diffInMinutes(now()) < 15) {
            return true;
        }

        // Admins can always edit
        return $user->role === 'admin';
    }

    /**
     * Check if comment can be deleted by user
     */
    public function canBeDeletedBy(User $user): bool
    {
        // Author can delete their own comments
        if ($this->user_id === $user->id) {
            return true;
        }

        // Admins and project managers can delete
        if ($user->role === 'admin') {
            return true;
        }

        // Check if user is project manager
        if ($this->commentable_type === 'App\\Models\\Project') {
            return $this->commentable->isProjectManager($user);
        }

        return false;
    }

    /**
     * Scope visible comments for user
     */
    public function scopeVisibleTo($query, User $user)
    {
        // Admin sees all
        if ($user->role === 'admin') {
            return $query;
        }

        // Others don't see internal comments unless they're managers
        if (!$user->isManager()) {
            return $query->where('is_internal', false);
        }

        return $query;
    }

    /**
     * Scope internal comments
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope public comments
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }
}