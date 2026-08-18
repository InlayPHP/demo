<?php

declare(strict_types=1);

namespace Database\Factories\Hr;

use App\Models\Hr\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Task> */
final class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return ['title' => fake()->sentence(6), 'project' => fake()->randomElement(['Website refresh', 'Launch plan', 'Customer research', 'Operations audit']), 'assignee' => fake()->name(), 'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']), 'status' => fake()->randomElement(['todo', 'in-progress', 'blocked', 'done']), 'due_date' => fake()->optional()->dateTimeBetween('now', '+6 months'), 'estimate' => fake()->randomFloat(2, 1, 40)];
    }
}
