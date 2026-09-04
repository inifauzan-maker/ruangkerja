<?php

namespace Database\Factories;

use App\Models\EmailNotificationLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EmailNotificationLog> */
class EmailNotificationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'sender_id' => User::factory(),
            'recipient' => fake()->safeEmail(),
            'event' => 'team_invitation',
            'subject' => 'Undangan tim',
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
