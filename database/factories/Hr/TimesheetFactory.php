<?php

declare(strict_types=1);

namespace Database\Factories\Hr;

use App\Models\Hr\Timesheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Timesheet> */
final class TimesheetFactory extends Factory
{
    protected $model = Timesheet::class;

    public function definition(): array
    {
        return ['employee' => fake()->name(), 'project' => fake()->randomElement(['Website refresh', 'Launch plan', 'Customer research', 'Operations audit']), 'work_date' => fake()->dateTimeBetween('-4 months', 'now'), 'hours' => fake()->randomFloat(2, 1, 10), 'status' => fake()->randomElement(['draft', 'submitted', 'approved']), 'notes' => fake()->optional()->sentence()];
    }
}
