<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'author_id' => User::factory(),
            'title' => fake()->sentence(5),
            'body' => fake()->paragraph(),
            'is_pinned' => false,
        ];
    }
}
