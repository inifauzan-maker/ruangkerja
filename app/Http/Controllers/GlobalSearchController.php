<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\Board;
use App\Models\BoardMessage;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate(['q' => ['required', 'string', 'max:100', 'not_regex:/^\\s*$/']]);
        $term = trim($validated['q']);

        $accessibleBoards = Board::query()->whereHas('team', function (Builder $query) use ($request): void {
            $query->where('owner_id', $request->user()->id)
                ->orWhereHas('members', fn (Builder $memberQuery) => $memberQuery->whereKey($request->user()->id));
        });
        $boardIds = (clone $accessibleBoards)->pluck('id');

        $boards = (clone $accessibleBoards)
            ->with('team:id,name')
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        $tasks = Task::query()
            ->with(['list.board.team:id,name', 'assignees:id,name'])
            ->whereHas('list', fn (Builder $query) => $query->whereIn('board_id', $boardIds))
            ->where(function (Builder $query) use ($term): void {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->latest()
            ->limit(20)
            ->get();

        $attachments = Attachment::query()
            ->with('attachable')
            ->where('is_current', true)
            ->where('original_name', 'like', "%{$term}%")
            ->where(function (Builder $query) use ($boardIds): void {
                $query->whereHasMorph('attachable', Task::class, function (Builder $taskQuery) use ($boardIds): void {
                    $taskQuery->whereHas('list', fn (Builder $listQuery) => $listQuery->whereIn('board_id', $boardIds));
                })->orWhereHasMorph('attachable', BoardMessage::class, fn (Builder $messageQuery) => $messageQuery->whereIn('board_id', $boardIds))
                    ->orWhereHasMorph('attachable', Announcement::class, fn (Builder $announcementQuery) => $announcementQuery->whereIn('board_id', $boardIds));
            })
            ->latest()
            ->limit(20)
            ->get();

        return view('search.index', compact('term', 'boards', 'tasks', 'attachments'));
    }
}
