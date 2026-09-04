<?php

namespace App\Actions;

use App\Models\Board;
use App\Models\EmailNotificationLog;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BuildReportData
{
    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, ?int $boardId = null, int $days = 30): array
    {
        $availableBoards = Board::query()
            ->whereHas('team', function (Builder $query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', fn (Builder $memberQuery) => $memberQuery->whereKey($user->id));
            })
            ->with(['team.owner:id,name', 'team.members:id,name'])
            ->orderBy('name')
            ->get();

        abort_if($boardId !== null && ! $availableBoards->contains('id', $boardId), 404);

        $boards = $boardId === null
            ? $availableBoards
            : $availableBoards->where('id', $boardId)->values();
        $boardIds = $boards->pluck('id');
        $tasks = Task::query()
            ->with(['list.board:id,name,team_id', 'assignees:id,name'])
            ->whereHas('list', fn (Builder $query) => $query->whereIn('board_id', $boardIds))
            ->get();

        $isCompleted = fn (Task $task): bool => Str::lower($task->list->title) === 'selesai';
        $isCancelled = fn (Task $task): bool => Str::lower($task->list->title) === 'batal';
        $completed = $tasks->filter($isCompleted);
        $overdue = $tasks->filter(fn (Task $task): bool => ! $isCompleted($task) && ! $isCancelled($task) && $task->due_at?->lt(today()) === true);
        $inProgress = $tasks->reject(fn (Task $task): bool => $isCompleted($task) || $isCancelled($task));

        $members = $boards->flatMap(fn (Board $board) => collect([$board->team->owner])->merge($board->team->members))
            ->unique('id')
            ->sortBy('name')
            ->values();

        $workload = $members->map(function (User $member) use ($tasks, $isCompleted, $isCancelled): array {
            $assigned = $tasks->filter(fn (Task $task): bool => $task->assignees->contains('id', $member->id));

            return [
                'id' => $member->id,
                'name' => $member->name,
                'total' => $assigned->count(),
                'completed' => $assigned->filter($isCompleted)->count(),
                'active' => $assigned->reject(fn (Task $task): bool => $isCompleted($task) || $isCancelled($task))->count(),
                'overdue' => $assigned->filter(fn (Task $task): bool => ! $isCompleted($task) && $task->due_at?->lt(today()) === true)->count(),
            ];
        })->sortByDesc('active')->values();

        $since = now()->subDays($days);
        $activities = TaskActivity::query()
            ->with(['actor:id,name', 'task.list.board:id,name'])
            ->whereHas('task.list', fn (Builder $query) => $query->whereIn('board_id', $boardIds))
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(100)
            ->get();

        $activityByProject = $boards->map(function (Board $board) use ($activities): array {
            $projectActivities = $activities->filter(fn (TaskActivity $activity): bool => $activity->task->list->board_id === $board->id);

            return [
                'board_id' => $board->id,
                'name' => $board->name,
                'count' => $projectActivities->count(),
                'latest_at' => $projectActivities->first()?->created_at,
            ];
        })->sortByDesc('count')->values();

        $whatsappLogs = WhatsappNotificationLog::query()
            ->whereHas('connection', fn (Builder $query) => $query->where('user_id', $user->id))
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(50)
            ->get();

        $emailLogs = EmailNotificationLog::query()
            ->where(function (Builder $query) use ($user, $boards): void {
                $query->where('recipient', $user->email)
                    ->orWhereIn('team_id', $boards->pluck('team_id'));
            })
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(50)
            ->get();

        return [
            'boards' => $availableBoards,
            'tasks' => $tasks,
            'metrics' => [
                'total' => $tasks->count(),
                'completed' => $completed->count(),
                'overdue' => $overdue->count(),
                'in_progress' => $inProgress->count(),
            ],
            'workload' => $workload,
            'activity_by_project' => $activityByProject,
            'activities' => $activities,
            'whatsapp_logs' => $whatsappLogs,
            'email_logs' => $emailLogs,
            'days' => $days,
            'selected_board_id' => $boardId,
            'generated_at' => now(),
        ];
    }
}
