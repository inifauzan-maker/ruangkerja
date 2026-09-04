<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_with_workspace_can_open_application(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user, 'owner')->create();
        $board = Board::factory()->for($team)->create();
        BoardList::factory()->for($board)->create();

        $this->actingAs($user)->get('/')->assertOk()->assertSee($board->name);
    }
}
