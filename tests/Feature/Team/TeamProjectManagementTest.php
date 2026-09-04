<?php

namespace Tests\Feature\Team;

use App\Models\Board;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TeamProjectManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_shows_owned_and_joined_teams_with_projects(): void
    {
        $user = User::factory()->create();
        $ownedTeam = Team::factory()->for($user, 'owner')->create(['name' => 'Tim Milik Saya']);
        $project = Board::factory()->for($ownedTeam)->create(['name' => 'Proyek Utama']);
        $otherOwner = User::factory()->create();
        $joinedTeam = Team::factory()->for($otherOwner, 'owner')->create(['name' => 'Tim Kolaborasi']);
        $joinedTeam->members()->attach($user, ['role' => 'member']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeInOrder(['Tim Kolaborasi', 'Tim Milik Saya']);
        $response->assertSee($project->name);
    }

    public function test_valid_payload_creates_team_owned_by_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('teams.store'), [
            'name' => 'Tim Kreatif',
            'description' => 'Mengelola produksi konten.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Tim baru berhasil dibuat.');
        $this->assertDatabaseHas('teams', [
            'owner_id' => $user->id,
            'name' => 'Tim Kreatif',
            'description' => 'Mengelola produksi konten.',
        ]);
    }

    public function test_empty_team_payload_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('teams.store'), [])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('teams', 0);
    }

    public function test_owner_can_add_existing_user_as_team_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();

        $response = $this->actingAs($owner)->post(route('teams.members.store', $team), [
            'email' => $member->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Anggota berhasil ditambahkan.');
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);
    }

    public function test_non_owner_cannot_add_team_member(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $candidate = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();

        $this->actingAs($intruder)->post(route('teams.members.store', $team), [
            'email' => $candidate->email,
        ])->assertNotFound();

        $this->assertDatabaseCount('team_user', 0);
    }

    public function test_owner_can_create_project_with_default_lists(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();

        $response = $this->actingAs($owner)->post(route('projects.store'), [
            'team_id' => $team->id,
            'name' => 'Website Perusahaan',
            'description' => 'Pembuatan ulang website utama.',
        ]);

        $project = Board::query()->where('name', 'Website Perusahaan')->firstOrFail();
        $response->assertRedirect(route('boards.show', $project));
        $this->assertDatabaseHas('boards', ['team_id' => $team->id, 'name' => 'Website Perusahaan']);
        $this->assertDatabaseCount('board_lists', 4);
    }

    public function test_member_can_open_project_but_cannot_create_project(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $project = Board::factory()->for($team)->create();

        $this->actingAs($member)->get(route('boards.show', $project))->assertOk();
        $this->actingAs($member)->post(route('projects.store'), [
            'team_id' => $team->id,
            'name' => 'Proyek Tanpa Izin',
        ])->assertNotFound();

        $this->assertDatabaseMissing('boards', ['name' => 'Proyek Tanpa Izin']);
    }

    public function test_dashboard_escapes_team_and_project_content(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user, 'owner')->create(['name' => '<script>tim</script>']);
        Board::factory()->for($team)->create(['name' => '<script>proyek</script>']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>tim</script>', false)
            ->assertDontSee('<script>proyek</script>', false);
    }
}
