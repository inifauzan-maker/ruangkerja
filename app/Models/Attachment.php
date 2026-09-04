<?php

namespace App\Models;

use Database\Factories\AttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['root_attachment_id', 'uploader_id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'version', 'is_current', 'scan_status', 'scanned_at'])]
class Attachment extends Model
{
    /** @use HasFactory<AttachmentFactory> */
    use HasFactory;

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_attachment_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'root_attachment_id')->orderByDesc('version');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function familyRootId(): int
    {
        return $this->root_attachment_id ?? $this->id;
    }

    public function isInlinePreviewable(): bool
    {
        return str_starts_with($this->mime_type, 'image/')
            || $this->mime_type === 'application/pdf'
            || str_starts_with($this->mime_type, 'text/');
    }

    public function formattedSize(): string
    {
        if ($this->size >= 1_048_576) {
            return number_format($this->size / 1_048_576, 1).' MB';
        }

        return number_format(max($this->size, 1) / 1024, 0).' KB';
    }

    protected static function booted(): void
    {
        static::deleted(function (Attachment $attachment): void {
            Storage::disk($attachment->disk)->delete($attachment->path);
        });
    }

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'version' => 'integer',
            'is_current' => 'boolean',
            'scanned_at' => 'datetime',
        ];
    }
}
