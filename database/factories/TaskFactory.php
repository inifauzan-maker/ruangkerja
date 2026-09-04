<?php

namespace Database\Factories;

use App\Models\BoardList;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'board_list_id' => BoardList::factory(),
            'creator_id' => User::factory(),
            'title' => fake()->sentence(5),
            'description' => fake()->optional()->sentence(12),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_at' => fake()->optional()->dateTimeBetween('now', '+3 weeks'),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
