<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TaskCollaborationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_task_can_be_created_with_multiple_team_assignees(): void
    {
        [$owner, $board, $list, $member] = $this->createTeamBoard();

        $response = $this->actingAs($owner)->post(route('boards.tasks.store', $board), [
            'board_list_id' => $list->id,
            'title' => 'Siapkan materi peluncuran',
            'priority' => 'high',
            'assignee_ids' => [$owner->id, $member->id],
        ]);

        $response->assertRedirect();
        $task = Task::query()->where('title', 'Siapkan materi peluncuran')->firstOrFail();

        $this->assertEqualsCanonicalizing([$owner->id, $member->id], $task->assignees()->pluck('users.id')->all());
        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'type' => 'created']);

        $this->actingAs($owner)->put(route('boards.tasks.assignees.update', [$board, $task]), [])->assertRedirect();
        $this->assertCount(0, $task->assignees()->get());
    }

    public function test_user_from_another_team_cannot_be_assigned(): void
    {
        [$owner, $board, $list] = $this->createTeamBoard();
        $outsider = User::factory()->create();

        $this->actingAs($owner)->post(route('boards.tasks.store', $board), [
            'board_list_id' => $list->id,
            'title' => 'Tugas rahasia',
            'priority' => 'medium',
            'assignee_ids' => [$outsider->id],
        ])->assertNotFound();

        $this->assertDatabaseMissing('tasks', ['title' => 'Tugas rahasia']);
    }

    public function test_member_can_manage_checklist_comment_mentions_and_activity(): void
    {
        [$owner, $board, $list, $member] = $this->createTeamBoard();
        $task = Task::factory()->for($list, 'list')->for($owner, 'creator')->create();

        $this->actingAs($member)->post(route('boards.tasks.checklist.store', [$board, $task]), [
            'title' => 'Periksa brief',
        ])->assertRedirect();

        $checklistItem = $task->checklistItems()->firstOrFail();
        $this->actingAs($member)->patch(route('boards.tasks.checklist.update', [$board, $task, $checklistItem]), [
            'is_completed' => true,
        ])->assertRedirect();

        $this->actingAs($member)->post(route('boards.tasks.comments.store', [$board, $task]), [
            'body' => 'Mohon ditinjau oleh owner.',
            'mention_ids' => [$owner->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('task_checklist_items', [
            'id' => $checklistItem->id,
            'is_completed' => true,
        ]);
        $comment = $task->comments()->firstOrFail();
        $this->assertDatabaseHas('task_comment_mentions', [
            'task_comment_id' => $comment->id,
            'user_id' => $owner->id,
        ]);
        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'type' => 'checklist_completed']);
        $this->assertDatabaseHas('task_activities', ['task_id' => $task->id, 'type' => 'comment_added']);
    }

    public function test_board_filters_tasks_by_assignee_priority_and_due_date(): void
    {
        [$owner, $board, $list, $member] = $this->createTeamBoard();
        $matchingTask = Task::factory()->for($list, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas prioritas khusus',
            'priority' => 'high',
            'due_at' => today()->subDay(),
        ]);
        $matchingTask->assignees()->attach($member);
        Task::factory()->for($list, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas biasa',
            'priority' => 'low',
        ]);

        $this->actingAs($owner)->get(route('boards.show', [
            'board' => $board,
            'assignee' => $member->id,
            'priority' => 'high',
            'due' => 'overdue',
        ]))->assertOk()
            ->assertSee('Tugas prioritas khusus')
            ->assertDontSee('Tugas biasa');
    }

    /**
     * @return array{User, Board, BoardList, User}
     */
    private function createTeamBoard(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create(['position' => 0]);

        return [$owner, $board, $list, $member];
    }
}
