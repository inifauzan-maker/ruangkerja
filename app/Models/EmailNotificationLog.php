<?php

namespace App\Models;

use Database\Factories\EmailNotificationLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['team_id', 'sender_id', 'recipient', 'event', 'subject', 'status', 'sent_at', 'error_at', 'error_message'])]
class EmailNotificationLog extends Model
{
    /** @use HasFactory<EmailNotificationLogFactory> */
    use HasFactory;

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'error_at' => 'datetime',
        ];
    }
}
