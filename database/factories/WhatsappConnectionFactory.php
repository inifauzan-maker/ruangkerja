<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WhatsappConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WhatsappConnection> */
class WhatsappConnectionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone_number_id' => 'fonnte',
            'recipient_phone' => '628'.fake()->numerify('##########'),
            'template_name' => 'plain_text',
            'template_language' => 'id',
            'is_active' => true,
            'consented_at' => now(),
            'opted_out_at' => null,
            'notify_task_created' => true,
            'notify_task_updated' => true,
            'notify_chat_messages' => false,
            'notify_announcements' => true,
            'notify_due_reminders' => true,
            'quiet_hours_enabled' => true,
            'timezone' => 'Asia/Jakarta',
            'quiet_hours_start' => '21:00',
            'quiet_hours_end' => '07:00',
        ];
    }
}
