<?php

namespace Tests\Feature\Admin;

use App\Models\AdminAuditLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_and_regular_user_cannot_access_superadmin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $owner = User::factory()->create();
        Team::factory()->for($owner, 'owner')->create();

        $this->actingAs($owner)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_superadmin_can_view_platform_dashboard_and_escaped_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->create(['name' => '<script>alert(1)</script>', 'email' => 'anggota@example.test']);

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Pusat Superadmin')
            ->assertSee('anggota@example.test')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_superadmin_can_promote_and_deactivate_user_with_audit_trail(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->withHeader('User-Agent', 'Admin Test Browser')
            ->patch(route('admin.users.update', $user), [
                'global_role' => User::RoleSuperAdmin,
                'is_active' => false,
            ]);

        $response->assertRedirect()->assertSessionHas('status');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'global_role' => User::RoleSuperAdmin,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_id' => $superAdmin->id,
            'target_user_id' => $user->id,
            'action' => 'user.updated',
            'user_agent' => 'Admin Test Browser',
        ]);
        $this->assertSame(
            ['global_role' => User::RoleUser, 'is_active' => true],
            AdminAuditLog::query()->sole()->before,
        );
    }

    public function test_invalid_global_role_is_rejected(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();

        $this->actingAs($superAdmin)->patch(route('admin.users.update', $user), [
            'global_role' => 'owner',
            'is_active' => true,
        ])->assertSessionHasErrors('global_role');

        $this->assertSame(User::RoleUser, $user->fresh()->global_role);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public function test_last_active_superadmin_cannot_be_demoted_or_deactivated(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->patch(route('admin.users.update', $superAdmin), [
            'global_role' => User::RoleUser,
            'is_active' => true,
        ])->assertSessionHasErrors('global_role');

        $this->actingAs($superAdmin)->patch(route('admin.users.update', $superAdmin), [
            'global_role' => User::RoleSuperAdmin,
            'is_active' => false,
        ])->assertSessionHasErrors('global_role');

        $this->assertTrue($superAdmin->fresh()->isSuperAdmin());
        $this->assertTrue($superAdmin->fresh()->is_active);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public function test_superadmin_can_demote_self_when_another_active_superadmin_exists(): void
    {
        $firstSuperAdmin = User::factory()->superAdmin()->create();
        User::factory()->superAdmin()->create();

        $this->actingAs($firstSuperAdmin)->patch(route('admin.users.update', $firstSuperAdmin), [
            'global_role' => User::RoleUser,
            'is_active' => true,
        ])->assertRedirect(route('dashboard'));

        $this->assertFalse($firstSuperAdmin->fresh()->isSuperAdmin());
    }

    public function test_inactive_user_cannot_log_in_or_keep_an_authenticated_session(): void
    {
        $inactiveUser = User::factory()->inactive()->create(['password' => 'password']);

        $this->post(route('login.store'), [
            'email' => $inactiveUser->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($inactiveUser)->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_promote_command_creates_an_active_superadmin_and_audit_log(): void
    {
        $user = User::factory()->inactive()->create(['email' => 'admin@example.test']);

        $this->artisan('users:promote-superadmin', [
            'email' => $user->email,
            '--force' => true,
        ])->assertSuccessful();

        $promotedUser = $user->fresh();
        $this->assertTrue($promotedUser->isSuperAdmin());
        $this->assertTrue($promotedUser->is_active);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_id' => null,
            'target_user_id' => $user->id,
            'action' => 'user.promoted_by_command',
        ]);
    }
}
