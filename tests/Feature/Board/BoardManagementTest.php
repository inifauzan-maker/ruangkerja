<?php

namespace Tests\Feature\Board;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BoardManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_view_board_with_escaped_task_content(): void
    {
        [$user, $board, $list] = $this->createBoardForUser();
        Task::factory()->for($list, 'list')->for($user, 'creator')->create([
            'title' => '<script>alert("xss")</script>',
        ]);

        $response = $this->actingAs($user)->get(route('boards.show', $board));

        $response->assertOk();
        $response->assertSee('&lt;script&gt;', false);
        $response->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_user_cannot_view_board_owned_by_another_team(): void
    {
        $intruder = User::factory()->create();
        [, $board] = $this->createBoardForUser();

        $this->actingAs($intruder)->get(route('boards.show', $board))->assertNotFound();
    }

    public function test_valid_payload_creates_task_in_selected_list(): void
    {
        [$user, $board, $list] = $this->createBoardForUser();

        $response = $this->actingAs($user)->post(route('boards.tasks.store', $board), [
            'board_list_id' => $list->id,
            'title' => 'Siapkan proposal peluncuran',
            'description' => 'Proposal untuk rapat hari Jumat.',
            'priority' => 'high',
            'due_at' => '2026-09-01',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Tugas berhasil dibuat.');
        $this->assertDatabaseHas('tasks', [
            'board_list_id' => $list->id,
            'creator_id' => $user->id,
            'title' => 'Siapkan proposal peluncuran',
            'priority' => 'high',
        ]);
    }

    public function test_empty_task_payload_returns_validation_errors_without_creating_task(): void
    {
        [$user, $board] = $this->createBoardForUser();

        $response = $this->actingAs($user)->post(route('boards.tasks.store', $board), []);

        $response->assertSessionHasErrors(['board_list_id', 'title', 'priority']);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_task_can_be_moved_to_another_list(): void
    {
        [$user, $board, $sourceList] = $this->createBoardForUser();
        $targetList = BoardList::factory()->for($board)->create(['position' => 1]);
        $task = Task::factory()->for($sourceList, 'list')->for($user, 'creator')->create();

        $response = $this->actingAs($user)->patchJson(route('boards.tasks.update', [$board, $task]), [
            'board_list_id' => $targetList->id,
            'position' => 0,
        ]);

        $response->assertOk()->assertJson(['message' => 'Posisi tugas diperbarui.']);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'board_list_id' => $targetList->id,
            'position' => 0,
        ]);
    }

    public function test_task_cannot_be_moved_to_list_on_another_board(): void
    {
        [$user, $board, $sourceList] = $this->createBoardForUser();
        [, $otherBoard, $otherList] = $this->createBoardForUser($user);
        $task = Task::factory()->for($sourceList, 'list')->for($user, 'creator')->create();

        $this->actingAs($user)->patchJson(route('boards.tasks.update', [$board, $task]), [
            'board_list_id' => $otherList->id,
            'position' => 0,
        ])->assertNotFound();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'board_list_id' => $sourceList->id]);
        $this->assertNotSame($board->id, $otherBoard->id);
    }

    /**
     * @return array{User, Board, BoardList}
     */
    private function createBoardForUser(?User $user = null): array
    {
        $user ??= User::factory()->create();
        $team = Team::factory()->for($user, 'owner')->create();
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create(['position' => 0]);

        return [$user, $board, $list];
    }
}
