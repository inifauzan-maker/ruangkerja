<?php

namespace Database\Factories;

use App\Models\WhatsappConnection;
use App\Models\WhatsappNotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WhatsappNotificationLog>
 */
class WhatsappNotificationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'whatsapp_connection_id' => WhatsappConnection::factory(),
            'task_id' => null,
            'idempotency_key' => (string) Str::uuid(),
            'event' => 'task_created',
            'event_label' => 'Tugas baru',
            'subject' => fake()->sentence(4),
            'project_name' => fake()->words(2, true),
            'url' => fake()->url(),
            'status' => WhatsappNotificationLog::StatusPending,
            'scheduled_for' => now(),
        ];
    }
}
