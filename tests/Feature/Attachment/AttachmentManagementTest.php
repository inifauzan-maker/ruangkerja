<?php

namespace Tests\Feature\Attachment;

use App\Models\Attachment;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_team_member_can_upload_multiple_files_to_task(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();

        $response = $this->actingAs($owner)->post(route('boards.tasks.store', $board), [
            'board_list_id' => $list->id,
            'title' => 'Tugas dengan berkas',
            'priority' => 'medium',
            'attachments' => [
                UploadedFile::fake()->create('brief.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('referensi.jpg'),
            ],
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Tugas berhasil dibuat.');
        $this->assertDatabaseCount('attachments', 2);
        Attachment::query()->each(fn (Attachment $attachment) => Storage::disk('local')->assertExists($attachment->path));
    }

    public function test_chat_message_can_contain_only_an_attachment(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();

        $response = $this->actingAs($owner)->post(route('boards.messages.store', $board), [
            'attachments' => [UploadedFile::fake()->create('catatan.txt', 2, 'text/plain')],
        ]);

        $response->assertRedirect(route('boards.chat', $board));
        $this->assertDatabaseHas('board_messages', ['board_id' => $board->id, 'body' => '']);
        $this->assertDatabaseHas('attachments', ['original_name' => 'catatan.txt', 'uploader_id' => $owner->id]);
        $this->actingAs($owner)->get(route('boards.chat', $board))->assertSee('catatan.txt');
    }

    public function test_member_can_add_attachment_to_an_existing_task(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();
        $task = $list->tasks()->create([
            'creator_id' => $owner->id,
            'title' => 'Tugas lama',
            'priority' => 'medium',
            'position' => 1,
        ]);

        $response = $this->actingAs($member)->post(route('boards.tasks.attachments.store', [$board, $task]), [
            'attachments' => [UploadedFile::fake()->create('tambahan.pdf', 20, 'application/pdf')],
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Lampiran ditambahkan ke tugas.');
        $attachment = Attachment::query()->firstOrFail();
        $this->assertSame($task->id, $attachment->attachable_id);
        $this->assertSame($member->id, $attachment->uploader_id);
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_announcement_accepts_a_private_attachment(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();

        $response = $this->actingAs($owner)->post(route('boards.announcements.store', $board), [
            'title' => 'Panduan baru',
            'body' => 'Silakan pelajari lampiran.',
            'attachments' => [UploadedFile::fake()->create('panduan.docx', 30, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')],
        ]);

        $response->assertRedirect(route('boards.announcements', $board));
        $attachment = Attachment::query()->firstOrFail();
        $this->assertSame('panduan.docx', $attachment->original_name);
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_executable_attachment_is_rejected_without_storing_a_file(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();

        $response = $this->actingAs($owner)->post(route('boards.tasks.store', $board), [
            'board_list_id' => $list->id,
            'title' => 'Berkas berbahaya',
            'priority' => 'high',
            'attachments' => [UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')],
        ]);

        $response->assertSessionHasErrors('attachments.0');
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('attachments', 0);
        Storage::disk('local')->assertDirectoryEmpty('/');
    }

    public function test_only_project_members_can_download_an_attachment(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();

        $this->actingAs($owner)->post(route('boards.messages.store', $board), [
            'attachments' => [UploadedFile::fake()->create('rahasia.pdf', 10, 'application/pdf')],
        ]);
        $attachment = Attachment::query()->firstOrFail();

        $this->actingAs($owner)->get(route('attachments.show', $attachment))->assertDownload('rahasia.pdf');
        $this->actingAs($outsider)->get(route('attachments.show', $attachment))->assertNotFound();
    }

    public function test_uploader_can_delete_attachment_and_physical_file(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();

        $this->actingAs($owner)->post(route('boards.messages.store', $board), [
            'attachments' => [UploadedFile::fake()->create('hapus.pdf', 10, 'application/pdf')],
        ]);
        $attachment = Attachment::query()->firstOrFail();
        $path = $attachment->path;

        $response = $this->actingAs($owner)->delete(route('attachments.destroy', $attachment));

        $response->assertRedirect()->assertSessionHas('status', 'Lampiran dihapus.');
        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_regular_member_cannot_delete_another_members_attachment(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();

        $this->actingAs($owner)->post(route('boards.messages.store', $board), [
            'attachments' => [UploadedFile::fake()->create('milik-owner.pdf', 10, 'application/pdf')],
        ]);
        $attachment = Attachment::query()->firstOrFail();

        $this->actingAs($member)->delete(route('attachments.destroy', $attachment))->assertNotFound();

        $this->assertModelExists($attachment);
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_deleting_task_also_removes_its_attachments(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();

        $this->actingAs($owner)->post(route('boards.tasks.store', $board), [
            'board_list_id' => $list->id,
            'title' => 'Tugas sementara',
            'priority' => 'low',
            'attachments' => [UploadedFile::fake()->create('sementara.pdf', 10, 'application/pdf')],
        ]);
        $task = $list->tasks()->firstOrFail();
        $attachment = Attachment::query()->firstOrFail();
        $path = $attachment->path;

        $this->actingAs($owner)->delete(route('boards.tasks.destroy', [$board, $task]))->assertRedirect();

        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_deleting_project_removes_all_nested_attachment_files(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create();

        $this->actingAs($owner)->post(route('boards.messages.store', $board), [
            'attachments' => [UploadedFile::fake()->create('project-file.pdf', 10, 'application/pdf')],
        ]);
        $attachment = Attachment::query()->firstOrFail();
        $path = $attachment->path;

        $this->actingAs($owner)->delete(route('projects.destroy', $board))->assertRedirect(route('dashboard'));

        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($path);
    }
}
