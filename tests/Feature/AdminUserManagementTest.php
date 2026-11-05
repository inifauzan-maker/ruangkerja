<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\BoardMessage;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_superadmin_can_open_forms_and_dashboard_links(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee(route('admin.users.create'), false)
            ->assertSee(route('admin.users.edit', $user), false);
        $this->get(route('admin.users.create'))->assertSee('Tambah pengguna');
        $this->get(route('admin.users.edit', $user))
            ->assertSee('Hapus pengguna')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    #[DataProvider('protectedEndpoints')]
    public function test_guests_and_regular_users_cannot_manage_users(string $method, string $route, bool $hasUser): void
    {
        $user = User::factory()->create();
        $url = route($route, $hasUser ? $user : []);

        $this->{$method}($url, [])->assertRedirect(route('login'));
        $this->actingAs($user)->{$method}($url, [])->assertForbidden();
        $this->assertModelExists($user);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public static function protectedEndpoints(): array
    {
        return [
            'create form' => ['get', 'admin.users.create', false],
            'create' => ['post', 'admin.users.store', false],
            'edit form' => ['get', 'admin.users.edit', true],
            'update' => ['patch', 'admin.users.update', true],
            'delete' => ['delete', 'admin.users.destroy', true],
        ];
    }

    public function test_superadmin_can_create_user_with_hashed_password_and_audit(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), $this->validAttributes() + [
            'email_verified_at' => now()->toDateTimeString(),
        ])->assertRedirect(route('admin.dashboard'))->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'baru@example.test')->sole();
        $this->assertSame('Pengguna Baru', $user->name);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password-baru-123', $user->password));
        $audit = AdminAuditLog::query()->sole();
        $this->assertSame('user.created', $audit->action);
        $this->assertSame($admin->id, $audit->actor_id);
        $this->assertArrayNotHasKey('password', $audit->after);
    }

    #[DataProvider('invalidAttributes')]
    public function test_create_and_update_reject_invalid_user_data(array $invalid, string $field): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.test']);
        $attributes = array_replace($this->validAttributes(), $invalid);

        $this->actingAs($admin)->post(route('admin.users.store'), $attributes)->assertSessionHasErrors($field);
        $this->patch(route('admin.users.update', $user), $attributes)->assertSessionHasErrors($field);

        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public static function invalidAttributes(): array
    {
        return [
            'empty name' => [['name' => ''], 'name'],
            'long name' => [['name' => str_repeat('a', 101)], 'name'],
            'invalid email' => [['email' => 'invalid'], 'email'],
            'duplicate email' => [['email' => 'taken@example.test'], 'email'],
            'invalid role' => [['global_role' => 'owner'], 'global_role'],
            'invalid status' => [['is_active' => 'invalid'], 'is_active'],
            'short password' => [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
            'mismatched password' => [['password_confirmation' => 'different-password'], 'password'],
            'invalid phone' => [['phone' => '<script>'], 'phone'],
            'long bio' => [['bio' => str_repeat('a', 1001)], 'bio'],
        ];
    }

    public function test_create_requires_identity_password_role_and_status(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'global_role', 'is_active']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_superadmin_can_update_identity_without_replacing_blank_password(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create(['password' => 'password-lama']);
        $attributes = array_replace($this->validAttributes(), [
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'job_title' => 'Manajer',
            'phone' => '+62 812 3456',
            'bio' => 'Mengelola tim.',
        ]);

        $this->actingAs($admin)->patch(route('admin.users.update', $user), $attributes)->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Pengguna Baru', $user->name);
        $this->assertSame('Manajer', $user->job_title);
        $this->assertSame('+62 812 3456', $user->phone);
        $this->assertSame('Mengelola tim.', $user->bio);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password-lama', $user->password));
        $this->assertSame('Pengguna Baru', AdminAuditLog::query()->sole()->after['name']);
    }

    public function test_credentials_update_revokes_sessions_tokens_and_email_verification(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();
        $oldRememberToken = $user->remember_token;
        DB::table('sessions')->insert(['id' => 'target-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'old-token', 'created_at' => now()]);

        $this->actingAs($admin)->patch(route('admin.users.update', $user), $this->validAttributes())->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('baru@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertNotSame($oldRememberToken, $user->remember_token);
        $this->assertTrue(Hash::check('password-baru-123', $user->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session']);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $audit = AdminAuditLog::query()->sole();
        $this->assertTrue($audit->after['password_changed']);
        $this->assertArrayNotHasKey('password', $audit->after);
        $this->assertArrayNotHasKey('password', $audit->before);
    }

    public function test_superadmin_can_delete_unused_user_and_preserve_audit_identity(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('avatars/test.jpg', 'avatar');
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create(['avatar_disk' => 'local', 'avatar_path' => 'avatars/test.jpg']);
        DB::table('sessions')->insert(['id' => 'deleted-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'old-token', 'created_at' => now()]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.dashboard'));

        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('sessions', ['id' => 'deleted-session']);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        Storage::disk('local')->assertMissing('avatars/test.jpg');
        $audit = AdminAuditLog::query()->sole();
        $this->assertSame('user.deleted', $audit->action);
        $this->assertNull($audit->target_user_id);
        $this->assertSame($user->email, $audit->before['email']);
        $this->get(route('admin.dashboard'))->assertSee($user->email);
    }

    public function test_last_active_superadmin_cannot_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors(['user' => 'Superadmin aktif terakhir tidak dapat dihapus.']);
        $this->assertModelExists($admin);
    }

    public function test_superadmin_cannot_delete_own_account_when_other_admin_exists(): void
    {
        $admin = User::factory()->superAdmin()->create();
        User::factory()->superAdmin()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        $this->assertModelExists($admin);
    }

    #[DataProvider('ownedContent')]
    public function test_users_with_work_content_cannot_be_deleted(string $model, string $ownerColumn): void
    {
        $admin = User::factory()->superAdmin()->create();
        $user = User::factory()->create();
        $content = $model::factory()->create([$ownerColumn => $user->id]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertSessionHasErrors('user');

        $this->assertModelExists($user);
        $this->assertModelExists($content);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public static function ownedContent(): array
    {
        return [
            'team' => [Team::class, 'owner_id'],
            'task' => [Task::class, 'creator_id'],
            'attachment' => [Attachment::class, 'uploader_id'],
            'message' => [BoardMessage::class, 'user_id'],
            'announcement' => [Announcement::class, 'author_id'],
            'comment' => [TaskComment::class, 'user_id'],
            'invitation' => [TeamInvitation::class, 'inviter_id'],
        ];
    }

    public function test_updating_own_credentials_requires_login_again(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->patch(route('admin.users.update', $admin), array_replace($this->validAttributes(), [
            'global_role' => User::RoleSuperAdmin,
        ]))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSame('baru@example.test', $admin->fresh()->email);
        $this->assertTrue($admin->fresh()->isSuperAdmin());
    }

    public function test_superadmin_can_create_inactive_superadmin(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), array_replace($this->validAttributes(), [
            'global_role' => User::RoleSuperAdmin,
            'is_active' => false,
        ]))->assertRedirect(route('admin.dashboard'));

        $createdUser = User::query()->where('email', 'baru@example.test')->sole();
        $this->assertTrue($createdUser->isSuperAdmin());
        $this->assertFalse($createdUser->is_active);
    }

    public function test_other_superadmin_can_be_deleted_when_active_admin_remains(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $target = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))->assertRedirect(route('admin.dashboard'));

        $this->assertModelMissing($target);
        $this->assertModelExists($admin);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'user.deleted', 'actor_id' => $admin->id]);
    }

    public function test_missing_user_returns_not_found_for_edit_update_and_delete(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get(route('admin.users.edit', 999999))->assertNotFound();
        $this->patch(route('admin.users.update', 999999), $this->validAttributes())->assertNotFound();
        $this->delete(route('admin.users.destroy', 999999))->assertNotFound();
    }

    /** @return array<string, string|bool> */
    private function validAttributes(): array
    {
        return [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.test',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
            'global_role' => User::RoleUser,
            'is_active' => true,
        ];
    }
}
