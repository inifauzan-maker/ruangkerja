<?php

namespace Database\Factories;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminAuditLog>
 */
class AdminAuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory()->superAdmin(),
            'target_user_id' => User::factory(),
            'action' => 'user.updated',
            'before' => ['global_role' => User::RoleUser, 'is_active' => true],
            'after' => ['global_role' => User::RoleSuperAdmin, 'is_active' => true],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
