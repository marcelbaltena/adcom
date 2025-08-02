<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Watcher;
use App\Models\Comment;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Mention;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

trait HasTeamFeatures
{
    /**
     * Boot the trait
     */
    public static function bootHasTeamFeatures()
    {
        // When model is created, add creator as watcher
        static::created(function ($model) {
            if (auth()->check() && $model->notify_watchers) {
                $model->addWatcher(auth()->user());
            }
        });

        // When model is deleted, clean up related records
        static::deleting(function ($model) {
            // Delete all related polymorphic records
            $model->assignees()->detach();
            $model->watchers()->detach();
            $model->comments()->delete();
            $model->activityLogs()->delete();
            $model->attachments()->each(function ($attachment) {
                $attachment->delete(); // This will also delete the file
            });
        });
    }

    /**
     * Get all assignees for this model
     */
    public function assignees(): MorphToMany
    {
        return $this->morphToMany(User::class, 'assignable', 'assignees')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Get all watchers for this model
     */
    public function watchers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'watchable', 'watchers')
                    ->withTimestamps();
    }

    /**
     * Get all comments for this model
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get all activity logs for this model
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get all attachments for this model
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get all mentions related to this model (through comments)
     */
    public function mentions()
    {
        return Mention::whereHas('mentionable', function ($query) {
            $query->where('commentable_type', get_class($this))
                  ->where('commentable_id', $this->id);
        });
    }

    // ===========================
    // ASSIGNEE METHODS
    // ===========================

    /**
     * Assign a user to this model
     */
    public function assignUser(User $user, string $role = 'assignee'): self
    {
        if (!$this->isAssignedTo($user)) {
            $this->assignees()->attach($user->id, ['role' => $role]);
            
            // Add as watcher too
            $this->addWatcher($user);
            
            // Log activity
            $this->logActivity('user_assigned', "{$user->name} was assigned as {$role}");
            
            // Update last activity
            if ($this->hasAttribute('last_activity_at')) {
                $this->update(['last_activity_at' => now()]);
            }
        }

        return $this;
    }

    /**
     * Unassign a user from this model
     */
    public function unassignUser(User $user): self
    {
        $this->assignees()->detach($user->id);
        
        // Log activity
        $this->logActivity('user_unassigned', "{$user->name} was unassigned");
        
        // Update last activity
        if ($this->hasAttribute('last_activity_at')) {
            $this->update(['last_activity_at' => now()]);
        }

        return $this;
    }

    /**
     * Update user's role in assignment
     */
    public function updateAssigneeRole(User $user, string $role): self
    {
        $this->assignees()->updateExistingPivot($user->id, ['role' => $role]);
        
        // Log activity
        $this->logActivity('role_updated', "{$user->name}'s role updated to {$role}");

        return $this;
    }

    /**
     * Check if a user is assigned to this model
     */
    public function isAssignedTo(User $user): bool
    {
        // Check new assignment system
        if ($this->assignees()->where('user_id', $user->id)->exists()) {
            return true;
        }
        
        // Check legacy assigned_to field
        if ($this->hasAttribute('assigned_to') && $this->assigned_to == $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Get assignee by role
     */
    public function getAssigneeByRole(string $role)
    {
        return $this->assignees()->wherePivot('role', $role)->first();
    }

    /**
     * Get all assignees with a specific role
     */
    public function getAssigneesByRole(string $role)
    {
        return $this->assignees()->wherePivot('role', $role)->get();
    }

    /**
     * Sync assignees (replace all current assignees)
     */
    public function syncAssignees(array $userIds, string $defaultRole = 'assignee'): self
    {
        $sync = [];
        foreach ($userIds as $userId) {
            $sync[$userId] = ['role' => $defaultRole];
        }
        
        $this->assignees()->sync($sync);
        
        // Add all as watchers
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->addWatcher($user);
            }
        }

        return $this;
    }

    // ===========================
    // WATCHER METHODS
    // ===========================

    /**
     * Add a watcher to this model
     */
    public function addWatcher(User $user): self
    {
        if (!$this->isWatchedBy($user)) {
            $this->watchers()->attach($user->id);
            
            // Log activity
            $this->logActivity('watcher_added', "{$user->name} started watching");
        }

        return $this;
    }

    /**
     * Remove a watcher from this model
     */
    public function removeWatcher(User $user): self
    {
        $this->watchers()->detach($user->id);
        
        // Log activity
        $this->logActivity('watcher_removed', "{$user->name} stopped watching");

        return $this;
    }

    /**
     * Check if a user is watching this model
     */
    public function isWatchedBy(User $user): bool
    {
        return $this->watchers()->where('user_id', $user->id)->exists();
    }

    /**
     * Toggle watcher status for a user
     */
    public function toggleWatcher(User $user): bool
    {
        if ($this->isWatchedBy($user)) {
            $this->removeWatcher($user);
            return false;
        } else {
            $this->addWatcher($user);
            return true;
        }
    }

    /**
     * Get users to notify (assignees + watchers)
     */
    public function getUsersToNotify(): \Illuminate\Support\Collection
    {
        $assigneeIds = $this->assignees()->pluck('users.id');
        $watcherIds = $this->watchers()->pluck('users.id');
        
        $allIds = $assigneeIds->merge($watcherIds)->unique();
        
        return User::whereIn('id', $allIds)->get();
    }

    // ===========================
    // COMMENT METHODS
    // ===========================

    /**
     * Add a comment to this model
     */
    public function addComment(string $content, ?User $user = null, bool $isInternal = false): Comment
    {
        $user = $user ?? auth()->user();
        
        $comment = $this->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
            'is_internal' => $isInternal,
        ]);
        
        // Update last activity
        if ($this->hasAttribute('last_activity_at')) {
            $this->update(['last_activity_at' => now()]);
        }

        return $comment;
    }

    /**
     * Get latest comments
     */
    public function getLatestComments(int $limit = 5)
    {
        return $this->comments()
                    ->with('user')
                    ->latest()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get comment count
     */
    public function getCommentCountAttribute(): int
    {
        return $this->comments()->count();
    }

    /**
     * Check if user can comment
     */
    public function canUserComment(User $user): bool
    {
        // Check if comments are allowed
        if ($this->hasAttribute('allow_comments') && !$this->allow_comments) {
            return false;
        }
        
        // Check if user has access to the parent model
        if (method_exists($this, 'canBeViewedBy')) {
            return $this->canBeViewedBy($user);
        }
        
        return true;
    }

    // ===========================
    // ACTIVITY LOG METHODS
    // ===========================

    /**
     * Log an activity for this model
     */
    public function logActivity(string $action, string $description = null, array $oldValues = [], array $newValues = []): ActivityLog
    {
        $activity = $this->activityLogs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $activity;
    }

    /**
     * Get latest activities
     */
    public function getLatestActivities(int $limit = 10)
    {
        return $this->activityLogs()
                    ->with('user')
                    ->latest()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get activity count
     */
    public function getActivityCountAttribute(): int
    {
        return $this->activityLogs()->count();
    }

    // ===========================
    // ATTACHMENT METHODS
    // ===========================

    /**
     * Add an attachment to this model
     */
    public function addAttachment($file, ?User $user = null): Attachment
    {
        $user = $user ?? auth()->user();
        
        // Store file
        $path = $file->store('attachments/' . class_basename($this) . '/' . $this->id, 'private');
        
        $attachment = $this->attachments()->create([
            'user_id' => $user->id,
            'filename' => basename($path),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => 'private',
        ]);
        
        // Log activity
        $this->logActivity('attachment_added', "File '{$attachment->original_filename}' was attached");
        
        // Update last activity
        if ($this->hasAttribute('last_activity_at')) {
            $this->update(['last_activity_at' => now()]);
        }

        return $attachment;
    }

    /**
     * Get attachment count
     */
    public function getAttachmentCountAttribute(): int
    {
        return $this->attachments()->count();
    }

    /**
     * Get total size of all attachments
     */
    public function getTotalAttachmentSizeAttribute(): int
    {
        return $this->attachments()->sum('size');
    }

    /**
     * Get human readable total attachment size
     */
    public function getHumanTotalAttachmentSizeAttribute(): string
    {
        $bytes = $this->total_attachment_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // ===========================
    // NOTIFICATION METHODS
    // ===========================

    /**
     * Notify watchers about an event
     */
    public function notifyWatchers(string $event, array $data = []): void
    {
        if (!$this->hasAttribute('notify_watchers') || !$this->notify_watchers) {
            return;
        }

        $usersToNotify = $this->getUsersToNotify();
        
        foreach ($usersToNotify as $user) {
            // Skip the current user
            if ($user->id === auth()->id()) {
                continue;
            }
            
            // Check user notification preferences
            if ($user->prefersNotification($event)) {
                // Here you would send the actual notification
                // For example: $user->notify(new ModelUpdatedNotification($this, $event, $data));
            }
        }
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    /**
     * Get all team members (assignees + watchers)
     */
    public function getTeamMembersAttribute(): \Illuminate\Support\Collection
    {
        return $this->getUsersToNotify();
    }

    /**
     * Get team member count
     */
    public function getTeamMemberCountAttribute(): int
    {
        return $this->team_members->count();
    }

    /**
     * Check if model has any team features data
     */
    public function hasTeamActivity(): bool
    {
        return $this->assignees()->exists() ||
               $this->watchers()->exists() ||
               $this->comments()->exists() ||
               $this->attachments()->exists();
    }

    /**
     * Get summary of team features
     */
    public function getTeamFeatureSummary(): array
    {
        return [
            'assignees' => $this->assignees()->count(),
            'watchers' => $this->watchers()->count(),
            'comments' => $this->comments()->count(),
            'attachments' => $this->attachments()->count(),
            'activities' => $this->activityLogs()->count(),
            'last_activity' => $this->hasAttribute('last_activity_at') ? $this->last_activity_at : null,
        ];
    }

    /**
     * Copy team features to another model
     */
    public function copyTeamFeaturesTo($targetModel): void
    {
        // Copy assignees
        foreach ($this->assignees as $assignee) {
            $targetModel->assignUser($assignee, $assignee->pivot->role);
        }
        
        // Copy watchers
        foreach ($this->watchers as $watcher) {
            $targetModel->addWatcher($watcher);
        }
        
        // Note: We don't copy comments, activities, or attachments as they are unique to each item
    }

    /**
     * Clear all team features
     */
    public function clearTeamFeatures(): void
    {
        DB::transaction(function () {
            $this->assignees()->detach();
            $this->watchers()->detach();
            $this->comments()->delete();
            $this->activityLogs()->delete();
            $this->attachments()->each(function ($attachment) {
                $attachment->delete();
            });
        });
    }
}