<?php

declare(strict_types=1);

namespace Database\Factories\Hr;

use App\Models\Hr\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
final class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'department' => fake()->randomElement(['Engineering', 'Design', 'Content', 'Operations', 'Sales']),
            'employment_type' => fake()->randomElement(['full-time', 'part-time', 'contract']),
            'status' => fake()->randomElement(['active', 'active', 'on-leave', 'inactive']),
            'hire_date' => fake()->dateTimeBetween('-6 years', '-2 months'),
            'salary' => fake()->randomFloat(2, 42000, 168000),
            'skills' => fake()->randomElements(['Laravel', 'React', 'Design', 'SQL', 'Writing', 'Leadership'], fake()->numberBetween(1, 4)),
            'metadata' => ['timezone' => fake()->randomElement(['Asia/Hong_Kong', 'Europe/London', 'America/New_York'])],
        ];
    }
}
