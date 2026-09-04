<?php

namespace App\Models;

use Database\Factories\WhatsappNotificationLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_connection_id',
    'task_id',
    'idempotency_key',
    'event',
    'event_label',
    'subject',
    'project_name',
    'url',
    'status',
    'scheduled_for',
    'sent_at',
    'message_id',
    'error_at',
    'error_message',
])]
class WhatsappNotificationLog extends Model
{
    public const StatusFailed = 'failed';

    public const StatusPending = 'pending';

    public const StatusSent = 'sent';

    public const StatusSkipped = 'skipped';

    /** @use HasFactory<WhatsappNotificationLogFactory> */
    use HasFactory;

    public function connection(): BelongsTo
    {
        return $this->belongsTo(WhatsappConnection::class, 'whatsapp_connection_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
            'error_at' => 'datetime',
        ];
    }
}
