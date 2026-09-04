<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\BoardMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardMessage>
 */
class BoardMessageFactory extends Factory
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
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }
}
