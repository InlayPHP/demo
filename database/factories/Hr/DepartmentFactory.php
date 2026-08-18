<?php

declare(strict_types=1);

namespace Database\Factories\Hr;

use App\Models\Hr\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Department> */
final class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return ['name' => fake()->randomElement(['Engineering', 'Design', 'Content', 'Operations', 'Sales', 'People']), 'parent' => fake()->optional()->randomElement(['Corporate', 'Product', 'Customer']), 'head' => fake()->name(), 'status' => fake()->randomElement(['active', 'active', 'archived'])];
    }
}
