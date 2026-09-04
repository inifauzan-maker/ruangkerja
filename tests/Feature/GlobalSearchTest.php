<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_returns_accessible_projects_tasks_and_files_only(): void
    {
        $user = User::factory()->create();
        [$board, $task] = $this->createTaskForOwner($user, 'Proposal Alpha');
        Attachment::factory()->for($user, 'uploader')->for($task, 'attachable')->create([
            'original_name' => 'proposal-alpha.pdf',
        ]);

        $otherOwner = User::factory()->create();
        [, $privateTask] = $this->createTaskForOwner($otherOwner, 'Proposal Privat');
        Attachment::factory()->for($otherOwner, 'uploader')->for($privateTask, 'attachable')->create([
            'original_name' => 'proposal-privat.pdf',
        ]);

        $this->actingAs($user)->get(route('search', ['q' => 'proposal']))
            ->assertOk()
            ->assertSee($board->name)
            ->assertSee('Proposal Alpha')
            ->assertSee('proposal-alpha.pdf')
            ->assertDontSee('Proposal Privat')
            ->assertDontSee('proposal-privat.pdf');
    }

    public function test_search_requires_a_non_empty_term(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('search'))->assertSessionHasErrors('q');
        $this->actingAs($user)->get(route('search', ['q' => '   ']))->assertSessionHasErrors('q');
    }

    /**
     * @return array{Board, Task}
     */
    private function createTaskForOwner(User $owner, string $title): array
    {
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create(['name' => $title.' Project']);
        $list = BoardList::factory()->for($board)->create();
        $task = Task::factory()->for($list, 'list')->for($owner, 'creator')->create(['title' => $title]);

        return [$board, $task];
    }
}
