<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\EmailNotificationLog;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_report_calculates_task_metrics_workload_activity_and_email_history(): void
    {
        [$owner, $member, $board, $activeList, $completedList] = $this->createBoard();
        $activeTask = Task::factory()->for($activeList, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas aktif terlambat',
            'due_at' => today()->subDay(),
        ]);
        $activeTask->assignees()->attach($member);
        $activeTask->recordActivity($owner, 'updated');
        $completedTask = Task::factory()->for($completedList, 'list')->for($owner, 'creator')->create([
            'title' => 'Tugas selesai',
        ]);
        $completedTask->assignees()->attach($member);

        EmailNotificationLog::factory()->create([
            'team_id' => $board->team_id,
            'sender_id' => $owner->id,
            'recipient' => $member->email,
            'subject' => 'Undangan proyek',
        ]);

        $this->actingAs($owner)->get(route('reports.index'))
            ->assertOk()
            ->assertViewHas('report', function (array $report) use ($member): bool {
                $memberWorkload = $report['workload']->firstWhere('id', $member->id);

                return $report['metrics'] === [
                    'total' => 2,
                    'completed' => 1,
                    'overdue' => 1,
                    'in_progress' => 1,
                ]
                    && $memberWorkload['total'] === 2
                    && $memberWorkload['active'] === 1
                    && $memberWorkload['overdue'] === 1
                    && $report['activities']->count() === 1
                    && $report['email_logs']->count() === 1;
            })
            ->assertSee('Undangan proyek');
    }

    public function test_report_filter_cannot_access_another_users_project(): void
    {
        [$owner] = $this->createBoard();
        $otherOwner = User::factory()->create();
        $otherTeam = Team::factory()->for($otherOwner, 'owner')->create();
        $privateBoard = Board::factory()->for($otherTeam)->create();

        $this->actingAs($owner)->get(route('reports.index', ['board' => $privateBoard->id]))
            ->assertNotFound();
    }

    public function test_pdf_and_excel_exports_are_valid_downloads(): void
    {
        [$owner] = $this->createBoard();

        $pdf = $this->actingAs($owner)->get(route('reports.pdf'));
        $pdf->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="laporan-villa-merah.pdf"');
        $this->assertStringStartsWith('%PDF-1.4', $pdf->getContent());

        $excel = $this->actingAs($owner)->get(route('reports.excel'));
        $excel->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename="laporan-villa-merah.xlsx"');
        $this->assertStringStartsWith('PK', $excel->getContent());
    }

    /**
     * @return array{User, User, Board, BoardList, BoardList}
     */
    private function createBoard(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $board = Board::factory()->for($team)->create();
        $activeList = BoardList::factory()->for($board)->create(['title' => 'Dikerjakan', 'position' => 0]);
        $completedList = BoardList::factory()->for($board)->create(['title' => 'Selesai', 'position' => 1]);

        return [$owner, $member, $board, $activeList, $completedList];
    }
}
