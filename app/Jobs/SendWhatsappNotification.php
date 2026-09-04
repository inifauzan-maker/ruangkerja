<?php

namespace App\Jobs;

use App\Actions\SendWhatsappMessage;
use App\Models\WhatsappConnection;
use App\Models\WhatsappNotificationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendWhatsappNotification implements ShouldQueue
{
    use Queueable;

    public int $timeout = 20;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public int $connectionId,
        public string $event,
        public string $eventLabel,
        public string $subject,
        public string $projectName,
        public string $url,
        public ?int $notificationLogId = null,
    ) {}

    public function handle(SendWhatsappMessage $sendWhatsappMessage): void
    {
        $connection = WhatsappConnection::query()->find($this->connectionId);
        $log = $this->notificationLogId
            ? WhatsappNotificationLog::query()->find($this->notificationLogId)
            : null;

        if (! $connection || ! $connection->wantsNotification($this->event)) {
            $log?->update(['status' => WhatsappNotificationLog::StatusSkipped]);

            return;
        }

        try {
            $messageId = $sendWhatsappMessage->execute($connection, [
                'event' => $this->eventLabel,
                'subject' => $this->subject,
                'project' => $this->projectName,
                'url' => $this->url,
            ]);

            $connection->update([
                'last_sent_at' => now(),
                'last_message_id' => $messageId,
                'last_error_at' => null,
                'last_error_message' => null,
            ]);
            $log?->update([
                'status' => WhatsappNotificationLog::StatusSent,
                'sent_at' => now(),
                'message_id' => $messageId,
                'error_at' => null,
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $connection->update([
                'last_error_at' => now(),
                'last_error_message' => $exception->getMessage(),
            ]);
            $log?->update([
                'status' => WhatsappNotificationLog::StatusFailed,
                'error_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        if (! $this->notificationLogId) {
            return;
        }

        WhatsappNotificationLog::query()
            ->whereKey($this->notificationLogId)
            ->update([
                'status' => WhatsappNotificationLog::StatusFailed,
                'error_at' => now(),
                'error_message' => $exception?->getMessage() ?? 'Job pengiriman WhatsApp gagal.',
            ]);
    }
}
