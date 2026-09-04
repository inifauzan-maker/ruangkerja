<?php

namespace Tests\Feature\Team;

use App\Mail\TeamInvitationMail;
use App\Models\Board;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeamSettingsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_member_can_view_own_team_settings_but_not_another_team(): void
    {
        $member = User::factory()->create();
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create(['name' => 'Tim Terhubung']);
        $team->members()->attach($member, ['role' => 'member']);
        $foreignTeam = Team::factory()->create(['name' => 'Tim Rahasia']);

        $this->actingAs($member)->get(route('teams.show', $team))->assertSee('Tim Terhubung');
        $this->actingAs($member)->get(route('teams.show', $foreignTeam))->assertNotFound();
    }

    public function test_only_owner_can_update_team_information(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create(['name' => 'Nama Lama']);
        $team->members()->attach($member, ['role' => 'member']);

        $this->actingAs($member)->patch(route('teams.update', $team), [
            'name' => 'Perubahan Ilegal',
            'description' => 'Tidak boleh tersimpan.',
        ])->assertNotFound();

        $response = $this->actingAs($owner)->patch(route('teams.update', $team), [
            'name' => 'Nama Baru',
            'description' => 'Deskripsi tim baru.',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Informasi tim berhasil diperbarui.');
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'Nama Baru']);
        $this->assertDatabaseMissing('teams', ['id' => $team->id, 'name' => 'Perubahan Ilegal']);
    }

    public function test_owner_can_change_member_role_to_admin(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);

        $response = $this->actingAs($owner)->patch(route('teams.members.update', [$team, $member]), [
            'role' => 'admin',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Role anggota berhasil diperbarui.');
        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $member->id, 'role' => 'admin']);
    }

    public function test_admin_can_create_and_update_project(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($admin, ['role' => 'admin']);

        $this->actingAs($admin)->post(route('projects.store'), [
            'team_id' => $team->id,
            'name' => 'Proyek Admin',
            'description' => 'Dibuat admin.',
        ])->assertRedirect();

        $project = Board::query()->where('name', 'Proyek Admin')->firstOrFail();
        $this->actingAs($admin)->patch(route('projects.update', $project), [
            'name' => 'Proyek Diperbarui',
            'description' => 'Sudah diperbarui admin.',
        ])->assertRedirect()->assertSessionHas('status', 'Informasi proyek berhasil diperbarui.');

        $this->assertDatabaseHas('boards', ['id' => $project->id, 'name' => 'Proyek Diperbarui']);
        $this->assertDatabaseCount('board_lists', 4);
    }

    public function test_member_cannot_update_project(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($member, ['role' => 'member']);
        $project = Board::factory()->for($team)->create(['name' => 'Nama Aman']);

        $this->actingAs($member)->patch(route('projects.update', $project), [
            'name' => 'Nama Ilegal',
            'description' => null,
        ])->assertNotFound();

        $this->assertDatabaseHas('boards', ['id' => $project->id, 'name' => 'Nama Aman']);
    }

    public function test_owner_can_send_invitation_email_for_any_address(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();

        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'email' => 'calon@example.com',
            'role' => 'admin',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Undangan berhasil dikirim ke calon@example.com.');
        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'email' => 'calon@example.com',
            'role' => 'admin',
            'accepted_at' => null,
        ]);
        Mail::assertSent(TeamInvitationMail::class, fn (TeamInvitationMail $mail): bool => $mail->hasTo('calon@example.com'));
    }

    public function test_invitation_email_escapes_team_and_inviter_names(): void
    {
        $owner = User::factory()->create(['name' => '<script>pemilik</script>']);
        $team = Team::factory()->for($owner, 'owner')->create(['name' => '<script>tim</script>']);
        $invitation = TeamInvitation::factory()->for($team)->for($owner, 'inviter')->create();

        $html = (new TeamInvitationMail($invitation->load(['team', 'inviter']), 'safe-token'))->render();

        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>tim</script>', $html);
        $this->assertStringNotContainsString('<script>pemilik</script>', $html);
    }

    public function test_admin_can_only_invite_members(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($admin, ['role' => 'admin']);

        $this->actingAs($admin)->post(route('teams.invitations.store', $team), [
            'email' => 'admin-baru@example.com',
            'role' => 'admin',
        ])->assertNotFound();

        $this->actingAs($admin)->post(route('teams.invitations.store', $team), [
            'email' => 'member-baru@example.com',
            'role' => 'member',
        ])->assertRedirect();

        $this->assertDatabaseMissing('team_invitations', ['email' => 'admin-baru@example.com']);
        $this->assertDatabaseHas('team_invitations', ['email' => 'member-baru@example.com', 'role' => 'member']);
    }

    public function test_matching_user_can_accept_valid_invitation_once(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $team = Team::factory()->for($owner, 'owner')->create();
        $token = 'valid-secret-token';
        $invitation = TeamInvitation::factory()->for($team)->for($owner, 'inviter')->create([
            'email' => $invitee->email,
            'role' => 'admin',
            'token_hash' => hash('sha256', $token),
        ]);

        $response = $this->actingAs($invitee)->post(route('team-invitations.accept', [$invitation, $token]));

        $response->assertRedirect(route('teams.show', $team))->assertSessionHas('status', 'Selamat datang di tim!');
        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $invitee->id, 'role' => 'admin']);
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->actingAs($invitee)->post(route('team-invitations.accept', [$invitation, $token]))->assertNotFound();
    }

    public function test_wrong_user_and_expired_invitation_are_rejected(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $otherUser = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $token = 'expired-secret-token';
        $invitation = TeamInvitation::factory()->for($team)->for($owner, 'inviter')->create([
            'email' => $invitee->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($otherUser)->get(route('team-invitations.show', [$invitation, $token]))->assertNotFound();
        $this->actingAs($invitee)->get(route('team-invitations.show', [$invitation, $token]))->assertNotFound();

        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $invitee->id]);
    }

    public function test_admin_cannot_remove_another_admin_but_owner_can_remove_any_member(): void
    {
        $owner = User::factory()->create();
        $firstAdmin = User::factory()->create();
        $secondAdmin = User::factory()->create();
        $team = Team::factory()->for($owner, 'owner')->create();
        $team->members()->attach($firstAdmin, ['role' => 'admin']);
        $team->members()->attach($secondAdmin, ['role' => 'admin']);

        $this->actingAs($firstAdmin)->delete(route('teams.members.destroy', [$team, $secondAdmin]))->assertNotFound();
        $this->actingAs($owner)->delete(route('teams.members.destroy', [$team, $secondAdmin]))
            ->assertRedirect()->assertSessionHas('status', 'Anggota dikeluarkan dari tim.');

        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $secondAdmin->id]);
    }
}
