<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\WhatsappConnectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable([
    'user_id',
    'phone_number_id',
    'access_token',
    'recipient_phone',
    'template_name',
    'template_language',
    'is_active',
    'consented_at',
    'opted_out_at',
    'notify_task_created',
    'notify_task_updated',
    'notify_chat_messages',
    'notify_announcements',
    'notify_due_reminders',
    'quiet_hours_enabled',
    'timezone',
    'quiet_hours_start',
    'quiet_hours_end',
    'last_tested_at',
    'last_sent_at',
    'last_message_id',
    'last_error_at',
    'last_error_message',
])]
#[Hidden(['access_token'])]
class WhatsappConnection extends Model
{
    public const EventAnnouncement = 'announcement';

    public const EventChatMessage = 'chat_message';

    public const EventTaskAssigned = 'task_assigned';

    public const EventTaskCreated = 'task_created';

    public const EventTaskDue = 'task_due';

    public const EventTaskMentioned = 'task_mentioned';

    public const EventTaskUpdated = 'task_updated';

    /** @use HasFactory<WhatsappConnectionFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(WhatsappNotificationLog::class);
    }

    public function hasNotificationConsent(): bool
    {
        return $this->consented_at !== null && $this->opted_out_at === null;
    }

    public function wantsNotification(string $event): bool
    {
        if (! $this->is_active || ! $this->hasNotificationConsent()) {
            return false;
        }

        return (bool) $this->{self::preferenceColumn($event)};
    }

    public static function preferenceColumn(string $event): string
    {
        return match ($event) {
            self::EventTaskCreated => 'notify_task_created',
            self::EventTaskUpdated,
            self::EventTaskAssigned,
            self::EventTaskMentioned => 'notify_task_updated',
            self::EventChatMessage => 'notify_chat_messages',
            self::EventAnnouncement => 'notify_announcements',
            self::EventTaskDue => 'notify_due_reminders',
            default => throw new InvalidArgumentException('Jenis notifikasi WhatsApp tidak dikenali.'),
        };
    }

    public function nextDeliveryAt(?CarbonInterface $at = null): CarbonImmutable
    {
        $now = $at ? CarbonImmutable::instance($at) : CarbonImmutable::now();

        if (! $this->quiet_hours_enabled) {
            return $now;
        }

        $localNow = $now->setTimezone($this->timezone);
        [$startHour, $startMinute] = array_map('intval', explode(':', $this->quiet_hours_start));
        [$endHour, $endMinute] = array_map('intval', explode(':', $this->quiet_hours_end));
        $start = $localNow->setTime($startHour, $startMinute);
        $end = $localNow->setTime($endHour, $endMinute);

        if ($start->equalTo($end)) {
            return $now;
        }

        if ($start->lessThan($end)) {
            if ($localNow->greaterThanOrEqualTo($start) && $localNow->lessThan($end)) {
                return $end->utc();
            }

            return $now;
        }

        if ($localNow->greaterThanOrEqualTo($start)) {
            return $end->addDay()->utc();
        }

        if ($localNow->lessThan($end)) {
            return $end->utc();
        }

        return $now;
    }

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'is_active' => 'boolean',
            'consented_at' => 'datetime',
            'opted_out_at' => 'datetime',
            'notify_task_created' => 'boolean',
            'notify_task_updated' => 'boolean',
            'notify_chat_messages' => 'boolean',
            'notify_announcements' => 'boolean',
            'notify_due_reminders' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'last_tested_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'last_error_at' => 'datetime',
        ];
    }
}
