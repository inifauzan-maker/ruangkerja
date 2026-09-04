<?php

namespace App\Actions;

use App\Models\Task;
use App\Models\WhatsappConnection;
use Illuminate\Database\Eloquent\Builder;

class DispatchWhatsappTaskReminders
{
    /** @var list<string> */
    private const TerminalListTitles = ['selesai', 'batal', 'done', 'cancelled', 'canceled'];

    public function __construct(private QueueWhatsappNotification $queueNotification) {}

    public function execute(): int
    {
        $queued = 0;

        WhatsappConnection::query()
            ->where('is_active', true)
            ->where('notify_due_reminders', true)
            ->chunkById(100, function ($connections) use (&$queued): void {
                foreach ($connections as $connection) {
                    $today = now($connection->timezone)->startOfDay();
                    $tomorrow = $today->copy()->addDay();

                    Task::query()
                        ->with('list.board')
                        ->whereHas('assignees', fn (Builder $query): Builder => $query->whereKey($connection->user_id))
                        ->where(function (Builder $query) use ($today, $tomorrow): void {
                            $query->whereDate('due_at', $today->toDateString())
                                ->orWhereDate('due_at', $tomorrow->toDateString());
                        })
                        ->whereHas('list', function (Builder $query): void {
                            $query->whereRaw(
                                'LOWER(title) NOT IN ('.implode(', ', array_fill(0, count(self::TerminalListTitles), '?')).')',
                                self::TerminalListTitles,
                            );
                        })
                        ->orderBy('id')
                        ->each(function (Task $task) use ($connection, $today, &$queued): void {
                            $board = $task->list->board;
                            $isDueToday = $task->due_at->isSameDay($today);
                            $reminderType = $isDueToday ? 'today' : 'tomorrow';
                            $eventLabel = $isDueToday
                                ? 'Tugas jatuh tempo hari ini'
                                : 'Pengingat tugas H-1';
                            $idempotencyKey = implode(':', [
                                'task_due',
                                $reminderType,
                                $task->id,
                                $connection->id,
                                $task->due_at->toDateString(),
                            ]);

                            if ($this->queueNotification->execute(
                                $connection,
                                WhatsappConnection::EventTaskDue,
                                $eventLabel,
                                $task->title,
                                $board->name,
                                route('boards.tasks.show', [$board, $task]),
                                $task,
                                $idempotencyKey,
                            )) {
                                $queued++;
                            }
                        });
                }
            });

        return $queued;
    }
}
