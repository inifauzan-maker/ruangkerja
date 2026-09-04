<?php

namespace App\Models;

use App\Models\Concerns\HasAttachments;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['board_id', 'author_id', 'title', 'body', 'is_pinned'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasAttachments, HasFactory;

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean'];
    }
}
