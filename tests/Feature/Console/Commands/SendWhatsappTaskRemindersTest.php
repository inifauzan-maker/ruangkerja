<?php

namespace Tests\Feature\Console\Commands;

use App\Actions\DispatchWhatsappTaskReminders;
use App\Jobs\SendWhatsappNotification;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\WhatsappConnection;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendWhatsappTaskRemindersTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_queues_assigned_due_reminders_once_and_ignores_terminal_or_unassigned_tasks(): void
    {
        Queue::fake([SendWhatsappNotification::class]);
        $this->travelTo(CarbonImmutable::parse('2026-08-30 08:00:00', 'Asia/Jakarta'));
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $board = Board::factory()->for($team)->create(['name' => 'Peluncuran']);
        $activeList = BoardList::factory()->for($board)->create(['title' => 'Dikerjakan']);
        $completedList = BoardList::factory()->for($board)->create(['title' => 'Selesai']);
        $todayTask = Task::factory()->for($activeList, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas hari ini',
            'due_at' => '2026-08-30',
        ]);
        $tomorrowTask = Task::factory()->for($activeList, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas besok',
            'due_at' => '2026-08-31',
        ]);
        $completedTask = Task::factory()->for($completedList, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas selesai',
            'due_at' => '2026-08-30',
        ]);
        Task::factory()->for($activeList, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas bukan milik pengguna',
            'due_at' => '2026-08-30',
        ]);
        $todayTask->assignees()->attach($owner);
        $tomorrowTask->assignees()->attach($owner);
        $completedTask->assignees()->attach($owner);
        WhatsappConnection::factory()->for($owner)->create([
            'quiet_hours_enabled' => false,
            'notify_due_reminders' => true,
        ]);

        $this->assertSame(2, app(DispatchWhatsappTaskReminders::class)->execute());
        $this->assertSame(0, app(DispatchWhatsappTaskReminders::class)->execute());

        Queue::assertPushed(SendWhatsappNotification::class, 2);
        $this->assertDatabaseCount('whatsapp_notification_logs', 2);
        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'task_id' => $todayTask->id,
            'subject' => 'Tugas hari ini',
            'event' => WhatsappConnection::EventTaskDue,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('whatsapp_notification_logs', ['subject' => 'Tugas selesai']);
        $this->assertDatabaseMissing('whatsapp_notification_logs', ['subject' => 'Tugas bukan milik pengguna']);
    }
}
