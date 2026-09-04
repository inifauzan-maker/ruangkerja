<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\BoardMessage;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'demo@ruangkerja.test'],
            ['name' => 'Andi Pratama', 'password' => 'password'],
        );

        $team = Team::query()->firstOrCreate(
            ['slug' => 'studio-nusa'],
            ['owner_id' => $user->id, 'name' => 'Studio Nusa', 'description' => 'Tim lintas fungsi untuk membangun dan meluncurkan produk digital.'],
        );
        $board = Board::query()->firstOrCreate(
            ['team_id' => $team->id, 'name' => 'Peluncuran Produk'],
            ['description' => 'Perencanaan, desain, dan materi peluncuran versi pertama.'],
        );

        $demoMembers = collect([
            ['name' => 'Maya Putri', 'email' => 'maya@ruangkerja.test'],
            ['name' => 'Raka Wijaya', 'email' => 'raka@ruangkerja.test'],
        ])->map(fn (array $member): User => User::query()->firstOrCreate(
            ['email' => $member['email']],
            ['name' => $member['name'], 'password' => 'password'],
        ));
        $team->members()->syncWithoutDetaching($demoMembers->mapWithKeys(fn (User $member): array => [
            $member->id => ['role' => 'member'],
        ]));

        $lists = collect([
            ['title' => 'To Do', 'color' => 'slate'],
            ['title' => 'Dikerjakan', 'color' => 'amber'],
            ['title' => 'Selesai', 'color' => 'emerald'],
            ['title' => 'Batal', 'color' => 'rose'],
        ])->map(fn (array $list, int $position): BoardList => BoardList::query()->updateOrCreate(
            ['board_id' => $board->id, 'title' => $list['title']],
            [...$list, 'position' => $position],
        ));

        $demoTasks = [
            [0, 'Riset kebutuhan pengguna', 'Rangkum hasil wawancara pengguna dan temukan tiga masalah utama.', 'high', now()->addDays(2)],
            [0, 'Susun konsep kampanye', 'Buat arahan pesan utama untuk kanal sosial dan landing page.', 'medium', now()->addDays(5)],
            [1, 'Desain halaman produk', 'Finalisasi tampilan desktop dan mobile untuk proses review.', 'high', now()->addDay()],
            [1, 'Siapkan materi peluncuran', null, 'medium', now()->addWeek()],
            [2, 'Tentukan nama produk', 'Nama telah disepakati bersama tim brand.', 'low', now()->subDay()],
            [2, 'Setup ruang kerja tim', null, 'low', null],
            [3, 'Eksperimen logo versi awal', 'Arah visual tidak dilanjutkan setelah sesi review.', 'low', null],
        ];

        foreach ($demoTasks as $position => [$listIndex, $title, $description, $priority, $dueAt]) {
            Task::query()->firstOrCreate([
                'board_list_id' => $lists[$listIndex]->id,
                'title' => $title,
            ], [
                'creator_id' => $user->id,
                'description' => $description,
                'priority' => $priority,
                'due_at' => $dueAt,
                'position' => $position,
            ]);
        }

        BoardMessage::query()->firstOrCreate([
            'board_id' => $board->id,
            'user_id' => $user->id,
            'body' => 'Selamat datang di ruang kerja tim! Kita fokus menyelesaikan materi peluncuran minggu ini.',
        ]);

        Announcement::query()->firstOrCreate([
            'board_id' => $board->id,
            'title' => 'Target peluncuran produk',
        ], [
            'author_id' => $user->id,
            'body' => 'Versi pertama ditargetkan siap ditinjau hari Jumat. Mohon perbarui status tugas sebelum rapat harian.',
            'is_pinned' => true,
        ]);
    }
}
