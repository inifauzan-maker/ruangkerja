<?php

namespace App\Models;

use App\Models\Concerns\HasAttachments;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['board_list_id', 'creator_id', 'title', 'description', 'priority', 'due_at', 'position'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasAttachments, HasFactory;

    public function list(): BelongsTo
    {
        return $this->belongsTo(BoardList::class, 'board_list_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('position')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }

    public function recordActivity(?User $actor, string $type, array $metadata = []): TaskActivity
    {
        return $this->activities()->create([
            'actor_id' => $actor?->id,
            'type' => $type,
            'metadata' => $metadata,
        ]);
    }

    protected function casts(): array
    {
        return ['due_at' => 'date'];
    }
}
