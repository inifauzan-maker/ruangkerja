<?php

namespace App\Http\Controllers;

use App\Actions\DispatchWhatsappNotifications;
use App\Http\Requests\UpdateTaskAssigneesRequest;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use App\Models\WhatsappConnection;
use Illuminate\Http\RedirectResponse;

class TaskAssigneeController extends Controller
{
    public function update(
        UpdateTaskAssigneesRequest $request,
        Board $board,
        Task $task,
        DispatchWhatsappNotifications $dispatchWhatsappNotifications,
    ): RedirectResponse {
        $this->authorizeTask($request->user(), $board, $task);

        $board->load('team.members:id');
        $allowedUserIds = $board->team->members->pluck('id')
            ->push($board->team->owner_id)
            ->unique();
        $assigneeIds = collect($request->validated('assignee_ids', []))->map(fn ($id): int => (int) $id);

        abort_unless($assigneeIds->diff($allowedUserIds)->isEmpty(), 404);

        $changes = $task->assignees()->sync($assigneeIds);
        if ($changes['attached'] !== [] || $changes['detached'] !== []) {
            $task->recordActivity($request->user(), 'assignees_updated', [
                'assignee_ids' => $assigneeIds->values()->all(),
            ]);
        }

        if ($changes['attached'] !== []) {
            $dispatchWhatsappNotifications->execute(
                $board->team,
                $request->user(),
                WhatsappConnection::EventTaskAssigned,
                'Anda mendapat tugas',
                $request->user()->name.' menugaskan Anda ke "'.$task->title.'".',
                $board->name,
                route('boards.tasks.show', [$board, $task]),
                $task,
                $changes['attached'],
            );
        }

        return back()->with('status', 'Penanggung jawab tugas diperbarui.');
    }

    private function authorizeTask(User $user, Board $board, Task $task): void
    {
        abort_unless($board->belongsToUser($user), 404);
        abort_unless($task->list()->where('board_id', $board->id)->exists(), 404);
    }
}
