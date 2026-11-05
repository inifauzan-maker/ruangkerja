<?php

namespace App\Actions;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteAdminUser
{
    public function execute(User $actor, User $targetUser, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        DB::transaction(function () use ($actor, $targetUser, $ipAddress, $userAgent): void {
            $user = User::query()->lockForUpdate()->findOrFail($targetUser->id);

            if ($user->isSuperAdmin() && $user->is_active
                && User::query()->where('global_role', User::RoleSuperAdmin)->where('is_active', true)->lockForUpdate()->get(['id'])->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'Superadmin aktif terakhir tidak dapat dihapus.']);
            }

            if ($actor->is($user)) {
                throw ValidationException::withMessages(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
            }

            if ($user->teams()->exists() || $user->tasks()->exists()) {
                throw ValidationException::withMessages(['user' => 'Pengguna masih memiliki tim atau tugas. Alihkan kepemilikan atau nonaktifkan akun.']);
            }

            foreach (['board_messages' => 'user_id', 'announcements' => 'author_id', 'attachments' => 'uploader_id', 'task_comments' => 'user_id', 'team_invitations' => 'inviter_id'] as $table => $column) {
                if (DB::table($table)->where($column, $user->id)->exists()) {
                    throw ValidationException::withMessages(['user' => 'Pengguna memiliki konten kerja. Nonaktifkan akun untuk mempertahankan data.']);
                }
            }

            AdminAuditLog::query()->create([
                'actor_id' => $actor->id,
                'target_user_id' => $user->id,
                'action' => 'user.deleted',
                'before' => $user->only(['id', 'name', 'email', 'global_role', 'is_active']),
                'after' => null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete();
        });
    }
}
