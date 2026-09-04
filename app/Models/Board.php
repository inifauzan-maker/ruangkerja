<?php

namespace App\Models;

use Database\Factories\BoardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'name', 'description', 'download_permission'])]
class Board extends Model
{
    /** @use HasFactory<BoardFactory> */
    use HasFactory;

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(BoardList::class)->orderBy('position')->orderBy('id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BoardMessage::class)->latest();
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class)
            ->orderByDesc('is_pinned')
            ->latest();
    }

    public function belongsToUser(User $user): bool
    {
        return $this->team()
            ->where(function ($query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->whereKey($user->id));
            })
            ->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (Board $board): void {
            $board->lists()->with('tasks.attachments')->get()->flatMap->tasks->each->delete();
            $board->messages()->with('attachments')->get()->each->delete();
            $board->announcements()->with('attachments')->get()->each->delete();
        });
    }
}
