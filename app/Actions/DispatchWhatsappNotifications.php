<?php

namespace App\Actions;

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\WhatsappConnection;

class DispatchWhatsappNotifications
{
    public function __construct(private QueueWhatsappNotification $queueNotification) {}

    /**
     * @param  iterable<int, int>|null  $recipientUserIds
     */
    public function execute(
        Team $team,
        User $actor,
        string $event,
        string $eventLabel,
        string $subject,
        string $projectName,
        string $url,
        ?Task $task = null,
        ?iterable $recipientUserIds = null,
    ): void {
        $preferenceColumn = WhatsappConnection::preferenceColumn($event);
        $eligibleUserIds = $team->members()
            ->pluck('users.id')
            ->push($team->owner_id)
            ->unique();

        if ($recipientUserIds !== null) {
            $requestedUserIds = collect($recipientUserIds)
                ->map(fn (mixed $userId): int => (int) $userId)
                ->unique();
            $eligibleUserIds = $eligibleUserIds->intersect($requestedUserIds);
        }

        if ($eligibleUserIds->isEmpty()) {
            return;
        }

        WhatsappConnection::query()
            ->where('is_active', true)
            ->where($preferenceColumn, true)
            ->whereIn('user_id', $eligibleUserIds)
            ->where('user_id', '!=', $actor->id)
            ->get()
            ->each(fn (WhatsappConnection $connection) => $this->queueNotification->execute(
                $connection,
                $event,
                $eventLabel,
                $subject,
                $projectName,
                $url,
                $task,
            ));
    }
}
