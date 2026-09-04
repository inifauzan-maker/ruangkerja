<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Board;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function index(Request $request): View
    {
        $board = Board::query()
            ->whereHas('team', function ($query) use ($request): void {
                $query->where('owner_id', $request->user()->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->whereKey($request->user()->id));
            })
            ->orderBy('name')
            ->firstOrFail();

        return $this->show($request, $board);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $team = Team::query()->findOrFail($request->integer('team_id'));
        $team->load('members');
        abort_unless($team->canManageProjects($request->user()), 404);

        $board = $team->boards()->create($request->safe()->only(['name', 'description']));
        foreach ([['To Do', 'slate'], ['Dikerjakan', 'amber'], ['Selesai', 'emerald'], ['Batal', 'rose']] as $position => [$title, $color]) {
            $board->lists()->create(compact('title', 'color', 'position'));
        }

        return redirect()->route('boards.show', $board)->with('status', 'Proyek baru berhasil dibuat.');
    }

    public function destroy(Request $request, Board $board): RedirectResponse
    {
        $board->load('team.members');
        abort_unless($board->team->canManageProjects($request->user()), 404);

        $board->delete();

        return redirect()->route('dashboard')->with('status', 'Proyek berhasil dihapus.');
    }

    public function update(UpdateProjectRequest $request, Board $board): RedirectResponse
    {
        $board->load('team.members');
        abort_unless($board->team->canManageProjects($request->user()), 404);

        $board->update($request->validated());

        return back()->with('status', 'Informasi proyek berhasil diperbarui.');
    }

    public function show(Request $request, Board $board): View
    {
        return $this->renderSection($request, $board, 'kanban');
    }

    public function summary(Request $request, Board $board): View
    {
        return $this->renderSection($request, $board, 'summary');
    }

    public function chat(Request $request, Board $board): View
    {
        return $this->renderSection($request, $board, 'chat');
    }

    public function announcements(Request $request, Board $board): View
    {
        return $this->renderSection($request, $board, 'announcements');
    }

    private function renderSection(Request $request, Board $board, string $activeSection): View
    {
        abort_unless($board->belongsToUser($request->user()), 404);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'assignee' => ['nullable', 'integer'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'due' => ['nullable', 'in:overdue,today,week,none'],
        ]);

        $relations = [
            'team.owner:id,name',
            'team.members:id,name',
            'lists' => fn ($query) => $query->with([
                'tasks' => fn ($taskQuery) => $taskQuery
                    ->with(['creator:id,name', 'assignees:id,name', 'attachments'])
                    ->withCount(['checklistItems', 'comments'])
                    ->withCount(['checklistItems as completed_checklist_items_count' => fn ($itemQuery) => $itemQuery->where('is_completed', true)]),
            ]),
        ];

        if ($activeSection === 'chat') {
            $relations['messages'] = fn ($query) => $query->with(['user:id,name', 'attachments'])->limit(100);
        }

        if ($activeSection === 'announcements') {
            $relations['announcements'] = fn ($query) => $query->with(['author:id,name', 'attachments']);
        }

        $board->load($relations);
        $teamMembers = collect([$board->team->owner])
            ->merge($board->team->members)
            ->unique('id')
            ->sortBy('name')
            ->values();

        if ($activeSection === 'kanban' && collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty()) {
            foreach ($board->lists as $list) {
                $list->setRelation('tasks', $this->filterTasks($list->tasks, $filters, $teamMembers));
            }
        }

        $boards = Board::query()
            ->whereHas('team', function ($query) use ($request): void {
                $query->where('owner_id', $request->user()->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->whereKey($request->user()->id));
            })
            ->with('team:id,name')
            ->orderBy('name')
            ->get();

        return view('boards.show', compact('board', 'boards', 'activeSection', 'teamMembers', 'filters'));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  Collection<int, mixed>  $teamMembers
     * @return Collection<int, Task>
     */
    private function filterTasks(Collection $tasks, array $filters, Collection $teamMembers): Collection
    {
        $assigneeId = isset($filters['assignee']) ? (int) $filters['assignee'] : null;
        if ($assigneeId !== null) {
            abort_unless($teamMembers->contains('id', $assigneeId), 404);
        }

        return $tasks->filter(function (Task $task) use ($filters, $assigneeId): bool {
            $term = trim((string) ($filters['q'] ?? ''));
            if ($term !== '' && ! Str::contains(Str::lower($task->title.' '.$task->description), Str::lower($term))) {
                return false;
            }

            if (filled($filters['priority'] ?? null) && $task->priority !== $filters['priority']) {
                return false;
            }

            if ($assigneeId !== null && ! $task->assignees->contains('id', $assigneeId)) {
                return false;
            }

            return match ($filters['due'] ?? null) {
                'overdue' => $task->due_at?->lt(today()) === true,
                'today' => $task->due_at?->isToday() === true,
                'week' => $task->due_at?->betweenIncluded(today(), today()->addDays(7)) === true,
                'none' => $task->due_at === null,
                default => true,
            };
        })->values();
    }
}
