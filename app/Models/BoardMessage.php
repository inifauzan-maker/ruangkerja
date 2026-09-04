<?php

namespace App\Models;

use App\Models\Concerns\HasAttachments;
use Database\Factories\BoardMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['board_id', 'user_id', 'body'])]
class BoardMessage extends Model
{
    /** @use HasFactory<BoardMessageFactory> */
    use HasAttachments, HasFactory;

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
