<?php

namespace App\Actions;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateAdminUser
{
    /**
     * @param  array{global_role: string, is_active: bool, name?: string, email?: string, job_title?: ?string, phone?: ?string, bio?: ?string, password?: string}  $attributes
     */
    public function execute(
        ?User $actor,
        User $targetUser,
        array $attributes,
        string $action = 'user.updated',
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): User {
        return DB::transaction(function () use ($actor, $targetUser, $attributes, $action, $ipAddress, $userAgent): User {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($targetUser->id);
            $before = [
                'global_role' => $lockedUser->global_role,
                'is_active' => $lockedUser->is_active,
            ];

            $removesActiveSuperAdmin = $lockedUser->isSuperAdmin()
                && $lockedUser->is_active
                && ($attributes['global_role'] !== User::RoleSuperAdmin || ! $attributes['is_active']);

            if ($removesActiveSuperAdmin) {
                $activeSuperAdmins = User::query()
                    ->where('global_role', User::RoleSuperAdmin)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id']);

                if ($activeSuperAdmins->count() <= 1) {
                    throw ValidationException::withMessages([
                        'global_role' => 'Superadmin aktif terakhir tidak dapat diturunkan atau dinonaktifkan.',
                    ]);
                }
            }

            $auditFields = array_intersect(array_keys($attributes), ['name', 'email', 'job_title', 'phone', 'bio']);
            $before = array_merge($before, $lockedUser->only($auditFields));
            $oldEmail = $lockedUser->email;
            $passwordChanged = ! empty($attributes['password']);
            $emailChanged = isset($attributes['email']) && $attributes['email'] !== $oldEmail;

            if ($emailChanged) {
                $lockedUser->email_verified_at = null;
            }

            if ($passwordChanged || $emailChanged) {
                $lockedUser->remember_token = Str::random(60);
                DB::table('password_reset_tokens')->where('email', $oldEmail)->delete();
                DB::table('sessions')->where('user_id', $lockedUser->id)->delete();
            }

            $lockedUser->forceFill($attributes)->save();
            $after = [
                'global_role' => $lockedUser->global_role,
                'is_active' => $lockedUser->is_active,
            ];

            $after = array_merge($after, $lockedUser->only($auditFields));

            if ($passwordChanged) {
                $after['password_changed'] = true;
            }

            if ($before !== $after) {
                AdminAuditLog::query()->create([
                    'actor_id' => $actor?->id,
                    'target_user_id' => $lockedUser->id,
                    'action' => $action,
                    'before' => $before,
                    'after' => $after,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                ]);
            }

            if (! $lockedUser->is_active) {
                DB::table('sessions')->where('user_id', $lockedUser->id)->delete();
            }

            return $lockedUser;
        });
    }
}
