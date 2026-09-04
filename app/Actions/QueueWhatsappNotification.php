<?php

namespace App\Actions;

use App\Jobs\SendWhatsappNotification;
use App\Models\Task;
use App\Models\WhatsappConnection;
use App\Models\WhatsappNotificationLog;
use Illuminate\Support\Str;

class QueueWhatsappNotification
{
    public function execute(
        WhatsappConnection $connection,
        string $event,
        string $eventLabel,
        string $subject,
        string $projectName,
        string $url,
        ?Task $task = null,
        ?string $idempotencyKey = null,
    ): bool {
        if (! $connection->wantsNotification($event)) {
            return false;
        }

        $scheduledFor = $connection->nextDeliveryAt();
        $log = WhatsappNotificationLog::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey ?? (string) Str::uuid()],
            [
                'whatsapp_connection_id' => $connection->id,
                'task_id' => $task?->id,
                'event' => $event,
                'event_label' => $eventLabel,
                'subject' => $subject,
                'project_name' => $projectName,
                'url' => $url,
                'status' => WhatsappNotificationLog::StatusPending,
                'scheduled_for' => $scheduledFor,
            ],
        );

        if (! $log->wasRecentlyCreated) {
            return false;
        }

        SendWhatsappNotification::dispatch(
            $connection->id,
            $event,
            $eventLabel,
            $subject,
            $projectName,
            $url,
            $log->id,
        )->delay($scheduledFor)->afterCommit();

        return true;
    }
}
