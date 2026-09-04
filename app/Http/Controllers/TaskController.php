<?php

namespace App\Http\Controllers;

use App\Actions\DispatchWhatsappNotifications;
use App\Actions\StoreAttachments;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Task;
use App\Models\WhatsappConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function show(Request $request, Board $board, Task $task): View
    {
        $this->authorizeTask($request, $board, $task);

        $board->load(['team.owner:id,name', 'team.members:id,name', 'lists:id,board_id,title,position']);
        $task->load([
            'list:id,board_id,title',
            'creator:id,name',
            'assignees:id,name',
            'attachments',
            'checklistItems.creator:id,name',
            'comments.user:id,name',
            'comments.mentions:id,name',
            'activities.actor:id,name',
        ]);

        $teamMembers = collect([$board->team->owner])
            ->merge($board->team->members)
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('tasks.show', compact('board', 'task', 'teamMembers'));
    }

    public function store(
        StoreTaskRequest $request,
        Board $board,
        StoreAttachments $storeAttachments,
        DispatchWhatsappNotifications $dispatchWhatsappNotifications,
    ): RedirectResponse {
        abort_unless($board->belongsToUser($request->user()), 404);

        $list = $this->findList($board, $request->integer('board_list_id'));
        $assigneeIds = collect($request->validated('assignee_ids', []))->map(fn ($id): int => (int) $id);
        $this->ensureTeamMembers($board, $assigneeIds);

        $task = DB::transaction(function () use ($list, $request, $storeAttachments, $assigneeIds): Task {
            $task = $list->tasks()->create([
                ...$request->safe()->except(['board_list_id', 'attachments', 'assignee_ids']),
                'creator_id' => $request->user()->id,
                'position' => ((int) $list->tasks()->max('position')) + 1,
            ]);

            $task->assignees()->sync($assigneeIds);
            $storeAttachments->execute($task, $request->user(), $request->file('attachments', []));
            $task->recordActivity($request->user(), 'created', ['assignee_ids' => $assigneeIds->values()->all()]);

            return $task;
        });

        $board->loadMissing('team');
        $dispatchWhatsappNotifications->execute(
            $board->team,
            $request->user(),
            WhatsappConnection::EventTaskCreated,
            'Tugas baru untuk Anda',
            $request->user()->name.' membuat tugas "'.$task->title.'".',
            $board->name,
            route('boards.tasks.show', [$board, $task]),
            $task,
            $assigneeIds,
        );

        return back()->with('status', 'Tugas berhasil dibuat.');
    }

    public function update(
        UpdateTaskRequest $request,
        Board $board,
        Task $task,
        DispatchWhatsappNotifications $dispatchWhatsappNotifications,
    ): RedirectResponse|JsonResponse {
        $this->authorizeTask($request, $board, $task);

        $validated = $request->validated();
        if (isset($validated['board_list_id'])) {
            $this->findList($board, (int) $validated['board_list_id']);
        }

        $task->update($validated);
        $changedFields = array_keys($task->getChanges());

        if ($changedFields !== []) {
            $task->recordActivity($request->user(), 'updated', ['fields' => $changedFields]);
            $board->loadMissing('team');
            $task->loadMissing('assignees:id');
            $recipientUserIds = $task->assignees->pluck('id')->push($task->creator_id)->unique();
            $dispatchWhatsappNotifications->execute(
                $board->team,
                $request->user(),
                WhatsappConnection::EventTaskUpdated,
                'Tugas diperbarui',
                $request->user()->name.' memperbarui tugas "'.$task->title.'".',
                $board->name,
                route('boards.tasks.show', [$board, $task]),
                $task,
                $recipientUserIds,
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Posisi tugas diperbarui.']);
        }

        return back()->with('status', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, Board $board, Task $task): RedirectResponse
    {
        $this->authorizeTask($request, $board, $task);
        $task->delete();

        return redirect()->route('boards.show', $board)->with('status', 'Tugas dihapus.');
    }

    private function findList(Board $board, int $listId): BoardList
    {
        return $board->lists()->findOrFail($listId);
    }

    private function authorizeTask(Request $request, Board $board, Task $task): void
    {
        abort_unless($board->belongsToUser($request->user()), 404);
        abort_unless($task->list()->where('board_id', $board->id)->exists(), 404);
    }

    /** @param Collection<int, int> $userIds */
    private function ensureTeamMembers(Board $board, Collection $userIds): void
    {
        if ($userIds->isEmpty()) {
            return;
        }

        $board->loadMissing('team.members:id');
        $allowedUserIds = $board->team->members->pluck('id')->push($board->team->owner_id)->unique();
        abort_unless($userIds->diff($allowedUserIds)->isEmpty(), 404);
    }
}
