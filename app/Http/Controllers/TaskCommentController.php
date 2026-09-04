<?php

namespace App\Http\Controllers;

use App\Actions\DispatchWhatsappNotifications;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Board;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\WhatsappConnection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaskCommentController extends Controller
{
    public function store(
        StoreTaskCommentRequest $request,
        Board $board,
        Task $task,
        DispatchWhatsappNotifications $dispatchWhatsappNotifications,
    ): RedirectResponse {
        $this->authorizeTask($request, $board, $task);

        $board->load('team.members:id');
        $allowedUserIds = $board->team->members->pluck('id')
            ->push($board->team->owner_id)
            ->unique();
        $mentionIds = collect($request->validated('mention_ids', []))->map(fn ($id): int => (int) $id);
        abort_unless($mentionIds->diff($allowedUserIds)->isEmpty(), 404);

        $comment = DB::transaction(function () use ($request, $task, $mentionIds): TaskComment {
            $comment = $task->comments()->create([
                'user_id' => $request->user()->id,
                'body' => $request->validated('body'),
            ]);
            $comment->mentions()->sync($mentionIds);
            $task->recordActivity($request->user(), 'comment_added', [
                'comment_id' => $comment->id,
                'mention_ids' => $mentionIds->values()->all(),
            ]);

            return $comment;
        });

        if ($mentionIds->isNotEmpty()) {
            $dispatchWhatsappNotifications->execute(
                $board->team,
                $request->user(),
                WhatsappConnection::EventTaskMentioned,
                'Anda disebut dalam komentar',
                $request->user()->name.': '.Str::limit($comment->body, 140),
                $board->name,
                route('boards.tasks.show', [$board, $task]),
                $task,
                $mentionIds,
            );
        }

        return back()->with('status', 'Komentar ditambahkan.');
    }

    public function destroy(Request $request, Board $board, Task $task, TaskComment $comment): RedirectResponse
    {
        $this->authorizeTask($request, $board, $task);
        abort_unless($comment->task_id === $task->id, 404);

        $board->load('team.members');
        abort_unless(
            $comment->user_id === $request->user()->id || $board->team->canManageProjects($request->user()),
            403,
        );

        $comment->delete();
        $task->recordActivity($request->user(), 'comment_removed');

        return back()->with('status', 'Komentar dihapus.');
    }

    private function authorizeTask(Request $request, Board $board, Task $task): void
    {
        abort_unless($board->belongsToUser($request->user()), 404);
        abort_unless($task->list()->where('board_id', $board->id)->exists(), 404);
    }
}
