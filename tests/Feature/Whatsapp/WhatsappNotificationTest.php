<?php

namespace Tests\Feature\Whatsapp;

use App\Actions\SendWhatsappMessage;
use App\Jobs\SendWhatsappNotification;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\WhatsappConnection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsappNotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creating_task_only_queues_notification_for_selected_assignee(): void
    {
        Queue::fake([SendWhatsappNotification::class]);
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $observer = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($actor, ['role' => 'member']);
        $team->members()->attach($assignee, ['role' => 'member']);
        $team->members()->attach($observer, ['role' => 'member']);
        $board = Board::factory()->for($team)->create(['name' => 'Peluncuran']);
        $list = BoardList::factory()->for($board)->create();
        WhatsappConnection::factory()->for($owner)->create();
        $assigneeConnection = WhatsappConnection::factory()->for($assignee)->create();
        WhatsappConnection::factory()->for($observer)->create();

        $response = $this->actingAs($actor)->post(route('boards.tasks.store', $board), [
            'board_list_id' => $list->id,
            'title' => 'Siapkan materi demo',
            'priority' => 'high',
            'assignee_ids' => [$assignee->id],
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Tugas berhasil dibuat.');
        $task = Task::query()->where('title', 'Siapkan materi demo')->firstOrFail();
        Queue::assertPushed(SendWhatsappNotification::class, 1);
        Queue::assertPushed(
            SendWhatsappNotification::class,
            fn (SendWhatsappNotification $job): bool => $job->connectionId === $assigneeConnection->id
                && $job->event === WhatsappConnection::EventTaskCreated
                && $job->projectName === 'Peluncuran',
        );
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'task_id' => $task->id,
            'whatsapp_connection_id' => $assigneeConnection->id,
        ]);
    }

    public function test_new_assignee_receives_targeted_notification(): void
    {
        Queue::fake([SendWhatsappNotification::class]);
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $observer = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $team->members()->attach($observer, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();
        $task = Task::factory()->for($list, 'list')->for($owner, 'creator')->create();
        $memberConnection = WhatsappConnection::factory()->for($member)->create();
        WhatsappConnection::factory()->for($observer)->create();

        $response = $this->actingAs($owner)->put(route('boards.tasks.assignees.update', [$board, $task]), [
            'assignee_ids' => [$member->id],
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Penanggung jawab tugas diperbarui.');
        Queue::assertPushed(SendWhatsappNotification::class, 1);
        Queue::assertPushed(
            SendWhatsappNotification::class,
            fn (SendWhatsappNotification $job): bool => $job->connectionId === $memberConnection->id
                && $job->event === WhatsappConnection::EventTaskAssigned,
        );
    }

    public function test_mentioned_user_receives_targeted_comment_notification(): void
    {
        Queue::fake([SendWhatsappNotification::class]);
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $observer = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $team->members()->attach($observer, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();
        $task = Task::factory()->for($list, 'list')->for($owner, 'creator')->create();
        $memberConnection = WhatsappConnection::factory()->for($member)->create();
        WhatsappConnection::factory()->for($observer)->create();

        $response = $this->actingAs($owner)->post(route('boards.tasks.comments.store', [$board, $task]), [
            'body' => 'Mohon periksa desain terbaru.',
            'mention_ids' => [$member->id],
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Komentar ditambahkan.');
        Queue::assertPushed(SendWhatsappNotification::class, 1);
        Queue::assertPushed(
            SendWhatsappNotification::class,
            fn (SendWhatsappNotification $job): bool => $job->connectionId === $memberConnection->id
                && $job->event === WhatsappConnection::EventTaskMentioned,
        );
    }

    public function test_disabled_preference_prevents_task_update_notification(): void
    {
        Queue::fake([SendWhatsappNotification::class]);
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($actor, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $list = BoardList::factory()->for($board)->create();
        $task = Task::factory()->for($list, 'list')->for($owner, 'creator')->create();
        WhatsappConnection::factory()->for($owner)->create(['notify_task_updated' => false]);

        $response = $this->actingAs($actor)->patch(route('boards.tasks.update', [$board, $task]), [
            'title' => 'Judul telah diperbarui',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Tugas berhasil diperbarui.');
        $this->assertSame('Judul telah diperbarui', $task->fresh()->title);
        Queue::assertNothingPushed();
    }

    public function test_announcement_and_chat_respect_their_notification_preferences(): void
    {
        Queue::fake([SendWhatsappNotification::class]);
        $owner = User::factory()->create();
        $actor = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($actor, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $connection = WhatsappConnection::factory()->for($owner)->create([
            'notify_announcements' => true,
            'notify_chat_messages' => false,
        ]);

        $announcementResponse = $this->actingAs($actor)->post(route('boards.announcements.store', $board), [
            'title' => 'Jadwal rapat',
            'body' => 'Rapat dimulai pukul sembilan.',
        ]);
        $chatResponse = $this->actingAs($actor)->post(route('boards.messages.store', $board), [
            'body' => 'Pesan ini tidak perlu notifikasi.',
        ]);

        $announcementResponse->assertRedirect(route('boards.announcements', $board));
        $chatResponse->assertRedirect(route('boards.chat', $board));
        $this->assertDatabaseHas('announcements', ['title' => 'Jadwal rapat']);
        Queue::assertPushed(SendWhatsappNotification::class, 1);
        Queue::assertPushed(
            SendWhatsappNotification::class,
            fn (SendWhatsappNotification $job): bool => $job->connectionId === $connection->id
                && $job->event === WhatsappConnection::EventAnnouncement,
        );
    }

    public function test_notification_job_sends_plain_text_and_records_fonnte_message_id(): void
    {
        config()->set('services.fonnte.api_key', 'fonnte-global-device-key-123456');
        Http::preventStrayRequests();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true, 'id' => ['80367171']]),
        ]);
        $user = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create([
            'recipient_phone' => '628123456789',
        ]);
        $job = new SendWhatsappNotification(
            $connection->id,
            WhatsappConnection::EventTaskCreated,
            'Tugas baru',
            'Dina membuat tugas “Materi demo”.',
            'Peluncuran',
            'http://localhost/boards/1',
        );

        $job->handle(app(SendWhatsappMessage::class));

        $connection->refresh();
        $this->assertSame('80367171', $connection->last_message_id);
        $this->assertNotNull($connection->last_sent_at);
        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'fonnte-global-device-key-123456')
            && str_contains($request['message'], '*Tugas baru*')
            && $request['target'] === '628123456789');
    }
}
