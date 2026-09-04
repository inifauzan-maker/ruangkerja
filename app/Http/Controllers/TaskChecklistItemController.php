<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskChecklistItemRequest;
use App\Http\Requests\UpdateTaskChecklistItemRequest;
use App\Models\Board;
use App\Models\Task;
use App\Models\TaskChecklistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskChecklistItemController extends Controller
{
    public function store(StoreTaskChecklistItemRequest $request, Board $board, Task $task): RedirectResponse
    {
        $this->authorizeTask($request, $board, $task);

        $item = $task->checklistItems()->create([
            'creator_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'position' => ((int) $task->checklistItems()->max('position')) + 1,
        ]);
        $task->recordActivity($request->user(), 'checklist_added', ['title' => $item->title]);

        return back()->with('status', 'Checklist ditambahkan.');
    }

    public function update(
        UpdateTaskChecklistItemRequest $request,
        Board $board,
        Task $task,
        TaskChecklistItem $checklistItem,
    ): RedirectResponse {
        $this->authorizeItem($request, $board, $task, $checklistItem);

        $validated = $request->validated();
        if (array_key_exists('is_completed', $validated)) {
            $validated['completed_at'] = $validated['is_completed'] ? now() : null;
        }

        $checklistItem->update($validated);
        $task->recordActivity($request->user(), $checklistItem->is_completed ? 'checklist_completed' : 'checklist_updated', [
            'title' => $checklistItem->title,
        ]);

        return back()->with('status', 'Checklist diperbarui.');
    }

    public function destroy(
        Request $request,
        Board $board,
        Task $task,
        TaskChecklistItem $checklistItem,
    ): RedirectResponse {
        $this->authorizeItem($request, $board, $task, $checklistItem);

        $title = $checklistItem->title;
        $checklistItem->delete();
        $task->recordActivity($request->user(), 'checklist_removed', ['title' => $title]);

        return back()->with('status', 'Checklist dihapus.');
    }

    private function authorizeTask(Request $request, Board $board, Task $task): void
    {
        abort_unless($board->belongsToUser($request->user()), 404);
        abort_unless($task->list()->where('board_id', $board->id)->exists(), 404);
    }

    private function authorizeItem(
        Request $request,
        Board $board,
        Task $task,
        TaskChecklistItem $checklistItem,
    ): void {
        $this->authorizeTask($request, $board, $task);
        abort_unless($checklistItem->task_id === $task->id, 404);
    }
}
