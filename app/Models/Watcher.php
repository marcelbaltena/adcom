<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Watcher extends Model
{
    use HasFactory;

    protected $fillable = [
        'watchable_type',
        'watchable_id',
        'user_id',
    ];

    /**
     * Get the parent watchable model
     */
    public function watchable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that is watching
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for a specific watchable type
     */
    public function scopeForType($query, string $type)
    {
        return $query->where('watchable_type', $type);
    }

    /**
     * Get display name for the watched item
     */
    public function getWatchableNameAttribute(): string
    {
        if (!$this->watchable) {
            return 'Unknown';
        }

        return match($this->watchable_type) {
            'App\\Models\\Project' => 'Project: ' . $this->watchable->name,
            'App\\Models\\Milestone' => 'Milestone: ' . $this->watchable->title,
            'App\\Models\\Task' => 'Task: ' . $this->watchable->title,
            'App\\Models\\Subtask' => 'Subtask: ' . $this->watchable->title,
            default => class_basename($this->watchable_type) . ': ' . ($this->watchable->name ?? $this->watchable->title ?? 'Item')
        };
    }

    /**
     * Get icon for the watched item type
     */
    public function getWatchableIconAttribute(): string
    {
        return match($this->watchable_type) {
            'App\\Models\\Project' => 'folder',
            'App\\Models\\Milestone' => 'flag',
            'App\\Models\\Task' => 'check-square',
            'App\\Models\\Subtask' => 'check-circle',
            default => 'eye'
        };
    }

    /**
     * Get color for the watched item type
     */
    public function getWatchableColorAttribute(): string
    {
        return match($this->watchable_type) {
            'App\\Models\\Project' => 'blue',
            'App\\Models\\Milestone' => 'purple',
            'App\\Models\\Task' => 'green',
            'App\\Models\\Subtask' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Check if a specific user is watching a specific item
     */
    public static function isWatching($watchable, $userId): bool
    {
        return self::where('watchable_type', get_class($watchable))
            ->where('watchable_id', $watchable->id)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Add a watcher
     */
    public static function addWatcher($watchable, $userId): void
    {
        self::firstOrCreate([
            'watchable_type' => get_class($watchable),
            'watchable_id' => $watchable->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Remove a watcher
     */
    public static function removeWatcher($watchable, $userId): void
    {
        self::where('watchable_type', get_class($watchable))
            ->where('watchable_id', $watchable->id)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Toggle watcher status
     */
    public static function toggleWatcher($watchable, $userId): bool
    {
        if (self::isWatching($watchable, $userId)) {
            self::removeWatcher($watchable, $userId);
            return false;
        } else {
            self::addWatcher($watchable, $userId);
            return true;
        }
    }

    /**
     * Get all watchers for an item
     */
    public static function getWatchers($watchable)
    {
        return User::whereIn('id', 
            self::where('watchable_type', get_class($watchable))
                ->where('watchable_id', $watchable->id)
                ->pluck('user_id')
        )->get();
    }
}