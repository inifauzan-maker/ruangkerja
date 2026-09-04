<?php

namespace Tests\Feature\Collaboration;

use App\Models\Announcement;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CollaborationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_open_summary_chat_and_announcement_pages(): void
    {
        [$user, $board] = $this->createBoardForUser();

        $this->actingAs($user)->get(route('boards.summary', $board))->assertOk()->assertSee('Ringkasan proyek');
        $this->actingAs($user)->get(route('boards.chat', $board))->assertOk()->assertSee('Percakapan');
        $this->actingAs($user)->get(route('boards.announcements', $board))->assertOk()->assertSee('Pengumuman terbaru');
    }

    public function test_valid_message_is_created_and_escaped_when_rendered(): void
    {
        [$user, $board] = $this->createBoardForUser();
        $message = 'Update <script>alert("xss")</script>';

        $response = $this->actingAs($user)->post(route('boards.messages.store', $board), [
            'body' => $message,
        ]);

        $response->assertRedirect(route('boards.chat', $board));
        $this->assertDatabaseHas('board_messages', ['board_id' => $board->id, 'body' => $message]);
        $this->actingAs($user)->get(route('boards.chat', $board))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_empty_message_is_rejected_without_database_change(): void
    {
        [$user, $board] = $this->createBoardForUser();

        $this->actingAs($user)->post(route('boards.messages.store', $board), [])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('board_messages', 0);
    }

    public function test_valid_announcement_is_created_and_can_be_pinned(): void
    {
        [$user, $board] = $this->createBoardForUser();

        $response = $this->actingAs($user)->post(route('boards.announcements.store', $board), [
            'title' => 'Jadwal rapat berubah',
            'body' => 'Rapat dipindahkan ke pukul 10.00.',
            'is_pinned' => true,
        ]);

        $response->assertRedirect(route('boards.announcements', $board));
        $this->assertDatabaseHas('announcements', [
            'board_id' => $board->id,
            'author_id' => $user->id,
            'title' => 'Jadwal rapat berubah',
            'is_pinned' => true,
        ]);
    }

    public function test_user_cannot_post_collaboration_content_to_another_users_board(): void
    {
        $intruder = User::factory()->create();
        [, $board] = $this->createBoardForUser();

        $this->actingAs($intruder)->post(route('boards.messages.store', $board), ['body' => 'Rahasia'])->assertNotFound();
        $this->actingAs($intruder)->post(route('boards.announcements.store', $board), [
            'title' => 'Tidak sah',
            'body' => 'Tidak boleh tersimpan.',
        ])->assertNotFound();

        $this->assertDatabaseCount('board_messages', 0);
        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_pinned_announcement_is_rendered_before_regular_announcement(): void
    {
        [$user, $board] = $this->createBoardForUser();
        Announcement::factory()->for($board)->for($user, 'author')->create(['title' => 'Pengumuman biasa']);
        Announcement::factory()->for($board)->for($user, 'author')->create(['title' => 'Pengumuman penting', 'is_pinned' => true]);

        $this->actingAs($user)->get(route('boards.announcements', $board))
            ->assertSeeInOrder(['Pengumuman penting', 'Pengumuman biasa']);
    }

    /**
     * @return array{User, Board}
     */
    private function createBoardForUser(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user, 'owner')->create();
        $board = Board::factory()->for($team)->create();
        BoardList::factory()->for($board)->create(['title' => 'To Do', 'position' => 0]);

        return [$user, $board];
    }
}
