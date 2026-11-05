<?php

namespace App\Actions;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateAdminUser
{
    /** @param array{name: string, email: string, password: string, global_role: string, is_active: bool, job_title?: ?string, phone?: ?string, bio?: ?string} $attributes */
    public function execute(User $actor, array $attributes, ?string $ipAddress = null, ?string $userAgent = null): User
    {
        return DB::transaction(function () use ($actor, $attributes, $ipAddress, $userAgent): User {
            $user = new User;
            $user->forceFill($attributes)->save();

            AdminAuditLog::query()->create([
                'actor_id' => $actor->id,
                'target_user_id' => $user->id,
                'action' => 'user.created',
                'before' => null,
                'after' => $user->only(['name', 'email', 'global_role', 'is_active']),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return $user;
        });
    }
}
