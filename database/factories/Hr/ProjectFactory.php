<?php

declare(strict_types=1);

namespace Database\Factories\Hr;

use App\Models\Hr\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'status' => fake()->randomElement(['planned', 'in-progress', 'at-risk', 'completed']),
            'owner' => fake()->name(),
            'budget' => fake()->randomFloat(2, 5000, 250000),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+9 months'),
            'plan' => [
                ['type' => 'milestone', 'data' => ['title' => 'Discovery', 'due' => now()->addWeeks(2)->toDateString()]],
                ['type' => 'checkpoint', 'data' => ['title' => 'Review scope', 'owner' => fake()->name()]],
            ],
        ];
    }
}
