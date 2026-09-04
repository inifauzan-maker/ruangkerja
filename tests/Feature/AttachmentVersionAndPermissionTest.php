<?php

namespace Tests\Feature;

use App\Actions\ScanUploadedFile;
use App\Models\Attachment;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttachmentVersionAndPermissionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_member_can_preview_image_and_upload_a_new_version(): void
    {
        Storage::fake('local');
        [$owner, $member, $board, $task] = $this->createTask();

        $this->actingAs($owner)->post(route('boards.tasks.attachments.store', [$board, $task]), [
            'attachments' => [UploadedFile::fake()->image('rancangan.png')],
        ])->assertRedirect();

        $firstVersion = Attachment::query()->firstOrFail();

        $this->actingAs($member)->get(route('attachments.preview', $firstVersion))
            ->assertOk()
            ->assertSee('rancangan.png');
        $this->actingAs($member)->get(route('attachments.inline', $firstVersion))->assertOk();

        $this->actingAs($member)->post(route('attachments.versions.store', $firstVersion), [
            'attachment' => UploadedFile::fake()->image('rancangan.png'),
        ])->assertRedirect();

        $currentVersion = Attachment::query()->where('is_current', true)->firstOrFail();
        $this->assertSame(2, $currentVersion->version);
        $this->assertSame($firstVersion->id, $currentVersion->root_attachment_id);
        $this->assertFalse($firstVersion->fresh()->is_current);
        $this->assertCount(1, $task->fresh()->attachments);
        $this->assertCount(2, $task->allAttachments);
    }

    public function test_new_version_must_keep_the_original_extension(): void
    {
        Storage::fake('local');
        [$owner, , $board, $task] = $this->createTask();

        $this->actingAs($owner)->post(route('boards.tasks.attachments.store', [$board, $task]), [
            'attachments' => [UploadedFile::fake()->create('brief.pdf', 5, 'application/pdf')],
        ]);
        $attachment = Attachment::query()->firstOrFail();

        $this->actingAs($owner)->post(route('attachments.versions.store', $attachment), [
            'attachment' => UploadedFile::fake()->image('brief.png'),
        ])->assertSessionHasErrors('attachment');

        $this->assertDatabaseCount('attachments', 1);
    }

    public function test_project_download_permission_can_limit_regular_members(): void
    {
        Storage::fake('local');
        [$owner, $member, $board, $task] = $this->createTask();

        $this->actingAs($owner)->post(route('boards.tasks.attachments.store', [$board, $task]), [
            'attachments' => [UploadedFile::fake()->create('rahasia.pdf', 5, 'application/pdf')],
        ]);
        $attachment = Attachment::query()->firstOrFail();

        $this->actingAs($owner)->patch(route('boards.file-settings.update', $board), [
            'download_permission' => 'managers',
        ])->assertRedirect();

        $this->actingAs($member)->get(route('attachments.show', $attachment))->assertNotFound();
        $this->actingAs($member)->get(route('attachments.preview', $attachment))->assertNotFound();
        $this->actingAs($owner)->get(route('attachments.show', $attachment))->assertDownload('rahasia.pdf');
    }

    public function test_regular_member_cannot_change_project_file_permission(): void
    {
        [, $member, $board] = $this->createTask();

        $this->actingAs($member)->patch(route('boards.file-settings.update', $board), [
            'download_permission' => 'uploader',
        ])->assertNotFound();

        $this->assertSame('team', $board->fresh()->download_permission);
    }

    public function test_antivirus_rejection_does_not_store_file_or_database_record(): void
    {
        Storage::fake('local');
        [$owner, , $board, $task] = $this->createTask();

        $scanner = $this->mock(ScanUploadedFile::class);
        $scanner->shouldReceive('execute')->once()->andThrow(
            ValidationException::withMessages(['attachments' => 'Malware terdeteksi.']),
        );

        $this->actingAs($owner)->post(route('boards.tasks.attachments.store', [$board, $task]), [
            'attachments' => [UploadedFile::fake()->create('terinfeksi.pdf', 5, 'application/pdf')],
        ])->assertSessionHasErrors('attachments');

        $this->assertDatabaseCount('attachments', 0);
        Storage::disk('local')->assertDirectoryEmpty('/');
    }

    /**
     * @return array{User, User, Board, Task}
     */
    private function createTask(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();
        $task = Task::factory()->for($list, 'list')->for($owner, 'creator')->create();

        return [$owner, $member, $board, $task];
    }
}
