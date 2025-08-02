<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'user_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'disk',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            // Delete file from storage when attachment is deleted
            $attachment->deleteFile();
        });
    }

    /**
     * Get the parent attachable model
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the attachment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get file URL
     */
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return Storage::disk($this->disk)->url($this->path);
        }

        // For private files, generate a temporary URL
        return route('attachments.download', $this);
    }

    /**
     * Get human readable file size
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file extension
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a PDF
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if file is a document
     */
    public function getIsDocumentAttribute(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ];

        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Get file icon based on type
     */
    public function getIconAttribute(): string
    {
        if ($this->is_image) return 'image';
        if ($this->is_pdf) return 'file-text';
        if ($this->is_document) return 'file-text';
        
        return match($this->extension) {
            'zip', 'rar', '7z' => 'archive',
            'mp4', 'avi', 'mov' => 'video',
            'mp3', 'wav', 'ogg' => 'music',
            default => 'file'
        };
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }

    /**
     * Download file
     */
    public function download()
    {
        return Storage::disk($this->disk)->download($this->path, $this->original_filename);
    }

    /**
     * Get file contents
     */
    public function getContents(): string
    {
        return Storage::disk($this->disk)->get($this->path);
    }

    /**
     * Check if user can download
     */
    public function canBeDownloadedBy(User $user): bool
    {
        // User who uploaded can always download
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check if user has access to parent item
        if ($this->attachable) {
            if (method_exists($this->attachable, 'canBeViewedBy')) {
                return $this->attachable->canBeViewedBy($user);
            }
        }

        // Admins can download everything
        return $user->role === 'admin';
    }

    /**
     * Scope by file type
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope documents
     */
    public function scopeDocuments($query)
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ];

        return $query->whereIn('mime_type', $documentTypes);
    }

    /**
     * Scope by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}